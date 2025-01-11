<?php

namespace App\Repository;

use App\Entity\Renter;
use App\Entity\Vehicle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vehicle>
 */
class VehicleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicle::class);
    }

    public function searchByQueryAndRenter(string $query, Renter $renter)
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.name LIKE :query')
            ->andWhere('k.renter = :renter')
            ->setParameter('query', '%' . $query . '%')
            ->setParameter('renter', $renter)
            ->getQuery()
            ->getResult();
    }

    public function findActiveByRenter(Renter $renter): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.renter = :renter')
            ->andWhere('v.available = true')
            ->setParameter('renter', $renter)
            ->getQuery()
            ->getResult();
    }

    public function findInactiveByRenter(Renter $renter): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.renter = :renter')
            ->andWhere('v.available = false')
            ->setParameter('renter', $renter)
            ->getQuery()
            ->getResult();
    }

    public function findByTypeAndRenter(string $type, Renter $renter): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.renter = :renter')
            ->andWhere('v.type = :type')
            ->setParameter('renter', $renter)
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Vehicle[] Returns an array of Vehicle objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Vehicle
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
