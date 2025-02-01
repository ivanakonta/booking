<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Entity\TimeSlot;
use App\Form\TimeSlotsFormType;
use App\Service\AccessCheckerService;
use App\Service\TimeSlotService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

class TimeSlotController extends AbstractController
{
    private TimeSlotService $timeSlotService;
    private AccessCheckerService $accessCheckerService;

    public function __construct(TimeSlotService $timeSlotService, AccessCheckerService $accessCheckerService)
    {
        $this->timeSlotService = $timeSlotService;
        $this->accessCheckerService = $accessCheckerService;

    }

    #[Route('/{id}/dodaj-timeSlot', name: 'add_timeSlot')]
    public function addTimeSlot(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->accessCheckerService->checkAdminOrManagerAccess($id);

        $restaurant = $entityManager->getRepository(Restaurant::class)->find($id);
        if (!$restaurant) {
            throw $this->createNotFoundException('Restaurant not found');
        }

        $timeSlot = new TimeSlot();
        $form = $this->createForm(TimeSlotsFormType::class, $timeSlot, ['restaurant' => $restaurant]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->timeSlotService->addTimeSlot($restaurant, $timeSlot);

            $this->addFlash('success', [
                'title' => 'TimeSlot dodan',
                'message' => 'TimeSlot uspješno dodan.'
            ]);
            return $this->redirectToRoute('list_timeSlot', ['id' => $id]);
        }

        return $this->render('timeSlots/new.html.twig', [
            'form' => $form->createView(),
            'restaurant' => $restaurant
        ]);
    }

    #[Route('/{id}/timeSlot/{timeSlotId}/edit', name: 'edit_timeSlot')]
    public function editTimeSlot(int $id, int $timeSlotId, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->accessCheckerService->checkAdminOrManagerAccess($id);

        $restaurant = $entityManager->getRepository(Restaurant::class)->find($id);

        $timeSlot = $this->timeSlotService->findTimeSlotById($timeSlotId);
        if (!$timeSlot) {
            throw $this->createNotFoundException('TimeSlot not found');
        }

        $form = $this->createForm(TimeSlotsFormType::class, $timeSlot, ['restaurant' => $restaurant]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->timeSlotService->updateTimeSlot($timeSlot);

            $this->addFlash('success', [
                'title' => 'TimeSlot ažuriran',
                'message' => 'TimeSlot uspješno ažuriran.'
            ]);
            return $this->redirectToRoute('list_timeSlot', ['id' => $id]);
        }

        return $this->render('timeSlots/edit.html.twig', [
            'form' => $form->createView(),
            'restaurant' => $restaurant,
            'timeSlot' => $timeSlot

        ]);
    }

    #[Route('/{id}/timeSlot/{timeSlotId}/delete', name: 'delete_timeSlot')]
    public function deleteTimeSlot(int $id, int $timeSlotId): Response
    {
        $this->accessCheckerService->checkAdminOrManagerAccess($id);

        $timeSlot = $this->timeSlotService->findTimeSlotById($timeSlotId);
        if (!$timeSlot) {
            throw $this->createNotFoundException('TimeSlot not found');
        }
        try {
            // Attempt to delete the korisnik
            $this->timeSlotService->deleteTimeSlot($timeSlot);

            // Success message
            $this->addFlash('success', [
                'title' => 'TimeSlot izbrisan',
                'message' => 'TimeSlot uspješno izbrisan.'
            ]);

        } catch (ForeignKeyConstraintViolationException $e) {
            // If deletion fails due to foreign key constraints, show a warning message
            $this->addFlash('error', [
                'title' => 'Brisanje nije moguće',
                'message' => 'Ne možete izbrisati ovaj timeSlot jer ima povezane podatke u drugim tablicama.',
            ]);
        }

        return $this->redirectToRoute('list_timeSlot', ['id' => $id]);
    }

    #[Route('/{id}/timeSlot', name: 'list_timeSlot')]
    public function listTimeSlot(int $id, EntityManagerInterface $entityManager): Response
    {
        $this->accessCheckerService->checkAdminOrManagerAccess($id);

        $restaurant = $entityManager->getRepository(Restaurant::class)->find($id);
        if (!$restaurant) {
            throw $this->createNotFoundException('Restaurant not found');
        }

        $timeSlots = $this->timeSlotService->listTimeSlot($restaurant);

        return $this->render('timeSlots/list.html.twig', [
            'timeSlots' => $timeSlots,
            'restaurant' => $restaurant,
            'pageTitle' => 'Lista Time slotova',
        ]);
    }
}
