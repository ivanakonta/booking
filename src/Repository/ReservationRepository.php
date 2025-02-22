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

    public function findScheduledByRestaurant(Restaurant $restaurant): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.restaurant = :restaurant')
            ->andWhere('v.status = :status')
            ->setParameter('restaurant', $restaurant)
            ->setParameter('status', 'SCHEDULED') // Postavi vrednost kao string
            ->getQuery()
            ->getResult();
    }
    
    public function findCanceledByRestaurant(Restaurant $restaurant): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.restaurant = :restaurant')
            ->andWhere('v.status = :status')
            ->setParameter('restaurant', $restaurant)
            ->setParameter('status', 'CANCELED')
            ->getQuery()
            ->getResult();
    }
    
    public function findFinishedByRestaurant(Restaurant $restaurant): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.restaurant = :restaurant')
            ->andWhere('v.status = :status')
            ->setParameter('restaurant', $restaurant)
            ->setParameter('status', 'FINISHED')
            ->getQuery()
            ->getResult();
    }
    
    public function findByPeriod(Restaurant $restaurant, string $period)
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant);
    
        if ($period === 'today') {
            $today = new \DateTime('today');
            $qb->andWhere('r.date BETWEEN :start AND :end')
               ->setParameter('start', $today->format('Y-m-d 00:00:00'))
               ->setParameter('end', $today->format('Y-m-d 23:59:59'));
        } elseif ($period === 'this_week') {
            $start = new \DateTime('monday this week');
            $end = new \DateTime('sunday this week');
            $qb->andWhere('r.date BETWEEN :start AND :end')
               ->setParameter('start', $start->format('Y-m-d 00:00:00'))
               ->setParameter('end', $end->format('Y-m-d 23:59:59'));
        } elseif ($period === 'this_month') {
            $start = new \DateTime('first day of this month');
            $end = new \DateTime('last day of this month');
            $qb->andWhere('r.date BETWEEN :start AND :end')
               ->setParameter('start', $start->format('Y-m-d 00:00:00'))
               ->setParameter('end', $end->format('Y-m-d 23:59:59'));
        }
    
        return $qb->getQuery()->getResult();
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
