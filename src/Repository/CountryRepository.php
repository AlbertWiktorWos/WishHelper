<?php

namespace App\Repository;

use App\Entity\Country;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Country>
 */
class CountryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Country::class);
    }

    public function findCountriesByCodes(array $codes): array
    {
        return $this->createQueryBuilder('c', 'c.code')
            ->andWhere('c.code IN (:codes)')
            ->setParameter('codes', $codes)
            ->orderBy('c.code', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllCountries(): array
    {
        return $this->createQueryBuilder('c', 'c.code')
            ->orderBy('c.code', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
