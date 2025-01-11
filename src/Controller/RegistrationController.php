<?php

namespace App\Controller;

use App\Entity\Korisnik;
use App\Entity\Renter;
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

    #[Route('/register/{renterId}', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        ?int $renterId = null // Optional parameter
    ): Response {
        $this->accessCheckerService->checkAdminOrManagerAccess($renterId);

        $renter = $entityManager->getRepository(Renter::class)->find($renterId);
        $korisnik = new Korisnik();
        $form = $this->createForm(RegistrationFormType::class, $korisnik, ['renter' => $renter]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedRole = $form->get('role')->getData();
            $korisnik->setRoles([$selectedRole]);

            if ($renter) {
                $korisnik->setRenter($renter);
            }

            $korisnik->setPassword($userPasswordHasher->hashPassword($korisnik, $form->get('password')->getData()));

            $entityManager->persist($korisnik);
            $entityManager->flush();

            return $this->redirectToRoute('list_korisnici', ['id' => $renter->getId()]);
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'renter' => $renter
        ]);
    }
}
