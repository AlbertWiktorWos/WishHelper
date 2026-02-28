<?php

namespace App\Tests\Service;

use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Category;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\WishItem;
use App\Entity\WishItemRecommendation;
use App\Event\WishItemSharedEvent;
use App\EventListener\WishItemSharedListener;
use App\Factory\CategoryFactory;
use App\Factory\UserFactory;
use App\Factory\WishItemFactory;
use App\Factory\WishItemRecommendationFactory;
use App\Service\Mailer;
use App\Service\TagService;
use App\Service\WishMatchCalculator;
use App\State\WishItemProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zenstruck\Foundry\Test\ResetDatabase;

class WishMatchCalculatorTest extends KernelTestCase
{
    use ResetDatabase;

    private WishMatchCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new WishMatchCalculator();
    }

    private function createWishItem(
        int $categoryId,
        array $tagIds = [],
        ?float $price = null
    ): WishItem {
        $category = $this->createMock(Category::class);
        $category->method('getId')->willReturn($categoryId);

        $tags = [];
        foreach ($tagIds as $id) {
            $tag = $this->createMock(Tag::class);
            $tag->method('getId')->willReturn($id);
            $tags[] = $tag;
        }

        $item = $this->createMock(WishItem::class);
        $item->method('getCategory')->willReturn($category);
        $item->method('getTags')->willReturn(new ArrayCollection($tags));
        $item->method('getPrice')->willReturn((string)$price);

        return $item;
    }

    public function testReturnsZeroWhenNoMatch(): void
    {
        $item = $this->createWishItem(1, [10, 11]);

        $score = $this->calculator->calculate($item, [2], [20]);

        $this->assertSame(0, $score);
    }

    public function testCategoryMatchGives50(): void
    {
        $item = $this->createWishItem(1);

        $score = $this->calculator->calculate($item, [1], []);

        $this->assertSame(50, $score);
    }

    public function testTagMatchCalculatesProportionally(): void
    {
        $item = $this->createWishItem(1, [10, 11]);

        $score = $this->calculator->calculate($item, [], [10, 11, 12, 13]);

        // 2 matches out of 4 selected tags -> 25
        $this->assertSame(25, $score);
    }

    public function testFullMatchReturns100(): void
    {
        $item = $this->createWishItem(1, [10, 11]);

        $score = $this->calculator->calculate($item, [1], [10, 11]);

        $this->assertSame(100, $score);
    }

    public function testPricePenalty(): void
    {
        $item = $this->createWishItem(1, [], 200);

        $score = $this->calculator->calculate($item, [1], [], 100);

        // 50 base - 30 penalty (max cap)
        $this->assertSame(20, $score);
    }

    public function testScoreIsClampedBetween0And100(): void
    {
        $item = $this->createWishItem(1, [], 1000);

        $score = $this->calculator->calculate($item, [1], [], 10);

        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    public function testDispatchesEventWhenShared(): void
    {
        $user = $this->createMock(User::class);
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);

        $inner = $this->createMock(ProcessorInterface::class);
        $tagService = $this->createMock(TagService::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $wish = new WishItem();
        $wish->setShared(true);

        $inner->method('process')->willReturn($wish);

        $dispatcher
            ->expects($this->once())
            ->method('dispatch');

        $processor = new WishItemProcessor(
            $security,
            $inner,
            $tagService,
            $dispatcher
        );

        $operation = $this->createMock(HttpOperation::class);
        $operation->method('getMethod')->willReturn('POST');

        $processor->process($wish, $operation, [], [
            'request' => new \Symfony\Component\HttpFoundation\Request([], [], [], [], [], [], '{}')
        ]);
    }

    public function testCreatesRecommendationAndSendsEmail(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $em = $container->get('doctrine')->getManager();

        $category = CategoryFactory::createOne();

        // user who follows the category
        $user = UserFactory::createOne([
            'categories' => [$category],
            'notify' => true,
        ]);

        // wish with the same category
        $wish = WishItemFactory::createOne([
            'category' => $category,
            'owner' => UserFactory::createOne(), // inny owner
            'shared' => true,
        ]);

        $calculator = $this->createMock(WishMatchCalculator::class);
        $calculator->method('calculate')->willReturn(80);

        $mailer = $this->createMock(Mailer::class);
        $mailer->expects($this->once())
            ->method('sendEmailWishNotificationMessage');

        $listener = new WishItemSharedListener(
            $em,
            $calculator,
            $mailer
        );

        $em->flush();
        $em->clear();
        $wish = $em->getRepository(WishItem::class)->find($wish->getId());

        $event = new WishItemSharedEvent($wish, true);

        $listener->onWishItemShared($event);

        $em->clear();

        $recommendations = $em->getRepository(WishItemRecommendation::class)
            ->findBy(['user' => $user]);

        $this->assertCount(1, $recommendations);
        $this->assertSame(80, $recommendations[0]->getScore());
    }
    public function testUpdatesExistingRecommendation(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();

        $category = CategoryFactory::createOne();

        $user = UserFactory::createOne([
            'categories' => [$category],
        ]);

        $wish = WishItemFactory::createOne([
            'category' => $category,
            'owner' => UserFactory::createOne(),
            'shared' => true,
        ]);

        WishItemRecommendationFactory::createOne([
            'user' => $user,
            'wishItem' => $wish,
            'score' => 60,
        ]);

        $calculator = $this->createMock(WishMatchCalculator::class);
        $calculator->method('calculate')->willReturn(90);

        $mailer = $this->createMock(Mailer::class);

        $listener = new WishItemSharedListener($em, $calculator, $mailer);

        $em->flush();
        $em->clear();
        $wish = $em->getRepository(WishItem::class)->find($wish->getId());

        $listener->onWishItemShared(
            new WishItemSharedEvent($wish, false)
        );

        $em->clear();

        $rec = $em->getRepository(WishItemRecommendation::class)
            ->findOneBy([
                'user' => $user,
                'wishItem' => $wish,
            ]);

        $this->assertSame(90, $rec->getScore());
        $this->assertNull($rec->getNotifiedAt());
    }

}