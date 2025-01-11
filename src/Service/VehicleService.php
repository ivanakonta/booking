<?php

namespace App\Service;

use App\Entity\Renter;
use App\Entity\Vehicle;
use App\Repository\VehicleRepository;
use Doctrine\ORM\EntityManagerInterface;

class VehicleService
{
    private EntityManagerInterface $entityManager;
    private VehicleRepository $vehicleRepository;

    public function __construct(EntityManagerInterface $entityManager, VehicleRepository $vehicleRepository)
    {
        $this->entityManager = $entityManager;
        $this->vehicleRepository = $vehicleRepository;
    }

    /**
     * Find all Vehicle entities by Renter ID.
     */
    public function findAllVehiclesByRenterId(Renter $renter): array
    {
        return $this->entityManager->getRepository(Vehicle::class)->findBy(['renter' => $renter]);
    }

    /**
     * Find a single Vehicle entity by its ID and Renter ID.
     */
    public function findVehiclesByIdAndRenterId(int $id, int $renterId): ?Vehicle
    {
        return $this->vehicleRepository->findOneBy(['id' => $id, 'renter' => $renterId]);
    }

    /**
     * Create a new instance of Vehicle.
     */
    public function createVehicleInstance(): Vehicle
    {
        return new Vehicle();
    }

    /**
     * Save a new or existing Vehicle entity.
     */
    public function saveVehicle(Vehicle $vehicle, Renter $renter): void
    {
        $vehicle->setRenter($renter);
        $this->entityManager->persist($vehicle);
        $this->entityManager->flush();
    }

    /**
     * Update an existing Vehicle entity.
     */
    public function updateVehicle(Vehicle $vehicle): void
    {
        $vehicle->setModifiedAt(new \DateTimeImmutable());
        $this->entityManager->persist($vehicle);
        $this->entityManager->flush();
    }

    /**
     * Delete an Vehicle entity.
     */
    public function deleteVehicle(Vehicle $vehicle): void
    {
        $this->entityManager->remove($vehicle);
        $this->entityManager->flush();
    }
}