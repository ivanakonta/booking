<?php

namespace App\Controller;

use App\Entity\NonWorkingDays;
use App\Entity\Renter;
use App\Entity\WorkingDays;
use App\Form\AddWorkingDaysFormType;
use App\Form\NonWorkingDaysFormType;
use App\Form\RenterFormType;
use App\Form\WorkingDaysFormType;
use App\Repository\KorisnikRepository;
use App\Service\AccessCheckerService;
use App\Service\KorisnikService;
use App\Service\RenterService;
use App\Service\SlugUniqueChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\String\Slugger\SluggerInterface;

class RenterController extends AbstractController
{
    private RenterService $renterService;
    private SluggerInterface $slugger;
    private SlugUniqueChecker $slugUniqueChecker;
    private AccessCheckerService $accessCheckerService;


    public function __construct(
        RenterService $renterService,
        SluggerInterface $slugger,
        SlugUniqueChecker $slugUniqueChecker,
        AccessCheckerService $accessCheckerService

    ) {
        $this->renterService = $renterService;
        $this->slugger = $slugger;
        $this->slugUniqueChecker = $slugUniqueChecker;
        $this->accessCheckerService = $accessCheckerService;
    }

    #[Route('/renter/create', name: 'new_renter')]
    public function create(Request $request): Response
    {
        $this->accessCheckerService->checkAdminAccess();

        $renter = new Renter();
        $form = $this->createForm(RenterFormType::class, $renter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $logo = $form->get('logo')->getData();

            if ($logo) {
                try {
                    $this->handleFileUpload(
                        $logo,
                        $renter,
                        'logo',
                        null // No original logo path for new Renter
                    );
                } catch (\RuntimeException $e) {
                    return new Response($e->getMessage());
                }
            }

            $this->renterService->createRenter($renter); // Pass the Renter object to the service

            $this->addFlash('success', [
                'title' => 'Iznajmljivač kreiran',
                'message' => 'Iznamljivač kreiran uspješno.',
            ]);
            return $this->redirectToRoute('renter'); // Adjust the route name to your actual index route
        }

        return $this->render('renter/new.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => "Kreiranje Iznajmljivača",
            'renter' => $renter,
        ]);
    }

    #[Route('/renter/', name: 'renter')]
    public function index(): Response
    {
        $this->accessCheckerService->checkAdminAccess();

        $renters = $this->renterService->getAllRenters();

        return $this->render('renter/show.html.twig', [
            'renters' => $renters,
            'pageTitle' => 'Lista Iznamljivača',
        ]);
    }

    #[Route('/renter/{id}/edit', name: 'edit_renter')]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->accessCheckerService->checkAdminOrManagerAccess($id);

        $renter = $this->renterService->getRenterById($id);

        $originalLogoPath = $renter->getLogo();

        $form = $this->createForm(RenterFormType::class, $renter);
        $form->handleRequest($request);


        // Create the form for non-working days
        $nonWorkingDaysForm = $this->createForm(NonWorkingDaysFormType::class);
        $nonWorkingDaysForm->handleRequest($request);

        // Create the form for working days
        $addWorkingDaysForm = $this->createForm(WorkingDaysFormType::class);
        $addWorkingDaysForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $logo = $form->get('logo')->getData();

            if ($logo) {
                try {
                    $this->handleFileUpload(
                        $logo,
                        $renter,
                        'logo',
                        $originalLogoPath
                    );
                } catch (\RuntimeException $e) {
                    return new Response($e->getMessage());
                }
            }

            $this->renterService->updateRenter($renter);

            $this->addFlash('success', [
                'title' => 'Iznajmljivač ažurirana',
                'message' => 'Iznajmljivač ažuriran uspješno.',
            ]);

            return $this->redirectToRoute('show_renter', ['id' => $renter->getId()]);
        }

        // Handle form submission for non-working days
        if ($nonWorkingDaysForm->isSubmitted() && $nonWorkingDaysForm->isValid()) {
            // Get the data from the form
            $nonWorkingDaysData = $nonWorkingDaysForm->getData();

            // Check if a non-working day with the same date already exists for the restaurant
            $existingNonWorkingDay = $entityManager->getRepository(NonWorkingDays::class)
                ->findOneBy([
                    'renter' => $renter,
                    'date' => $nonWorkingDaysData->getDate(),
                ]);

            if ($existingNonWorkingDay) {
                // Add a flash message for error
                $this->addFlash('error', [
                    'title' => 'Neradni dan greška',
                    'message' => 'Neradni dan sa istim datumom već postoji u bazi.',
                ]);
            } else {
                // Check if the date is in the past
                $today = new \DateTime();
                $today->setTime(0, 0, 0); // Normalize the time part to 00:00:00 for comparison

                // Convert the selected date string to a DateTime object
                $selectedDate = \DateTime::createFromFormat('Y-m-d', $nonWorkingDaysData->getDate());
                $selectedDate->setTime(0, 0, 0); // Normalize the time part to 00:00:00 for comparison

                if ($selectedDate < $today) {
                    // Date is in the past
                    $this->addFlash('error', [
                        'title' => 'Neradni dan greška',
                        'message' => 'Ne može se dodati neradni dan za datume u prošlosti.',
                    ]);
                } else {
                    // Create a new NonWorkingDays entity and set its properties
                    $nonWorkingDays = new NonWorkingDays();

                    // Format the date to Y-m-d
                    $formattedDate = $selectedDate->format('Y-m-d');

                    $nonWorkingDays->setDate($formattedDate);
                    $nonWorkingDays->setDescription($nonWorkingDaysData->getDescription());

                    // Add the non-working day to the restaurant
                    $renter->addNonWorkingDay($nonWorkingDays);

                    // Persist the new NonWorkingDays entity
                    $entityManager->persist($nonWorkingDays);
                    $entityManager->flush();

                    // Add a flash message for success
                    $this->addFlash('success', [
                        'title' => 'Neradni dan dodan',
                        'message' => 'Neradni dan uspješno dodan.',
                    ]);
                }
            }

            // Redirect to the edit restaurant page
            return $this->redirectToRoute('edit_renter', ['id' => $renter->getId()]);
        }

        // Handle form submission for working days
        if ($addWorkingDaysForm->isSubmitted() && $addWorkingDaysForm->isValid()) {
            // Get the data from the form
            $addWorkingDaysData = $addWorkingDaysForm->getData();

            // Check if a non-working day with the same date already exists for the restaurant
            $existingAddWorkingDay = $entityManager->getRepository(WorkingDays::class)
                ->findOneBy([
                    'renter' => $renter,
                    'date' => $addWorkingDaysData->getDate(),
                ]);

            if ($existingAddWorkingDay) {
                // Add a flash message for error
                $this->addFlash('error', [
                    'title' => 'Radni dan greška',
                    'message' => 'Radni dan sa istim datumom već postoji u bazi.',
                ]);
            } else {
                // Check if the date is in the past
                $today = new \DateTime();
                $selectedDate = new \DateTime($addWorkingDaysData->getDate());

                if ($selectedDate < $today) {
                    // Date is in the past
                    $this->addFlash('error', [
                        'title' => 'Radni dan greška',
                        'message' => 'Ne može se dodati radni dan za datume u prošlosti.',
                    ]);
                } else {
                    // Create a new AddWorkingDays entity and set its properties
                    $addWorkingDays = new WorkingDays();

                    // Format the date to Y-m-d
                    $formattedDate = $selectedDate->format('Y-m-d');

                    $addWorkingDays->setDate($formattedDate);
                    $addWorkingDays->setDescription($addWorkingDaysData->getDescription());

                    // Add the working day to the restaurant
                    $renter->addWorkingDay($addWorkingDays);

                    // Persist the new AddWorkingDays entity
                    $entityManager->persist($addWorkingDays);
                    $entityManager->flush();

                    // Add a flash message for success
                    $this->addFlash('success', [
                        'title' => 'Radni dan dodan',
                        'message' => 'Radni dan uspješno dodan.',
                    ]);
                }
            }
            // Redirect to the edit restaurant page
            return $this->redirectToRoute('edit_renter', ['id' => $renter->getId()]);
        }

        // Check if today is a non-working day
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        $nonWorkingDayRepo = $entityManager->getRepository(NonWorkingDays::class);
        $existingNonWorkingDay = $nonWorkingDayRepo->findOneBy([
            'renter' => $renter,
            'date' => $today->format('Y-m-d'),
        ]);

        $isTodayNonWorkingDay = $existingNonWorkingDay !== null;

        return $this->render('renter/edit.html.twig', [
            'form' => $form->createView(),
            'nonWorkingDaysForm' => $nonWorkingDaysForm->createView(),
            'addWorkingDaysForm' => $addWorkingDaysForm->createView(),
            'pageTitle' => "Uredi Iznajmljivača: " . $renter->getName(),
            'renter' => $renter,
            'isTodayNonWorkingDay' => $isTodayNonWorkingDay,

        ]);
    }

    #[Route('/renter/{id}', name: 'show_renter')]
    public function show(int $id): Response
    {
        try {
            $this->accessCheckerService->checkAdminOrManagerAccess($id);
        } catch (AccessDeniedException $e) {
            // If they don't have admin or manager access, check user access
            $this->accessCheckerService->checkUserAccess($id);
        }

        $renter = $this->renterService->getRenterById($id);

        return $this->render('renter/show_single.html.twig', [
            'renter' => $renter,
            'pageTitle' => 'Iznajmljivač: ' . $renter->getName(),
        ]);
    }

    #[Route('/renter/ajax/check-unique-slugs', name: 'check_unique_slugs', methods: ['POST'])]
    public function checkUniqueSlugs(Request $request): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse('Invalid request', Response::HTTP_BAD_REQUEST);
        }

        $name = $request->request->get('name');
        $slug = $this->slugger->slug($name)->lower()->toString();
        $isSlugUnique = $this->slugUniqueChecker->isSlugUnique($slug);

        return new JsonResponse([
            'slug' => $slug,
            'isSlugUnique' => $isSlugUnique,
        ]);
    }

    private function handleFileUpload(
        UploadedFile $file,
        Renter $renter,
        string $type,
        ?string $originalFilePath
    ): void {
        $workImageDir = $this->getParameter('app.workImageDir');
        $newFileName = uniqid().'.'.$file->guessExtension();

        try {
            $file->move(
                $this->getParameter('kernel.project_dir').$workImageDir.'/renter/',
                $newFileName
            );
        } catch (FileException $e) {
            throw new \RuntimeException($e->getMessage());
        }

        $newFilePath = '/renter/'.$newFileName;

        switch ($type) {
            case 'logo':
                $renter->setLogo($newFilePath);
                break;
            default:
                throw new \InvalidArgumentException('Invalid file type provided.');
        }

        if ($originalFilePath && $originalFilePath !== $newFilePath) {
            $oldFilePath = $this->getParameter('kernel.project_dir').$workImageDir.$originalFilePath;
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
        }
    }

    #[Route('/renter/{id}/zaposlenici', name: 'list_korisnici')]
    public function listKorisnici(int $id, Request $request, KorisnikRepository $korisnikRepository, EntityManagerInterface $entityManager): Response
    {
        $this->accessCheckerService->checkAdminOrManagerAccess($id);

        $renter = $entityManager->getRepository(Renter::class)->find($id);

        if (!$renter) {
            throw $this->createNotFoundException('Renter not found');
        }

        // Get filter type from request (either 'active' or 'inactive')
        $filter = $request->query->get('filter', 'all');

        // Handle search query
        $searchQuery = $request->query->get('search');

        if ($searchQuery) {
            $korisnici = $korisnikRepository->searchByQueryAndRenter($searchQuery, $renter);
        } else {
            if ($filter === 'active') {
                $korisnici = $korisnikRepository->findActiveByRenter($renter);
            } elseif ($filter === 'inactive') {
                $korisnici = $korisnikRepository->findInactiveByRenter($renter);
            } else {
                $korisnici = $korisnikRepository->findByRenter($renter);
            }
        }

        return $this->render('korisnik/korisnici.html.twig', [
            'korisnici' => $korisnici,
            'renter' => $renter,
            'pageTitle' => 'Korisnici for Iznajmljivač: ' . $renter->getName(),
            'searchQuery' => $searchQuery,
            'filter' => $filter,
        ]);
    }
}