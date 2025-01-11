<?php

namespace App\Service;

use App\Entity\Korisnik;
use App\Entity\Renter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class KorisnikService
{
    private $entityManager;
    private $passwordHasher;


    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;

    }

    public function updateKorisnik(Korisnik $korisnik): void
    {
        $korisnik->setModifiedAt(new \DateTimeImmutable());
        // Save changes
        $this->entityManager->persist($korisnik);
        $this->entityManager->flush();
    }

    public function deleteKorisnik(Korisnik $korisnik): void
    {
        $this->entityManager->remove($korisnik);
        $this->entityManager->flush();
    }

    public function findKorisnikById(int $id): ?Korisnik
    {
        return $this->entityManager->getRepository(Korisnik::class)->find($id);
    }

    /**
     * Check if the Korisnik is associated with the given Renter.
     *
     * @throws AccessDeniedException if the Korisnik does not have access.
     */
    public function checkUserRenterAssociation(Korisnik $korisnik, Renter $renter): void
    {
        if ($korisnik->getRenter() === null || $korisnik->getRenter()->getId() !== $renter->getId()) {
            throw new AccessDeniedException('You do not have permission to access this Renter.');
        }
    }
}