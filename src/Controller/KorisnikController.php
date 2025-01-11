<?php

namespace App\Controller;

use App\Entity\Renter;
use App\Service\KorisnikService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\KorisnikFormType;
use App\Service\AccessCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

class KorisnikController extends AbstractController
{
    private $korisnikService;
    private $passwordHasher;
    private AccessCheckerService $accessCheckerService;

    public function __construct(KorisnikService $korisnikService, UserPasswordHasherInterface $passwordHasher, AccessCheckerService $accessCheckerService)
    {
        $this->korisnikService = $korisnikService;
        $this->passwordHasher = $passwordHasher;
        $this->accessCheckerService = $accessCheckerService;

    }

    #[Route('/{renterId}/zaposlenik/edit/{id}', name: 'edit_korisnik')]
    public function edit(Request $request, int $renterId, int $id, EntityManagerInterface $entityManager): Response
    {
        $this->accessCheckerService->checkAdminOrManagerAccess($renterId);
        $renter = $entityManager->getRepository(Renter::class)->find($renterId);

        $korisnik = $this->korisnikService->findKorisnikById($id);

        if (!$korisnik) {
            throw $this->createNotFoundException('Zaposlenik not found');
        }

        $form = $this->createForm(KorisnikFormType::class, $korisnik, ['renter' => $renter]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedRole = $form->get('roles')->getData(); // This will be a single string
            if ($selectedRole) {
                $korisnik->setRoles($selectedRole);
            }
            $newPassword = $form->get('password')->getData();
            if ($newPassword) {
                $encodedPassword = $this->passwordHasher->hashPassword($korisnik, $newPassword);
                $korisnik->setPassword($encodedPassword);
            }


            $this->korisnikService->updateKorisnik($korisnik);

            $this->addFlash('success', [
                'title' => 'Zaposlenik ažuriran',
                'message' => 'Zaposlenik uspješno ažuriran.',
            ]);

            return $this->redirectToRoute('list_korisnici', ['id' => $renterId]);
        }

        return $this->render('korisnik/edit.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => 'Edit Korisnik',
            'renter' => $renter,
            'korisnik' => $korisnik,
            'renterId' => $renterId
        ]);
    }

    #[Route('/{renterId}/zaposlenik/delete/{id}', name: 'delete_korisnik')]
    public function delete(int $renterId, int $id): Response
    {
        $this->accessCheckerService->checkAdminOrManagerAccess($renterId);

        $korisnik = $this->korisnikService->findKorisnikById($id);

        if (!$korisnik) {
            throw $this->createNotFoundException('Zaposlenik not found');
        }

        try {
            // Attempt to delete the korisnik
            $this->korisnikService->deleteKorisnik($korisnik);

            // Success message
            $this->addFlash('success', [
                'title' => 'Zaposlenik izbrisan',
                'message' => 'Zaposlenik uspješno izbrisan.',
            ]);

        } catch (ForeignKeyConstraintViolationException $e) {
            // If deletion fails due to foreign key constraints, show a warning message
            $this->addFlash('error', [
                'title' => 'Brisanje nije moguće',
                'message' => 'Ne možete izbrisati ovog zaposlenika jer ima povezane podatke u drugim tablicama.',
            ]);
        }

        return $this->redirectToRoute('list_korisnici', ['id' => $renterId]);
    }
}
