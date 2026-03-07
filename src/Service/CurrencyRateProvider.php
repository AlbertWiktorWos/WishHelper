<?php

namespace App\Service;

use App\Repository\CurrencyRepository;
use Symfony\Contracts\Cache\CacheInterface;

class CurrencyRateProvider
{
    public function __construct(
        private CurrencyRepository $repository,
        private CacheInterface $cache,
    ) {
    }

    public function getRates(): array
    {
        return $this->cache->get('currency_rates', function ($item) {
            $item->expiresAfter(3600);

            $rates = [];
            foreach ($this->repository->findAll() as $currency) {
                $rates[$currency->getCode()] = (float) $currency->getExchangeRate();
            }

            return $rates;
        });
    }

    public function convert(float $amount, string $from, string $to): float
    {
        $rates = $this->getRates();

        if (!isset($rates[$from]) || !isset($rates[$to])) {
            return $amount;
        }

        $usdAmount = $amount / $rates[$from];

        return $usdAmount * $rates[$to];
    }
}
