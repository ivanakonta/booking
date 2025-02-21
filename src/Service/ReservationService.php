<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Entity\Guest;
use App\Entity\Restaurant;
use App\Entity\TimeSlot;
use App\Repository\GuestRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReservationService
{
    private GuestRepository $guestRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        GuestRepository $guestRepository, 
        EntityManagerInterface $entityManager, 
        )
    {
        $this->guestRepository = $guestRepository;
        $this->entityManager = $entityManager;
    }

    // Create or update a reservation
    public function createOrUpdateReservation(
        Restaurant $restaurant,
        string $guestEmail, // Email passed as the identifier
        string $guestName,  // Name of the guest
        string $guestPhoneNumber,  // Phone number of the guest
        DateTimeImmutable $date,
        TimeSlot $time,
        int $numberOfPersons
    ): Reservation {
        // Check if the guest already exists by email
        $guest = $this->guestRepository->findOneByEmail($guestEmail);

        if (!$guest) {
            // If the guest doesn't exist, create a new guest
            $guest = new Guest();
            $guest->setEmail($guestEmail)
                ->setName($guestName)
                ->setPhoneNumber($guestPhoneNumber);

            // Persist new guest in the database
            $this->entityManager->persist($guest);
            $this->entityManager->flush();  // Make sure the guest is saved
        } else {
            // If the guest exists, update their name and phone number
            $guest->setName($guestName)
                ->setPhoneNumber($guestPhoneNumber);

            $this->entityManager->flush();  // Update guest record
        }

        // Create the reservation and associate with the guest
        $reservation = new Reservation();
        $reservation->setRestaurant($restaurant)
            ->setGuest($guest)
            ->setDate($date)
            ->setTime($time)
            ->setNumberOfPersons($numberOfPersons)
            ->setStatus('SCHEDULED');

        // Persist the reservation
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

        return $reservation;
    }

    // Get all reservations
    public function getAllReservationsByRestaurant(Restaurant $restaurant): array
    {
        return $this->entityManager->getRepository(Reservation::class)->findAllByRestaurant($restaurant);
    }

    // Get reservation by ID
    public function getReservationById(int $id): Reservation
    {
        $reservation = $this->entityManager->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            throw new NotFoundHttpException('Reservation not found');
        }

        return $reservation;
    }

    // Update a reservation
    public function updateReservation(
        int $id,
        Restaurant $restaurant,
        string $guestEmail,
        string $guestName,
        string $guestPhoneNumber,
        DateTimeImmutable $date,
        TimeSlot $time,
        int $numberOfPersons
    ): Reservation {
        $reservation = $this->getReservationById($id);

        // Check if the guest already exists by email
        $guest = $this->guestRepository->findOneByEmail($guestEmail);

        if (!$guest) {
            // If the guest doesn't exist, create a new guest
            $guest = new Guest();
            $guest->setEmail($guestEmail)
                ->setName($guestName)
                ->setPhoneNumber($guestPhoneNumber);

            // Persist new guest in the database
            $this->entityManager->persist($guest);
            $this->entityManager->flush();
        } else {
            // If the guest exists, update their name and phone number
            $guest->setName($guestName)
                ->setPhoneNumber($guestPhoneNumber);

            $this->entityManager->flush();  // Update guest record
        }

        // Update the reservation
        $reservation->setRestaurant($restaurant)
            ->setGuest($guest)
            ->setDate($date)
            ->setTime($time)
            ->setNumberOfPersons($numberOfPersons);

        $this->entityManager->flush();

        return $reservation;
    }

    // Delete a reservation
    public function deleteReservation(int $id): void
    {
        $reservation = $this->getReservationById($id);

        $this->entityManager->remove($reservation);
        $this->entityManager->flush();
    }

    public function cancelReservation(Reservation $reservation): void
    {
        $reservation->setStatus('CANCELED');
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

    }

    public function finishedReservation(Reservation $reservation): void
    {
        $reservation->setStatus('FINISHED');
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

    }

    public function guestCancelReservation(Reservation $reservation): void
    {
        $reservation->setStatus('FINISHED');
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

    }

    public function getAllGuestsForRestaurant(Restaurant $restaurant): array
    {
        // Fetch all reservations for the restaurant
        $reservations = $this->entityManager->getRepository(Reservation::class)
            ->findBy(['restaurant' => $restaurant]);
    
        // Create an array to store unique guests
        $guests = [];
    
        // Iterate over the reservations and collect guests
        foreach ($reservations as $reservation) {
            $guest = $reservation->getGuest();
        
            if ($guest && !in_array($guest, $guests, true)) {
                $guests[] = $guest; // Add unique guests
            }
        }
    
        return $guests;
    }
}
