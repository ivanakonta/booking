<?php

namespace App\Repository;

use App\Entity\Korisnik;
use App\Entity\Restaurant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Svg\Tag\Rect;

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
    public function findByRestaurant(Restaurant $restaurant)
    {
        return $this->createQueryBuilder('k')
            ->where('k.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->getQuery()
            ->getResult();
    }

    public function searchByQueryAndRestaurant(string $query, Restaurant $restaurant)
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.firstName LIKE :query OR k.lastName LIKE :query OR k.email LIKE :query')
            ->andWhere('k.restaurant = :restaurant')
            ->setParameter('query', '%' . $query . '%')
            ->setParameter('restaurant', $restaurant)
            ->getQuery()
            ->getResult();
    }

    public function findActiveByRestaurant(Restaurant $restaurant)
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.restaurant = :restaurant')
            ->andWhere('k.isActive = true')
            ->setParameter('restaurant', $restaurant)
            ->getQuery()
            ->getResult();
    }

    public function findInactiveByRestaurant(Restaurant $restaurant)
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.restaurant = :restaurant')
            ->andWhere('k.isActive = false')
            ->setParameter('restaurant', $restaurant)
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
