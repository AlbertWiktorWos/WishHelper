<?php

namespace App\Integration\RestIntegration\CurrencyApi;

use App\Integration\RestIntegration\ApiGetRequestInterface;
use App\Integration\RestIntegration\CurrencyApi\Exception\CurrencyApiException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MockCurrencyApiClient implements CurrencyApiClientInterface
{
    public function __construct(
        private string $baseUrl,
    ) {
    }

    public function getRequest(ResourcesNames $resourceName, ApiGetRequestInterface $request): ResponseInterface
    {
        $mockHttpClient = new MockHttpClient();

        $response = $this->buildResponse($resourceName, $request->toQuery());

        $mockHttpClient->setResponseFactory([$response]);

        $response = $mockHttpClient->request(
            'GET',
            $this->baseUrl.'/'.$resourceName->value,
            ['query' => $request->toQuery()]
        );

        if (200 !== $response->getStatusCode()) {
            throw new CurrencyApiException(sprintf('Currency API request failed for resource "%s"', $resourceName->value));
        }

        return $response;
    }

    private function buildResponse(ResourcesNames $resourceName, array $query): MockResponse
    {
        $data = $this->getDefaultData($resourceName);

        if ($this->isInvalidQuery($resourceName, $query)) {
            return $this->getDefaultIncorrectResponse($resourceName);
        }

        $data = $this->applyQueryFilter($resourceName, $data, $query);

        return new MockResponse(json_encode(['data' => $data]), ['http_code' => 200]);
    }

    private function isInvalidQuery(ResourcesNames $resourceName, array $query): bool
    {
        if (!isset($query['currencies'])) {
            return false;
        }

        $data = $this->getDefaultData($resourceName);
        $requested = explode(',', $query['currencies']);

        foreach ($requested as $currency) {
            if (!isset($data[$currency])) {
                return true;
            }
        }

        return false;
    }

    private function applyQueryFilter(ResourcesNames $resourceName, array $data, array $query): array
    {
        if (!isset($query['currencies'])) {
            return $data;
        }

        $requested = explode(',', $query['currencies']);

        return array_intersect_key($data, array_flip($requested));
    }

    protected function getDefaultIncorrectResponse(ResourcesNames $resourceName): MockResponse
    {
        $defaltResponse = [
            'latest' => <<<JSON
{
  "message": "Validation error",
  "errors": {
    "currencies": [
      "The selected currencies is invalid."
    ]
  },
  "info": "For more information, see documentation: https://freecurrencyapi.com/docs/status-codes#_422"
}
JSON,
            'currencies' => <<<JSON
{
  "message": "Validation error",
  "errors": {
    "currencies": [
      "The selected currencies is invalid."
    ]
  },
  "info": "For more information, see documentation: https://freecurrencyapi.com/docs/status-codes#_422"
}
JSON,
        ];

        return new MockResponse($defaltResponse[$resourceName->value], ['http_code' => 422]); // Validation error, please check the list of validation errors
    }

    private function getDefaultData(ResourcesNames $resourceName): array
    {
        return match ($resourceName) {
            ResourcesNames::LATEST => [
                'AUD' => 1.4133002245,
                'BGN' => 1.6650002354,
                'BRL' => 5.2314006013,
                'CAD' => 1.3643401407,
                'CHF' => 0.779180093,
                'CNY' => 6.906700922,
                'CZK' => 20.9413034753,
                'DKK' => 6.421641134,
                'EUR' => 0.8594871602,
                'GBP' => 0.7479711098,
                'HKD' => 7.8172111259,
                'HRK' => 6.4751698684,
                'HUF' => 330.5000402215,
                'IDR' => 16866.102642128,
                'ILS' => 3.0710003115,
                'INR' => 92.1234165567,
                'ISK' => 124.3700135677,
                'JPY' => 156.9860297741,
                'KRW' => 1462.9481469116,
                'MXN' => 17.5978031261,
                'MYR' => 3.9410005286,
                'NOK' => 9.6305717983,
                'NZD' => 1.6844002873,
                'PHP' => 58.3795101932,
                'PLN' => 3.6672706166,
                'RON' => 4.377810648,
                'RUB' => 77.8732103267,
                'SEK' => 9.1743011734,
                'SGD' => 1.274810187,
                'THB' => 31.5870038118,
                'TRY' => 43.9783066942,
                'USD' => 1,
                'ZAR' => 16.3475026408,
            ],

            ResourcesNames::CURRENCIES => [
                'EUR' => [
                    'symbol' => '€',
                    'name' => 'Euro',
                    'symbol_native' => '€',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'EUR',
                    'name_plural' => 'Euros',
                    'type' => 'fiat',
                ],
                'USD' => [
                    'symbol' => '$',
                    'name' => 'US Dollar',
                    'symbol_native' => '$',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'USD',
                    'name_plural' => 'US dollars',
                    'type' => 'fiat',
                ],
                'JPY' => [
                    'symbol' => '¥',
                    'name' => 'Japanese Yen',
                    'symbol_native' => '￥',
                    'decimal_digits' => 0,
                    'rounding' => 0,
                    'code' => 'JPY',
                    'name_plural' => 'Japanese yen',
                    'type' => 'fiat',
                ],
                'BGN' => [
                    'symbol' => 'BGN',
                    'name' => 'Bulgarian Lev',
                    'symbol_native' => 'лв.',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'BGN',
                    'name_plural' => 'Bulgarian leva',
                    'type' => 'fiat',
                ],
                'CZK' => [
                    'symbol' => 'Kč',
                    'name' => 'Czech Republic Koruna',
                    'symbol_native' => 'Kč',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'CZK',
                    'name_plural' => 'Czech Republic korunas',
                    'type' => 'fiat',
                ],
                'DKK' => [
                    'symbol' => 'Dkr',
                    'name' => 'Danish Krone',
                    'symbol_native' => 'kr',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'DKK',
                    'name_plural' => 'Danish kroner',
                    'type' => 'fiat',
                ],
                'GBP' => [
                    'symbol' => '£',
                    'name' => 'British Pound Sterling',
                    'symbol_native' => '£',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'GBP',
                    'name_plural' => 'British pounds sterling',
                    'type' => 'fiat',
                ],
                'HUF' => [
                    'symbol' => 'Ft',
                    'name' => 'Hungarian Forint',
                    'symbol_native' => 'Ft',
                    'decimal_digits' => 0,
                    'rounding' => 0,
                    'code' => 'HUF',
                    'name_plural' => 'Hungarian forints',
                    'type' => 'fiat',
                ],
                'PLN' => [
                    'symbol' => 'zł',
                    'name' => 'Polish Zloty',
                    'symbol_native' => 'zł',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'PLN',
                    'name_plural' => 'Polish zlotys',
                    'type' => 'fiat',
                ],
                'RON' => [
                    'symbol' => 'RON',
                    'name' => 'Romanian Leu',
                    'symbol_native' => 'RON',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'RON',
                    'name_plural' => 'Romanian lei',
                    'type' => 'fiat',
                ],
                'SEK' => [
                    'symbol' => 'Skr',
                    'name' => 'Swedish Krona',
                    'symbol_native' => 'kr',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'SEK',
                    'name_plural' => 'Swedish kronor',
                    'type' => 'fiat',
                ],
                'CHF' => [
                    'symbol' => 'CHF',
                    'name' => 'Swiss Franc',
                    'symbol_native' => 'CHF',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'CHF',
                    'name_plural' => 'Swiss francs',
                    'type' => 'fiat',
                ],
                'ISK' => [
                    'symbol' => 'Ikr',
                    'name' => 'Icelandic Króna',
                    'symbol_native' => 'kr',
                    'decimal_digits' => 0,
                    'rounding' => 0,
                    'code' => 'ISK',
                    'name_plural' => 'Icelandic krónur',
                    'type' => 'fiat',
                ],
                'NOK' => [
                    'symbol' => 'Nkr',
                    'name' => 'Norwegian Krone',
                    'symbol_native' => 'kr',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'NOK',
                    'name_plural' => 'Norwegian kroner',
                    'type' => 'fiat',
                ],
                'HRK' => [
                    'symbol' => 'kn',
                    'name' => 'Croatian Kuna',
                    'symbol_native' => 'kn',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'HRK',
                    'name_plural' => 'Croatian kunas',
                    'type' => 'fiat',
                ],
                'RUB' => [
                    'symbol' => 'RUB',
                    'name' => 'Russian Ruble',
                    'symbol_native' => 'руб.',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'RUB',
                    'name_plural' => 'Russian rubles',
                    'type' => 'fiat',
                ],
                'TRY' => [
                    'symbol' => 'TL',
                    'name' => 'Turkish Lira',
                    'symbol_native' => '₺',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'TRY',
                    'name_plural' => 'Turkish Lira',
                    'type' => 'fiat',
                ],
                'AUD' => [
                    'symbol' => 'AU$',
                    'name' => 'Australian Dollar',
                    'symbol_native' => '$',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'AUD',
                    'name_plural' => 'Australian dollars',
                    'type' => 'fiat',
                ],
                'BRL' => [
                    'symbol' => 'R$',
                    'name' => 'Brazilian Real',
                    'symbol_native' => 'R$',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'BRL',
                    'name_plural' => 'Brazilian reals',
                    'type' => 'fiat',
                ],
                'CAD' => [
                    'symbol' => 'CA$',
                    'name' => 'Canadian Dollar',
                    'symbol_native' => '$',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'CAD',
                    'name_plural' => 'Canadian dollars',
                    'type' => 'fiat',
                ],
                'CNY' => [
                    'symbol' => 'CN¥',
                    'name' => 'Chinese Yuan',
                    'symbol_native' => 'CN¥',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'CNY',
                    'name_plural' => 'Chinese yuan',
                    'type' => 'fiat',
                ],
                'HKD' => [
                    'symbol' => 'HK$',
                    'name' => 'Hong Kong Dollar',
                    'symbol_native' => '$',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'HKD',
                    'name_plural' => 'Hong Kong dollars',
                    'type' => 'fiat',
                ],
                'IDR' => [
                    'symbol' => 'Rp',
                    'name' => 'Indonesian Rupiah',
                    'symbol_native' => 'Rp',
                    'decimal_digits' => 0,
                    'rounding' => 0,
                    'code' => 'IDR',
                    'name_plural' => 'Indonesian rupiahs',
                    'type' => 'fiat',
                ],
                'ILS' => [
                    'symbol' => '₪',
                    'name' => 'Israeli New Sheqel',
                    'symbol_native' => '₪',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'ILS',
                    'name_plural' => 'Israeli new sheqels',
                    'type' => 'fiat',
                ],
                'INR' => [
                    'symbol' => 'Rs',
                    'name' => 'Indian Rupee',
                    'symbol_native' => 'টকা',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'INR',
                    'name_plural' => 'Indian rupees',
                    'type' => 'fiat',
                ],
                'KRW' => [
                    'symbol' => '₩',
                    'name' => 'South Korean Won',
                    'symbol_native' => '₩',
                    'decimal_digits' => 0,
                    'rounding' => 0,
                    'code' => 'KRW',
                    'name_plural' => 'South Korean won',
                    'type' => 'fiat',
                ],
                'MXN' => [
                    'symbol' => 'MX$',
                    'name' => 'Mexican Peso',
                    'symbol_native' => '$',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'MXN',
                    'name_plural' => 'Mexican pesos',
                    'type' => 'fiat',
                ],
                'MYR' => [
                    'symbol' => 'RM',
                    'name' => 'Malaysian Ringgit',
                    'symbol_native' => 'RM',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'MYR',
                    'name_plural' => 'Malaysian ringgits',
                    'type' => 'fiat',
                ],
                'NZD' => [
                    'symbol' => 'NZ$',
                    'name' => 'New Zealand Dollar',
                    'symbol_native' => '$',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'NZD',
                    'name_plural' => 'New Zealand dollars',
                    'type' => 'fiat',
                ],
                'PHP' => [
                    'symbol' => '₱',
                    'name' => 'Philippine Peso',
                    'symbol_native' => '₱',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'PHP',
                    'name_plural' => 'Philippine pesos',
                    'type' => 'fiat',
                ],
                'SGD' => [
                    'symbol' => 'S$',
                    'name' => 'Singapore Dollar',
                    'symbol_native' => '$',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'SGD',
                    'name_plural' => 'Singapore dollars',
                    'type' => 'fiat',
                ],
                'THB' => [
                    'symbol' => '฿',
                    'name' => 'Thai Baht',
                    'symbol_native' => '฿',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'THB',
                    'name_plural' => 'Thai baht',
                    'type' => 'fiat',
                ],
                'ZAR' => [
                    'symbol' => 'R',
                    'name' => 'South African Rand',
                    'symbol_native' => 'R',
                    'decimal_digits' => 2,
                    'rounding' => 0,
                    'code' => 'ZAR',
                    'name_plural' => 'South African rand',
                    'type' => 'fiat',
                ],
            ],
        };
    }
}
