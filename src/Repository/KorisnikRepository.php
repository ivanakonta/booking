<?php

namespace App\Repository;

use App\Entity\Korisnik;
use App\Entity\Renter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Korisnik>
 */
class KorisnikRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Korisnik::class);
    }

    /**
     * @return Korisnik[] Returns an array of Korisnik objects
     */
    public function findByRenter(Renter $renter)
    {
        return $this->createQueryBuilder('k')
            ->where('k.renter = :renter')
            ->setParameter('renter', $renter)
            ->getQuery()
            ->getResult();
    }

    public function searchByQueryAndRenter(string $query, Renter $renter)
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.firstName LIKE :query OR k.lastName LIKE :query OR k.email LIKE :query')
            ->andWhere('k.renter = :renter')
            ->setParameter('query', '%' . $query . '%')
            ->setParameter('renter', $renter)
            ->getQuery()
            ->getResult();
    }

    public function findActiveByRenter(Renter $renter)
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.renter = :renter')
            ->andWhere('k.isActive = true')
            ->setParameter('renter', $renter)
            ->getQuery()
            ->getResult();
    }

    public function findInactiveByRenter(Renter $renter)
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.renter = :renter')
            ->andWhere('k.isActive = false')
            ->setParameter('renter', $renter)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Korisnik[] Returns an array of Korisnik objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('k')
    //            ->andWhere('k.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('k.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Korisnik
    //    {
    //        return $this->createQueryBuilder('k')
    //            ->andWhere('k.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
