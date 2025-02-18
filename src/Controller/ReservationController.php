<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Entity\TimeSlot;
use App\Entity\Guest;
use App\Entity\Reservation;
use App\Form\ReservationFormType;
use App\Repository\ReservationRepository;
use App\Service\AccessCheckerService;
use App\Service\ReservationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReservationController extends AbstractController
{
    private ReservationService $reservationService;
    private AccessCheckerService $accessCheckerService;

    public function __construct(ReservationService $reservationService, AccessCheckerService $accessCheckerService)
    {
        $this->reservationService = $reservationService;
        $this->accessCheckerService = $accessCheckerService;
    }

    #[Route('/restaurant/{id}/reservations', name: 'list_reservations')]
    public function listReservations(
        int $id, Request $request, 
        EntityManagerInterface $entityManager, 
        ReservationRepository $reservationRepository
        ): Response
    {
        $this->accessCheckerService->checkAdminOrManagerAccess($id);
        // Fetch the restaurant
        $restaurant = $entityManager->getRepository(Restaurant::class)->find($id);
        if (!$restaurant) {
            throw $this->createNotFoundException('Restaurant not found');
        }
        // Fetch filter parameters from request
        $timeSlotId = $request->query->get('timeSlot') !== null ? (int)$request->query->get('timeSlot') : null;
        $dateFrom = $request->query->get('dateFrom') ? new \DateTime($request->query->get('dateFrom')) : null;
        $dateTo = $request->query->get('dateTo') ? new \DateTime($request->query->get('dateTo')) : null;

        // Convert IDs to entities if they are not null
        $timeSlot = $timeSlotId ? $entityManager->getRepository(TimeSlot::class)->find($timeSlotId) : null;

        // Fetch filtered results and totals
        $reservations = $reservationRepository->findAllByFilters(
            $restaurant,
            $timeSlot,
            $dateFrom,
            $dateTo
        );

        $timeSlots = $entityManager->getRepository(TimeSlot::class)->findBy(['restaurant' => $restaurant]);

        // Fetch the list of reservations for the restaurant
        // $reservations = $this->reservationService->getAllReservationsByRestaurant($restaurant);

        return $this->render('reservation/list.html.twig', [
            'reservations' => $reservations,
            'timeSlots' => $timeSlots,
            'restaurant' => $restaurant,
            'pageTitle' => 'Reservations List',
        ]);
    }

    #[Route('/restaurant/{id}/reservation/new', name: 'new_reservation')]
    public function newReservation(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->accessCheckerService->checkAdminOrManagerAccess($id);
    
        $restaurant = $entityManager->getRepository(Restaurant::class)->find($id);
        if (!$restaurant) {
            throw $this->createNotFoundException('Restaurant not found');
        }
    
        // Fetch available time slots for the restaurant
        $timeSlots = $entityManager->getRepository(TimeSlot::class)->findBy(['restaurant' => $restaurant]);
    
        // Create a new Reservation instance
        $reservation = new Reservation();
        $form = $this->createForm(ReservationFormType::class, $reservation, ['restaurant' => $restaurant]);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Access the data from the form
            $reservationFormData = $form->getData();
            
            // Extract individual data
            $guestEmail = $reservationFormData->getGuest()->getEmail();
            $guestName = $reservationFormData->getGuest()->getName();
            $guestPhoneNumber = $reservationFormData->getGuest()->getPhoneNumber();
            $date = $reservationFormData->getDate(); // Ensure this is a DateTimeImmutable object
            $time = $reservationFormData->getTime();
            $numberOfPersons = $reservationFormData->getNumberOfPersons();
    
            // Call the service to create or update the reservation
            $this->reservationService->createOrUpdateReservation(
                $restaurant, 
                $guestEmail, 
                $guestName, 
                $guestPhoneNumber, 
                $date, 
                $time, 
                $numberOfPersons
            );
    
            // Add a success flash message
            $this->addFlash('success', [
                'title' => 'Reservation Created',
                'message' => 'Your reservation has been successfully created.'
            ]);
    
            // Redirect to the list of reservations
            return $this->redirectToRoute('list_reservations', ['id' => $id]);
        }
    
        return $this->render('reservation/new.html.twig', [
            'restaurant' => $restaurant,
            'form' => $form->createView(),
            'timeSlots' => $timeSlots,
        ]);
    }

    #[Route('/restaurant/{id}/reservation/book', name: 'new_reservation_guest')]
    public function newReservationGuest(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {    
        $restaurant = $entityManager->getRepository(Restaurant::class)->find($id);
        if (!$restaurant) {
            throw $this->createNotFoundException('Restaurant not found');
        }
    
        // Fetch available time slots for the restaurant
        $timeSlots = $entityManager->getRepository(TimeSlot::class)->findBy(['restaurant' => $restaurant]);
    
        // Create a new Reservation instance
        $reservation = new Reservation();
        $form = $this->createForm(ReservationFormType::class, $reservation, ['restaurant' => $restaurant]);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Access the data from the form
            $reservationFormData = $form->getData();
            
            // Extract individual data
            $guestEmail = $reservationFormData->getGuest()->getEmail();
            $guestName = $reservationFormData->getGuest()->getName();
            $guestPhoneNumber = $reservationFormData->getGuest()->getPhoneNumber();
            $date = $reservationFormData->getDate(); // Ensure this is a DateTimeImmutable object
            $time = $reservationFormData->getTime();
            $numberOfPersons = $reservationFormData->getNumberOfPersons();
    
            // Call the service to create or update the reservation
            $this->reservationService->createOrUpdateReservation(
                $restaurant, 
                $guestEmail, 
                $guestName, 
                $guestPhoneNumber, 
                $date, 
                $time, 
                $numberOfPersons
            );
    
            // Add a success flash message
            $this->addFlash('success', [
                'title' => 'Reservation Created',
                'message' => 'Your reservation has been successfully created.'
            ]);
    
            // Redirect to the list of reservations
            return $this->redirectToRoute('restaurants_list', ['id' => $id]);
        }
    
        return $this->render('reservation/new_guest.html.twig', [
            'restaurant' => $restaurant,
            'form' => $form->createView(),
            'timeSlots' => $timeSlots,
            'pageTitle' => sprintf('Kreiranje rezervacije za %s', $restaurant->getName()),
        ]);
    }
    

    #[Route('/restaurant/{id}/reservation/{reservationId}/edit', name: 'edit_reservation')]
    public function editReservation(
        int $id,
        int $reservationId,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->accessCheckerService->checkAdminOrManagerAccess($id);
    
        $restaurant = $entityManager->getRepository(Restaurant::class)->find($id);
        if (!$restaurant) {
            throw $this->createNotFoundException('Restaurant not found');
        }
    
        // Get the existing reservation
        $reservation = $this->reservationService->getReservationById($reservationId);
    
        if (!$reservation || $reservation->getRestaurant()->getId() !== $id) {
            throw $this->createNotFoundException('Reservation not found');
        }
    
        // Fetch available time slots for the restaurant
        $timeSlots = $entityManager->getRepository(TimeSlot::class)->findBy(['restaurant' => $restaurant]);
    
        // Create the form for the reservation edit
        $form = $this->createForm(ReservationFormType::class, $reservation, [
            'restaurant' => $restaurant
        ]);
        
        // Pre-fill the form data with existing reservation details
        $form->setData($reservation);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Access the data from the combined form
            $reservationFormData = $form->getData();
            
            // Extract individual data
            $guestEmail = $reservationFormData->getGuest()->getEmail();
            $guestName = $reservationFormData->getGuest()->getName();
            $guestPhoneNumber = $reservationFormData->getGuest()->getPhoneNumber();
            $date = $reservationFormData->getDate(); // Ensure this is a DateTimeImmutable object
            $time = $reservationFormData->getTime();
            $numberOfPersons = $reservationFormData->getNumberOfPersons();
    
            // Call the service to update the reservation
            $this->reservationService->updateReservation(
                $reservationId,
                $restaurant,
                $guestEmail,
                $guestName,
                $guestPhoneNumber,
                $date,
                $time,
                $numberOfPersons
            );
    
            // Add a success flash message
            $this->addFlash('success', 'Reservation updated successfully!');
    
            // Redirect to the list of reservations
            return $this->redirectToRoute('list_reservations', ['id' => $id]);
        }
    
        return $this->render('reservation/edit.html.twig', [
            'restaurant' => $restaurant,
            'reservation' => $reservation,
            'form' => $form->createView(),
            'timeSlots' => $timeSlots,
        ]);
    }
    

    #[Route('/restaurant/{id}/reservation/{reservationId}/delete', name: 'delete_reservation')]
    public function deleteReservation(int $id, int $reservationId, EntityManagerInterface $entityManager): Response
    {
        $this->accessCheckerService->checkAdminOrManagerAccess($id);

        $restaurant = $entityManager->getRepository(Restaurant::class)->find($id);
        if (!$restaurant) {
            throw $this->createNotFoundException('Restaurant not found');
        }

        $reservation = $this->reservationService->getReservationById($reservationId);

        if (!$reservation || $reservation->getRestaurant()->getId() !== $id) {
            throw $this->createNotFoundException('Reservation not found');
        }

        $this->reservationService->deleteReservation($reservationId);

        $this->addFlash('success', 'Reservation deleted successfully!');

        return $this->redirectToRoute('list_reservations', ['id' => $id]);
    }

    #[Route('/restaurant/{id}/guests', name: 'list_guests')]
    public function getAllGuestsForRestaurant(
        int $id,
        EntityManagerInterface $entityManager
    ): Response {
        $this->accessCheckerService->checkAdminOrManagerAccess($id);

        $restaurant = $entityManager->getRepository(Restaurant::class)->find($id);

        if (!$restaurant) {
            throw $this->createNotFoundException('Restaurant not found');
        }

        // Fetch all guests for the restaurant using the service
        $guests = $this->reservationService->getAllGuestsForRestaurant($restaurant);

        // Render the guests in a template or return them as JSON
        return $this->render('guest/list.html.twig', [
            'restaurant' => $restaurant,
            'guests' => $guests,
            'pageTitle' => 'Guests list'
        ]);
    }
}
