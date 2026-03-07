<?php

namespace App\Service;

use App\Entity\Currency;
use App\Integration\RestIntegration\CurrencyApi\DTO\CurrenciesResponse;
use App\Integration\RestIntegration\CurrencyApi\DTO\LatestRatesResponse;
use App\Integration\RestIntegration\CurrencyApi\Provider\CurrenciesInfoProvider;
use App\Integration\RestIntegration\CurrencyApi\Provider\CurrencyRatesProvider;
use App\Repository\CurrencyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class CurrencyUpdater
{
    public function __construct(
        private CurrencyRatesProvider $currencyRatesProvider,
        private CurrenciesInfoProvider $currenciesInfoProvider,
        private CurrencyRepository $repository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
        private \Symfony\Contracts\Cache\CacheInterface $cache,
    ) {
    }

    public function update(array $codes = []): array
    {
        $ratesFromApi = $this->currencyRatesProvider->getRates(null, $codes);
        $currenciesFromApi = $this->currenciesInfoProvider->getCurrencies($codes);

        if (!$currenciesFromApi instanceof CurrenciesResponse || !$ratesFromApi instanceof LatestRatesResponse) {
            return [];
        }

        $this->em->beginTransaction();
        try {
            $currenciesToPersist = [];
            $code = '';

            $codes = array_keys($ratesFromApi->rates);
            $currenciesEntities = $this->repository->findCurrenciesByCodes($codes);

            foreach ($currenciesFromApi->currencies as $code => $apiCurrency) {
                $currency = $currenciesEntities[$code] ?? null;
                $currencyChanged = false;

                if (!$currency) {
                    $currency = new Currency();
                    $currency->setCode($code);
                    $currencyChanged = true;
                }

                if ($apiCurrency->name !== $currency->getName()) {
                    $currency->setName($apiCurrency->name);
                    $currencyChanged = true;
                }

                if ($apiCurrency->symbol !== $currency->getSymbol()) {
                    $currency->setSymbol($apiCurrency->symbol);
                    $currencyChanged = true;
                }

                if ($currencyChanged) {
                    $currenciesToPersist[$code] = $currency;
                }

                $currenciesEntities[$code] = $currency;
            }

            foreach ($ratesFromApi->rates as $code => $rate) {
                $currency = $currenciesEntities[$code] ?? null;
                if (!$currency) {
                    continue;
                }

                if ((float) $currency->getExchangeRate() === (float) $rate) {
                    continue;
                }

                $currency->setExchangeRate((string) $rate);
                $currenciesToPersist[$code] = $currency;
            }

            foreach ($currenciesToPersist as $currencyToPersist) {
                $currencyToPersist->setUpdatedAt(new \DateTimeImmutable());
                $this->em->persist($currencyToPersist);
            }

            $this->em->flush();
            $this->em->commit();

            $this->cache->delete('currencies_collection');
            $this->cache->delete('currency_rates');
        } catch (\Exception $exception) {
            $this->em->rollback();
            $this->logger->info(sprintf('an error occurred while updating the %s currency', $code));
            throw $exception;
        }

        $this->logger->info(sprintf('%d currencies successfully updated', count($currenciesToPersist)));

        return $currenciesToPersist;
    }
}
