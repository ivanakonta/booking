<?php

namespace App\Controller;

use App\Entity\AddWorkingDays;
use App\Entity\NonWorkingDays;
use App\Entity\Restaurant;
use App\Entity\WorkingDays;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WorkingDaysController extends AbstractController
{
    #[Route('/working-day-remove/{id}', name: 'remove_working_day')]
    public function removeWorkingDay(int $id, EntityManagerInterface $entityManager): Response
    {
        // Retrieve the NonWorkingDays entity from the database based on $id
        $addWorkingDay = $entityManager->getRepository(WorkingDays::class)->find($id);

        if (!$addWorkingDay) {
            throw $this->createNotFoundException('Working day not found');
        }

        // Remove the WorkingDays entity from the database
        $entityManager->remove($addWorkingDay);
        $entityManager->flush();

        // Add a flash message for success
        $this->addFlash('success', ['title' => 'Radni dan izbrisan', 'message' => 'Radni dan uspješno izbrisan.']);

        // Redirect to the edit restaurant page or any other desired page
        return $this->redirectToRoute('edit_restaurant', ['id' => $addWorkingDay->getRestaurant()->getId()]);
    }

    #[Route('/non-working-day-remove/{id}', name: 'remove_non_working_day')]
    public function removeNonWorkingDay(int $id, EntityManagerInterface $entityManager): Response
    {
        // Retrieve the NonWorkingDays entity from the database based on $id
        $nonWorkingDay = $entityManager->getRepository(NonWorkingDays::class)->find($id);

        if (!$nonWorkingDay) {
            throw $this->createNotFoundException('Non-working day not found');
        }

        // Remove the NonWorkingDays entity from the database
        $entityManager->remove($nonWorkingDay);
        $entityManager->flush();

        // Add a flash message for success
        $this->addFlash('success', ['title' => 'Neradni dan izbrisan', 'message' => 'Neradni dan uspješno izbrisan.']);

        // Redirect to the edit restaurant page or any other desired page
        return $this->redirectToRoute('edit_restaurant', ['id' => $nonWorkingDay->getRestaurant()->getId()]);
    }

    #[Route('/non-working-day-add-today/{id}', name: 'disable_booking_today')]
    public function disableBookingToday(int $id, EntityManagerInterface $entityManager): Response
    {
        $restaurant = $entityManager->getRepository(Restaurant::class)->find($id);

        if (!$restaurant) {
            throw $this->createNotFoundException('restaurant not found');
        }

        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        $nonWorkingDayRepo = $entityManager->getRepository(NonWorkingDays::class);
        $existingNonWorkingDay = $nonWorkingDayRepo->findOneBy([
            'restaurant' => $restaurant,
            'date' => $today->format('Y-m-d'),
        ]);

        if ($existingNonWorkingDay) {
            // If a non-working day exists for today, remove it
            $entityManager->remove($existingNonWorkingDay);
            $entityManager->flush();

            $this->addFlash('success', [
                'title' => 'Rezervacije isključene',
                'message' => 'Rezervacije za današnji datum su uključene.',
            ]);
        } else {
            // If no non-working day exists for today, create it
            $nonWorkingDays = new NonWorkingDays();
            $nonWorkingDays->setDate($today->format('Y-m-d'));
            $nonWorkingDays->setDescription('Disabled Booking for ' . $today->format('d.m.Y'));

            $restaurant->addNonWorkingDay($nonWorkingDays);
            $entityManager->persist($nonWorkingDays);
            $entityManager->flush();

            $this->addFlash('success', [
                'title' => 'BRezervacije isključene',
                'message' => 'Rezervacije za današnji datum su onemogućene.',
            ]);
        }

        return $this->redirectToRoute('edit_restaurant', ['id' => $id]);
    }
}
