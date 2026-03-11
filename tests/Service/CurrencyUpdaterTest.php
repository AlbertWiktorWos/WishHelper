<?php

namespace App\Tests\Service;

use App\Entity\Currency;
use App\Factory\CurrencyFactory;
use App\Integration\RestIntegration\CurrencyApi\DTO\CurrenciesResponse;
use App\Integration\RestIntegration\CurrencyApi\DTO\CurrencyDto;
use App\Integration\RestIntegration\CurrencyApi\DTO\LatestRatesResponse;
use App\Integration\RestIntegration\CurrencyApi\Provider\CurrenciesInfoProvider;
use App\Integration\RestIntegration\CurrencyApi\Provider\CurrencyRatesProvider;
use App\Repository\CurrencyRepository;
use App\Service\Item\CurrencyUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\Cache\CacheInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CurrencyUpdaterTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories; // Trait to use Zenstruck Foundry factories

    private EntityManagerInterface $em;
    private CurrencyUpdater $updater;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = self::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->updater = $container->get(CurrencyUpdater::class);
    }

    public function testCreatesCurrencies(): void
    {
        $ratesProvider = $this->createMock(CurrencyRatesProvider::class);
        $infoProvider = $this->createMock(CurrenciesInfoProvider::class);
        $repo = $this->createMock(CurrencyRepository::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $cache = $this->createMock(CacheInterface::class);

        $rates = new LatestRatesResponse('USD', [
            'USD' => 1,
            'EUR' => 0.9,
        ]);

        $usd = new CurrencyDto(
            'USD',
            'US Dollar',
            '$'
        );
        $eur = new CurrencyDto(
            'EUR',
            'Euro',
            '€'
        );

        $currencies = new CurrenciesResponse([
            'USD' => $usd,
            'EUR' => $eur,
        ]);

        $ratesProvider
            ->method('getRates')
            ->willReturn($rates);

        $infoProvider
            ->method('getCurrencies')
            ->willReturn($currencies);

        $repo
            ->method('findCurrenciesByCodes')
            ->willReturn([]);

        $em->expects($this->once())->method('flush');

        $service = new CurrencyUpdater(
            $ratesProvider,
            $infoProvider,
            $repo,
            $em,
            new NullLogger(),
            $cache
        );

        $result = $service->update();

        $this->assertCount(2, $result);
        $this->assertEquals('US Dollar', $result['USD']->getName());
    }

    public function testUpdatesExistingCurrencyRate(): void
    {
        $currency = new Currency();
        $currency->setCode('USD');
        $currency->setExchangeRate('999');

        $repo = $this->createMock(CurrencyRepository::class);
        $repo->method('findCurrenciesByCodes')
            ->willReturn(['USD' => $currency]);

        $ratesProvider = $this->createMock(CurrencyRatesProvider::class);
        $infoProvider = $this->createMock(CurrenciesInfoProvider::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $cache = $this->createMock(CacheInterface::class);

        $rates = new LatestRatesResponse('USD', ['USD' => 1]);

        $dto = new CurrencyDto(
            'USD',
            'US Dollar',
            '$'
        );

        $currencies = new CurrenciesResponse(['USD' => $dto]);

        $ratesProvider->method('getRates')->willReturn($rates);
        $infoProvider->method('getCurrencies')->willReturn($currencies);

        $em->expects($this->once())->method('flush');

        $service = new CurrencyUpdater(
            $ratesProvider,
            $infoProvider,
            $repo,
            $em,
            new NullLogger(),
            $cache
        );

        $service->update(['USD']);

        $this->assertEquals(1.0, (float) $currency->getExchangeRate());
    }

    public function testCreatesCurrenciesWhenTheyDoNotExist(): void
    {
        $this->updater->update(['USD', 'EUR']);

        $repo = $this->em->getRepository(Currency::class);

        $usd = $repo->findOneBy(['code' => 'USD']);
        $eur = $repo->findOneBy(['code' => 'EUR']);

        $this->assertNotNull($usd);
        $this->assertNotNull($eur);

        $this->assertEquals('1', $usd->getExchangeRate());
        $this->assertNotNull($usd->getUpdatedAt());
        $this->assertNotNull($eur->getExchangeRate());
        $this->assertNotNull($eur->getUpdatedAt());
    }

    public function testUpdatesExistingCurrency(): void
    {
        $currency = CurrencyFactory::createOne([
            'code' => 'USD',
            'exchangeRate' => 999,
        ]);

        $this->updater->update(['USD']);

        $this->em->clear();

        $repo = $this->em->getRepository(Currency::class);
        $updated = $repo->findOneBy(['code' => 'USD']);

        $this->assertEquals(1, (float) $updated->getExchangeRate());
    }
}
