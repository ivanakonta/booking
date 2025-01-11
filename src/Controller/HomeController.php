<?php

namespace App\Controller;

use App\Entity\Korisnik;
use App\Service\AccessCheckerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class HomeController extends AbstractController
{
    private AccessCheckerService $accessCheckerService;

    public function __construct(
        AccessCheckerService $accessCheckerService,
    ) {
        $this->accessCheckerService = $accessCheckerService;
    }

    #[Route('/', name: 'app_home')]
    public function index(Security $security, AuthorizationCheckerInterface $authorizationChecker, AccessCheckerService $accessCheckerService): Response
    {
        // Check if the user is logged in
        $currentUser = $security->getUser();

        if (!$currentUser instanceof Korisnik) {
            // Handle case where current user is not an instance of Korisnik
            return $this->redirectToRoute('app_login');
        }

        try {
            // Check if the user has admin access
            $accessCheckerService->checkAdminAccess();
            // Redirect admin to the Pilane page
            return $this->redirectToRoute('renter'); // Adjust this route name as needed
        } catch (AccessDeniedException $e) {
            // If admin access is denied, check if the user is a manager or user
            if ($authorizationChecker->isGranted('ROLE_MANAGER') || $authorizationChecker->isGranted('ROLE_USER')) {
                // Check if the user has a Renter
                $renter = $currentUser->getRenter();
                if ($renter) {
                    $renterId = $renter->getId();
                    // Redirect user to their Renter page
                    return $this->redirectToRoute('show_renter', ['id' => $renterId]);
                } else {
                    // Handle case where Renter is not set or found
                    $this->addFlash('error', 'No Renter found for the user.');
                    return $this->redirectToRoute('app_login');
                }
            } else {
                // If neither admin nor manager/user, redirect to login page
                return $this->redirectToRoute('app_login');
            }
        }
    }
}
