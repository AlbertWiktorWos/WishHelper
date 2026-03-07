<?php

namespace App\Tests\Integration;

use App\Integration\SoapIntegration\CountryApi\Mapper\CountryMapper;
use App\Integration\SoapIntegration\CountryApi\MockCountryComm;
use App\Integration\SoapIntegration\CountryApi\Provider\CountryInfoProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class CountryInfoProviderTest extends TestCase
{
    private CountryInfoProvider $provider;

    protected function setUp(): void
    {
        $comm = new MockCountryComm(new NullLogger());
        $mapper = new CountryMapper();

        $this->provider = new CountryInfoProvider($comm, $mapper);
    }

    public function testGetCountriesHappyPath(): void
    {
        $countries = $this->provider->getCountries();

        $this->assertCount(3, $countries);
        $this->assertEquals('PL', $countries['PL']->code);
        $this->assertEquals('Poland', $countries['PL']->name);
    }

    public function testGetCountriesFilteredHappyPath(): void
    {
        $countries = $this->provider->getCountries(['DE']);

        $this->assertCount(1, $countries);
        $this->assertEquals('DE', $countries['DE']->code);
        $this->assertEquals('Germany', $countries['DE']->name);
    }

    public function testGetCountriesBadPathWhenCodeNotExists(): void
    {
        $countries = $this->provider->getCountries(['XX']);

        $this->assertIsArray($countries);
        $this->assertCount(0, $countries);
    }

    public function testGetCountryInfoBadPath(): void
    {
        $country = $this->provider->getCountryInfo('XX');

        $this->assertEquals('', $country->code);
        $this->assertEquals('Country not found in the database', $country->name);
    }
}
