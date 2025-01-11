<?php

namespace App\Controller;

use App\Entity\Korisnik;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        $user = $this->getUser();
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $restaurant = null;

        if (!$isAdmin && $user instanceof Korisnik) {
            // For non-admin users, check if the user has a restaurant
            $restaurant = $user->getRestaurant();
        }

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error, 'isAdmin' => $isAdmin, 'restaurant' => $restaurant]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
