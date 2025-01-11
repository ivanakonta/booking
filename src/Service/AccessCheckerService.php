<?php
namespace App\Service;

use App\Entity\Restaurant;
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

    public function checkUserAccess(int $restaurantId): void
    {
        // Fetch restaurant by ID
        $restaurant = $this->entityManager->getRepository(Restaurant::class)->find($restaurantId);
        if (!$restaurant) {
            throw new NotFoundHttpException('restaurant not found.');
        }

        // Fetch the current user
        $korisnik = $this->security->getUser();
        if (!$korisnik) {
            throw new AccessDeniedException('You must be logged in to access this resource.');
        }

        // Check if the user has a relation with the restaurant
        $this->korisnikService->checkUserRestaurantAssociation($korisnik, $restaurant);
    }

    public function checkUserOrAdminAccess(int $restaurantId): void
    {
        $korisnik = $this->security->getUser();

        // Check if the user has admin access
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return; // Admin has access to all
        }

        // If not an admin, check if the user has access to the specific restaurant
        $restaurant = $this->entityManager->getRepository(Restaurant::class)->find($restaurantId);

        $this->korisnikService->checkUserRestaurantAssociation($korisnik, $restaurant);

    }

    public function checkAdminOrManagerAccess(int $restaurantId): void
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

        // Fetch restaurant by ID
        $restaurant = $this->entityManager->getRepository(Restaurant::class)->find($restaurantId);
        if (!$restaurant) {
            throw new NotFoundHttpException('restaurant not found.');
        }

        // Check if the user is a manager and has a relation with the restaurant
        if ($this->authorizationChecker->isGranted('ROLE_MANAGER')) {
            $this->korisnikService->checkUserRestaurantAssociation($korisnik, $restaurant);
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
