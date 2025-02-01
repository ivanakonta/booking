<?php

namespace App\Service;

use App\Entity\Pilana;
use App\Entity\Proizvod;
use App\Entity\Restaurant;
use App\Entity\TimeSlot;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Time;

class TimeSlotService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addTimeSlot(Restaurant $restaurant, TimeSlot $timeSlot): void
    {
        $timeSlot->setRestaurant($restaurant);
        $this->entityManager->persist($timeSlot);
        $this->entityManager->flush();
    }

    public function updateTimeSlot(TimeSlot $timeSlot): void
    {
        $timeSlot->setModifiedAt(new \DateTimeImmutable());

        $this->entityManager->persist($timeSlot);
        $this->entityManager->flush();
    }

    public function deleteTimeSlot(TimeSlot $timeSlot): void
    {
        $this->entityManager->remove($timeSlot);
        $this->entityManager->flush();
    }

    public function findTimeSlotById(int $timeSlotId): ?TimeSlot
    {
        return $this->entityManager->getRepository(TimeSlot::class)->find($timeSlotId);
    }

    public function listTimeSlot(Restaurant $restaurant): array
    {
        return $this->entityManager->getRepository(TimeSlot::class)->findBy(['restaurant' => $restaurant]);
    }
}