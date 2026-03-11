<?php

namespace App\Tests\Service;

use App\Factory\CountryFactory;
use App\Factory\CurrencyFactory;
use App\Service\Item\CountryUpdater;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CountryUpdaterTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories; // Trait to use Zenstruck Foundry factories

    private CountryUpdater $updater;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = self::getContainer();

        $this->updater = $container->get(CountryUpdater::class);
    }

    public function testCreatesCountries(): void
    {
        $currency = CurrencyFactory::createOne([
            'code' => 'USD',
            'exchangeRate' => 999,
        ]);
        $result = $this->updater->update();
        $this->assertCount(3, $result);
        $this->assertEquals('Poland', $result['PL']->getName());
        $this->assertEquals('Germany', $result['DE']->getName());
        $this->assertEquals('United States', $result['US']->getName());
    }

    public function testUpdateCountries(): void
    {
        CountryFactory::createOne([
            'code' => 'DE',
            'name' => 'Deutschland',
        ]);
        CurrencyFactory::createOne([
            'code' => 'USD',
            'exchangeRate' => 1,
        ]);
        $currency = CurrencyFactory::createOne([
            'code' => 'EUR',
            'exchangeRate' => 1.5,
        ]);

        $result = $this->updater->update();
        $this->assertNotEmpty($result);
        $this->assertEquals('Germany', $result['DE']->getName());
        $this->assertEquals($currency->_real(), $result['DE']->getCurrency());
    }

    public function testThrowsExceptionWhenDefaultCurrencyMissing(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('There is no default currency');

        $this->updater->update();
    }
}
