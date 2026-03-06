<?php

namespace App\Tests\Integration;

use App\Integration\RestIntegration\CurrencyApi\Exception\CurrencyApiException;
use App\Integration\RestIntegration\CurrencyApi\MockCurrencyApiClient;
use App\Integration\RestIntegration\CurrencyApi\Provider\CurrenciesInfoProvider;
use App\Integration\RestIntegration\CurrencyApi\Provider\CurrencyRatesProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class CurrenciesProvidersTest extends TestCase
{
    public function testReturnsCurrencies(): void
    {
        $provider = new CurrenciesInfoProvider(
            new MockCurrencyApiClient('http://fake'),
            new NullLogger()
        );

        $response = $provider->getCurrencies(['USD', 'EUR']);

        $this->assertNotNull($response);
        $this->assertCount(2, $response->currencies);

        $usd = $response->currencies['USD'];
        $eur = $response->currencies['EUR'];

        $this->assertEquals('USD', $usd->code);
        $this->assertEquals('US Dollar', $usd->name);
        $this->assertEquals('$', $usd->symbol);

        $this->assertEquals('EUR', $eur->code);
        $this->assertEquals('Euro', $eur->name);
        $this->assertEquals('€', $eur->symbol);
    }

    public function testReturnsAllCurrenciesWhenNoFilter(): void
    {
        $provider = new CurrenciesInfoProvider(
            new MockCurrencyApiClient('http://fake'),
            new NullLogger()
        );

        $response = $provider->getCurrencies();

        $this->assertNotNull($response);

        $this->assertArrayHasKey('PLN', $response->currencies);
        $this->assertEquals('Polish Zloty', $response->currencies['PLN']->name);
    }

    public function testReturnsNullOnInvalidCurrency(): void
    {
        $provider = new CurrenciesInfoProvider(
            new MockCurrencyApiClient('http://fake'),
            new NullLogger()
        );

        $this->expectException(CurrencyApiException::class);
        $provider->getCurrencies(['XXX']);
    }

    public function testReturnsRates(): void
    {
        $provider = new CurrencyRatesProvider(
            new MockCurrencyApiClient('http://fake'),
            'USD',
            new NullLogger()
        );

        $response = $provider->getRates(null, ['EUR', 'PLN']);

        $this->assertNotNull($response);
        $this->assertArrayHasKey('EUR', $response->rates);
        $this->assertEquals(0.8594871602, $response->rates['EUR']);
        $this->assertEquals(3.6672706166, $response->rates['PLN']);
    }

    public function testReturnsNullOnInvalidRateCurrency(): void
    {
        $provider = new CurrencyRatesProvider(
            new MockCurrencyApiClient('http://fake'),
            'USD',
            new NullLogger()
        );

        $this->expectException(CurrencyApiException::class);
        $provider->getRates(null, ['XXX']);
    }
}
