<?php

namespace App\Controller;

use App\Entity\Korisnik;
use App\Entity\Restaurant;
use App\Form\RegistrationFormType;
use App\Service\AccessCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    private AccessCheckerService $accessCheckerService;

    public function __construct(AccessCheckerService $accessCheckerService)
    {
        $this->accessCheckerService = $accessCheckerService;
    }

    #[Route('/register/{restaurantId}', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        ?int $restaurantId = null // Optional parameter
    ): Response {
        $this->accessCheckerService->checkAdminOrManagerAccess($restaurantId);

        $restaurant = $entityManager->getRepository(Restaurant::class)->find($restaurantId);
        $korisnik = new Korisnik();
        $form = $this->createForm(RegistrationFormType::class, $korisnik, ['restaurant' => $restaurant]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedRole = $form->get('role')->getData();
            $korisnik->setRoles([$selectedRole]);

            if ($restaurant) {
                $korisnik->setRestaurant($restaurant);
            }

            $korisnik->setPassword($userPasswordHasher->hashPassword($korisnik, $form->get('password')->getData()));

            $entityManager->persist($korisnik);
            $entityManager->flush();

            return $this->redirectToRoute('list_korisnici', ['id' => $restaurant->getId()]);
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'restaurant' => $restaurant
        ]);
    }
}
