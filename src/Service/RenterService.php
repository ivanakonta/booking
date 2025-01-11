<?php

namespace App\Service;

use App\Entity\Korisnik;
use App\Entity\Renter;
use App\Repository\RenterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Common\Collections\Collection;

class RenterService
{
    private RenterRepository $renterRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(RenterRepository $renterRepository, EntityManagerInterface $entityManager)
    {
        $this->renterRepository = $renterRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Retrieves all Renter entities.
     *
     * @return Collection
     */
    public function getAllRenters(): array
    {
        return $this->renterRepository->findAll();
    }

    /**
     * Retrieves a Renter entity by its ID.
     *
     * @param int $id
     * @return Renter
     * @throws EntityNotFoundException
     */
    public function getRenterById(int $id): Renter
    {
        $renter = $this->renterRepository->find($id);

        if (!$renter) {
            throw new EntityNotFoundException('Renter not found');
        }

        return $renter;
    }

    /**
     * Adds a Korisnik to a Renter.
     *
     * @param Renter $renter
     * @param Korisnik $korisnik
     */
    public function addUserToRenter(Renter $renter, Korisnik $korisnik): void
    {
        if (!$renter->getKorisniks()->contains($korisnik)) {
            $renter->addKorisnik($korisnik);
            $this->entityManager->persist($renter);
            $this->entityManager->flush();
        }
    }

    /**
     * Removes a Korisnik from a Renter.
     *
     * @param Renter $renter
     * @param Korisnik $korisnik
     */
    public function removeUserFromRenter(Renter $renter, Korisnik $korisnik): void
    {
        if ($renter->getKorisniks()->contains($korisnik)) {
            $renter->removeKorisnik($korisnik);
            $this->entityManager->persist($renter);
            $this->entityManager->flush();
        }
    }

    /**
     * Creates a new Renter.
     *
     * @param string $name
     * @return Renter
     */
    public function createRenter(Renter $renter): void
    {

        $this->entityManager->persist($renter);
        $this->entityManager->flush();

    }

    /**
     * Updates an existing Renter.
     *
     * @param Renter $renter
     * @param string $name
     */
    public function updateRenter(Renter $renter): void
    {
        $renter->setModifiedAt(new \DateTimeImmutable());

        $this->entityManager->persist($renter);
        $this->entityManager->flush();
    }

    /**
     * Deletes a Renter.
     *
     * @param Renter $renter
     */
    public function deleteRenter(Renter $renter): void
    {
        $this->entityManager->remove($renter);
        $this->entityManager->flush();
    }
}