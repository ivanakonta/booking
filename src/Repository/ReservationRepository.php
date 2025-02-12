<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\Restaurant;
use App\Entity\TimeSlot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    // Custom method to get all reservations for a specific restaurant
    public function findAllByRestaurant(Restaurant $restaurant): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->getQuery()
            ->getResult();
    }

    public function findAllByFilters(
        ?Restaurant $restaurant,
        ?TimeSlot $time,
        ?\DateTimeInterface $dateFrom,
        ?\DateTimeInterface $dateTo
    ): array {
        $qb = $this->createQueryBuilder('p');

        if ($restaurant) {
            $qb->andWhere('p.restaurant = :restaurant')
               ->setParameter('restaurant', $restaurant);
        }
        if ($time) {
            $qb->andWhere('p.time = :time')
               ->setParameter('time', $time);
        }
        if ($dateFrom) {
            $qb->andWhere('p.date >= :dateFrom')
               ->setParameter('dateFrom', $dateFrom);
        }
        if ($dateTo) {
            $qb->andWhere('p.date <= :dateTo')
               ->setParameter('dateTo', $dateTo);
        }

        $results = $qb->getQuery()->getResult();

        return $results;
    }

    //    /**
    //     * @return Reservation[] Returns an array of Reservation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reservation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
