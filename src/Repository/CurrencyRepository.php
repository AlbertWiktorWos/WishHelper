<?php

namespace App\Repository;

use App\Entity\Currency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Currency>
 */
class CurrencyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Currency::class);
    }

    public function findCurrenciesByCodes(array $codes): array
    {
        return $this->createQueryBuilder('c', 'c.code')
            ->andWhere('c.code IN (:codes)')
            ->setParameter('codes', $codes)
            ->orderBy('c.code', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
