<?php
namespace App\Service;

use App\Entity\Renter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AccessCheckerService
{
    private EntityManagerInterface $entityManager;
    private Security $security;
    private KorisnikService $korisnikService;
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security,
        KorisnikService $korisnikService,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->korisnikService = $korisnikService;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function checkUserAccess(int $renterId): void
    {
        // Fetch Renter by ID
        $renter = $this->entityManager->getRepository(Renter::class)->find($renterId);
        if (!$renter) {
            throw new NotFoundHttpException('Renter not found.');
        }

        // Fetch the current user
        $korisnik = $this->security->getUser();
        if (!$korisnik) {
            throw new AccessDeniedException('You must be logged in to access this resource.');
        }

        // Check if the user has a relation with the Renter
        $this->korisnikService->checkUserRenterAssociation($korisnik, $renter);
    }

    public function checkUserOrAdminAccess(int $renterId): void
    {
        $korisnik = $this->security->getUser();

        // Check if the user has admin access
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return; // Admin has access to all
        }

        // If not an admin, check if the user has access to the specific Renter
        $renter = $this->entityManager->getRepository(Renter::class)->find($renterId);

        $this->korisnikService->checkUserRenterAssociation($korisnik, $renter);

    }

    public function checkAdminOrManagerAccess(int $renterId): void
    {
        // Fetch the current user
        $korisnik = $this->security->getUser();
        if (!$korisnik) {
            throw new AccessDeniedException('You must be logged in to access this resource.');
        }

        // Check if the user is an admin
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return; // Admins have access
        }

        // Fetch Renter by ID
        $renter = $this->entityManager->getRepository(Renter::class)->find($renterId);
        if (!$renter) {
            throw new NotFoundHttpException('Renter not found.');
        }

        // Check if the user is a manager and has a relation with the Renter
        if ($this->authorizationChecker->isGranted('ROLE_MANAGER')) {
            $this->korisnikService->checkUserRenterAssociation($korisnik, $renter);
            return; // Manager with a valid relation has access
        }

        // Throw an exception if the user is neither an admin nor a manager with a valid relation
        throw new AccessDeniedException('You do not have access to this resource.');
    }

    public function checkAdminAccess(): void
    {
        // Check if the user is an admin
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return; // Admins have access
        }

        // Throw an exception if the user is neither an admin nor a manager with a valid relation
        throw new AccessDeniedException('You do not have access to this resource.');
    }
}
