<?php

namespace App\Controller;

use App\Entity\Pilana;
use App\Entity\Renter;
use App\Form\VehicleFormType;
use App\Form\VrstaDrvetaFormType;
use App\Repository\VehicleRepository;
use App\Repository\VrsteDrvetaRepository;
use App\Service\AccessCheckerService;
use App\Service\VehicleService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

class VehicleController extends AbstractController
{
    private VehicleService $vehicleService;
    private AccessCheckerService $accessCheckerService;

    public function __construct(VehicleService $vehicleService, AccessCheckerService $accessCheckerService)
    {
        $this->vehicleService = $vehicleService;
        $this->accessCheckerService = $accessCheckerService;

    }

    #[Route('/{id}/dodaj-vozilo', name: 'add_vozilo')]
    public function addVozilo(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->accessCheckerService->checkAdminOrManagerAccess($id);

        $renter = $entityManager->getRepository(Renter::class)->find($id);
        if (!$renter) {
            throw $this->createNotFoundException('Renter not found');
        }

        $vehicle = $this->vehicleService->createVehicleInstance();
        $form = $this->createForm(VehicleFormType::class, $vehicle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->vehicleService->saveVehicle($vehicle, $renter);

            $this->addFlash('success', [
                'title' => 'Vozilo dodano',
                'message' => 'Vozilo uspješno dodano.'
            ]);
            return $this->redirectToRoute('list_vozila', ['id' => $id]);
        }

        return $this->render('vehicle/new.html.twig', [
            'form' => $form->createView(),
            'renter' => $renter
        ]);
    }

    #[Route('/{id}/vozila/{vehicleId}/edit', name: 'edit_vozilo')]
    public function editVozilo(int $id, int $vehicleId, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->accessCheckerService->checkAdminOrManagerAccess($id);

        $renter = $entityManager->getRepository(Renter::class)->find($id);

        $vehicle = $this->vehicleService->findVehiclesByIdAndRenterId($vehicleId, $id);
        if (!$vehicle) {
            throw $this->createNotFoundException('Isporucitelj not found');
        }

        $form = $this->createForm(VehicleFormType::class, $vehicle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->vehicleService->updateVehicle($vehicle);

            $this->addFlash('success', [
                'title' => 'Vozilo ažurirano',
                'message' => 'Vozilo je uspješno ažurirano.'
            ]);
            return $this->redirectToRoute('list_vozila', ['id' => $id]);
        }

        return $this->render('vehicle/edit.html.twig', [
            'form' => $form->createView(),
            'renter' => $renter,
            'vehicle' => $vehicle

        ]);
    }

    #[Route('/{id}/vozila/{vehicleId}/delete', name: 'delete_vozilo')]
    public function deleteVozilo(int $id, int $vehicleId): Response
    {
        $this->accessCheckerService->checkAdminOrManagerAccess($id);

        $vehicle = $this->vehicleService->findVehiclesByIdAndRenterId($vehicleId, $id);
        if (!$vehicle) {
            throw $this->createNotFoundException('Vehicle not found');
        }
        try {
            // Attempt to delete the korisnik
            $this->vehicleService->deleteVehicle($vehicle);

            // Success message
            $this->addFlash('success', [
                'title' => 'Vozilo izbrisnao',
                'message' => 'Vozilo uspješno izbrisano.'
            ]);

        } catch (ForeignKeyConstraintViolationException $e) {
            // If deletion fails due to foreign key constraints, show a warning message
            $this->addFlash('error', [
                'title' => 'Brisanje nije moguće',
                'message' => 'Ne možete izbrisati ovo vozilo jer ima povezane podatke u drugim tablicama.',
            ]);
        }

        return $this->redirectToRoute('list_vozila', ['id' => $id]);
    }

    #[Route('/{id}/vozila', name: 'list_vozila')]
    public function listVozila(int $id, Request $request, VehicleRepository $vehicleRepository, EntityManagerInterface $entityManager): Response
    {
        $this->accessCheckerService->checkAdminOrManagerAccess($id);

        $renter = $entityManager->getRepository(Renter::class)->find($id);
        if (!$renter) {
            throw $this->createNotFoundException('Renter not found');
        }

        // Get filter type from request (either 'active' or 'inactive')
        $filter = $request->query->get('filter', 'all');
        // Handle search query
        $searchQuery = $request->query->get('search');

        if ($searchQuery) {
            $vehicles = $vehicleRepository->searchByQueryAndRenter($searchQuery, $renter);
        } else {
            if ($filter === 'active') {
                $vehicles = $vehicleRepository->findActiveByRenter($renter);
            } elseif ($filter === 'inactive') {
                $vehicles = $vehicleRepository->findInactiveByRenter($renter);
            } elseif ($filter === 'quad') {
                $vehicles = $vehicleRepository->findByTypeAndRenter('quad', $renter);
            } elseif ($filter === 'suv') {
                $vehicles = $vehicleRepository->findByTypeAndRenter('suv', $renter);
            } else {
                $vehicles = $this->vehicleService->findAllVehiclesByRenterId($renter);
            }
        }

        return $this->render('vehicle/list.html.twig', [
            'vehicles' => $vehicles,
            'renter' => $renter,
            'pageTitle' => 'Lista vozila',
            'searchQuery' => $searchQuery,
        ]);
    }
}