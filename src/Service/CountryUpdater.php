<?php

namespace App\Service;

use App\Entity\Country;
use App\Entity\Currency;
use App\Integration\SoapIntegration\CountryApi\Provider\CountryInfoProvider;
use App\Repository\CountryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class CountryUpdater
{
    public function __construct(
        private CountryInfoProvider $countryInfoProvider,
        private CountryRepository $repository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
        private string $defaultBaseCurrency = 'USD',
    ) {
    }

    /**
     * @param string[] $codes ISO codes of countries to update, empty = all
     *
     * @return Country[] updated Country entities
     */
    public function update(array $codes = []): array
    {
        // Pobranie DTO z API
        $countriesFromApi = $this->countryInfoProvider->getCountries($codes);

        if (!$countriesFromApi) {
            $this->logger->info('No countries returned from Provider');

            return [];
        }

        $this->em->beginTransaction();
        try {
            $code = '';
            $defaultCurrency = $this->em->getRepository(Currency::class)->findOneBy(['code' => $this->defaultBaseCurrency]);

            if (!$defaultCurrency) {
                throw new \Exception('There is no default currency');
            }

            $countriesToPersist = [];

            // fetching existing countries in the database
            if (!empty($codes)) {
                $existingCountries = $this->repository->findCountriesByCodes($codes);
            } else {
                $existingCountries = $this->repository->findAllCountries();
            }

            foreach ($countriesFromApi as $code => $dto) {
                $country = $existingCountries[$code] ?? new Country();
                $countryChanged = false;

                if ($country->getName() !== $dto->name) {
                    $country->setName($dto->name);
                    $countryChanged = true;
                }
                if ($country->getFlag() !== $dto->flag) {
                    $country->setFlag($dto->flag);
                    $countryChanged = true;
                }
                if ($country->getContinent() !== $dto->continent) {
                    $country->setContinent($dto->continent);
                    $countryChanged = true;
                }
                $currency = null;
                if ($dto->currency) {
                    $currency = $this->em->getRepository(Currency::class)->findOneBy(['code' => $dto->currency]);
                    if ($country->getCurrency()?->getCode() !== $currency?->getCode() && $currency) {
                        $country->setCurrency($currency);
                        $countryChanged = true;
                    }
                } elseif (!$country->getCurrency()) {  // if we haven't set the currency, we need to set the default
                    $country->setCurrency($defaultCurrency);
                    $countryChanged = true;
                }

                if ($countryChanged || !$country->getId()) {
                    $country->setCode($dto->code);
                    $country->setUpdatedAt(new \DateTimeImmutable());
                    $this->em->persist($country);
                    $countriesToPersist[$dto->code] = $country;
                }
            }

            $this->em->flush();
            $this->em->commit();

            $this->logger->info(sprintf('%d countries successfully updated', count($countriesToPersist)));

            return $countriesToPersist;
        } catch (\Exception $e) {
            $this->em->rollback();
            $this->logger->error(sprintf('Error updating country %s: %s', $code, $e->getMessage()));
            throw $e;
        }
    }
}
