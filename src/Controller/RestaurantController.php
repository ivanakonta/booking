<?php

namespace App\Controller;

use App\Entity\NonWorkingDays;
use App\Entity\Restaurant;
use App\Entity\WorkingDays;
use App\Form\NonWorkingDaysFormType;
use App\Form\RestaurantFormType;
use App\Form\WorkingDaysFormType;
use App\Repository\KorisnikRepository;
use App\Service\AccessCheckerService;
use App\Service\RestaurantService;
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

class RestaurantController extends AbstractController
{
    private RestaurantService $restaurantService;
    private SluggerInterface $slugger;
    private SlugUniqueChecker $slugUniqueChecker;
    private AccessCheckerService $accessCheckerService;


    public function __construct(
        RestaurantService $restaurantService,
        SluggerInterface $slugger,
        SlugUniqueChecker $slugUniqueChecker,
        AccessCheckerService $accessCheckerService

    ) {
        $this->restaurantService = $restaurantService;
        $this->slugger = $slugger;
        $this->slugUniqueChecker = $slugUniqueChecker;
        $this->accessCheckerService = $accessCheckerService;
    }

    #[Route('/restaurant/create', name: 'new_restaurant')]
    public function create(Request $request): Response
    {
        $this->accessCheckerService->checkAdminAccess();

        $restaurant = new Restaurant();
        $form = $this->createForm(RestaurantFormType::class, $restaurant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $logo = $form->get('logo')->getData();

            if ($logo) {
                try {
                    $this->handleFileUpload(
                        $logo,
                        $restaurant,
                        'logo',
                        null // No original logo path for new restaurant
                    );
                } catch (\RuntimeException $e) {
                    return new Response($e->getMessage());
                }
            }

            $this->restaurantService->createRestaurant($restaurant); // Pass the restaurant object to the service

            $this->addFlash('success', [
                'title' => 'Restoran kreiran',
                'message' => 'Restoran kreiran uspješno.',
            ]);
            return $this->redirectToRoute('restaurant'); // Adjust the route name to your actual index route
        }

        return $this->render('restaurant/new.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => "Kreiranje Restorana",
            'restaurant' => $restaurant,
        ]);
    }

    #[Route('/restaurant/', name: 'restaurant')]
    public function index(): Response
    {
        $this->accessCheckerService->checkAdminAccess();

        $restaurants = $this->restaurantService->getAllRestaurants();

        return $this->render('restaurant/show.html.twig', [
            'restaurants' => $restaurants,
            'pageTitle' => 'Lista restorana',
        ]);
    }

    #[Route('/restaurant/{id}/edit', name: 'edit_restaurant')]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->accessCheckerService->checkAdminOrManagerAccess($id);

        $restaurant = $this->restaurantService->getRestaurantById($id);

        $originalLogoPath = $restaurant->getLogo();

        $form = $this->createForm(RestaurantFormType::class, $restaurant);
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
                        $restaurant,
                        'logo',
                        $originalLogoPath
                    );
                } catch (\RuntimeException $e) {
                    return new Response($e->getMessage());
                }
            }

            $this->restaurantService->updateRestaurant($restaurant);

            $this->addFlash('success', [
                'title' => 'Restoran ažuriran',
                'message' => 'Restoran ažuriran uspješno.',
            ]);

            return $this->redirectToRoute('show_restaurant', ['id' => $restaurant->getId()]);
        }

        // Handle form submission for non-working days
        if ($nonWorkingDaysForm->isSubmitted() && $nonWorkingDaysForm->isValid()) {
            // Get the data from the form
            $nonWorkingDaysData = $nonWorkingDaysForm->getData();

            // Check if a non-working day with the same date already exists for the restaurant
            $existingNonWorkingDay = $entityManager->getRepository(NonWorkingDays::class)
                ->findOneBy([
                    'restaurant' => $restaurant,
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
                    $restaurant->addNonWorkingDay($nonWorkingDays);

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
            return $this->redirectToRoute('edit_restaurant', ['id' => $restaurant->getId()]);
        }

        // Handle form submission for working days
        if ($addWorkingDaysForm->isSubmitted() && $addWorkingDaysForm->isValid()) {
            // Get the data from the form
            $addWorkingDaysData = $addWorkingDaysForm->getData();

            // Check if a non-working day with the same date already exists for the restaurant
            $existingAddWorkingDay = $entityManager->getRepository(WorkingDays::class)
                ->findOneBy([
                    'restaurant' => $restaurant,
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
                    $restaurant->addWorkingDay($addWorkingDays);

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
            return $this->redirectToRoute('edit_restaurant', ['id' => $restaurant->getId()]);
        }

        // Check if today is a non-working day
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        $nonWorkingDayRepo = $entityManager->getRepository(NonWorkingDays::class);
        $existingNonWorkingDay = $nonWorkingDayRepo->findOneBy([
            'restaurant' => $restaurant,
            'date' => $today->format('Y-m-d'),
        ]);

        $isTodayNonWorkingDay = $existingNonWorkingDay !== null;

        return $this->render('restaurant/edit.html.twig', [
            'form' => $form->createView(),
            'nonWorkingDaysForm' => $nonWorkingDaysForm->createView(),
            'addWorkingDaysForm' => $addWorkingDaysForm->createView(),
            'pageTitle' => "Uredi restoran: " . $restaurant->getName(),
            'restaurant' => $restaurant,
            'isTodayNonWorkingDay' => $isTodayNonWorkingDay,

        ]);
    }

    #[Route('/restaurant/{id}', name: 'show_restaurant')]
    public function show(int $id): Response
    {
        try {
            $this->accessCheckerService->checkAdminOrManagerAccess($id);
        } catch (AccessDeniedException $e) {
            // If they don't have admin or manager access, check user access
            $this->accessCheckerService->checkUserAccess($id);
        }

        $restaurant = $this->restaurantService->getRestaurantById($id);

        return $this->render('restaurant/show_single.html.twig', [
            'restaurant' => $restaurant,
            'pageTitle' => 'Restoran: ' . $restaurant->getName(),
        ]);
    }

    #[Route('/restaurant/ajax/check-unique-slugs', name: 'check_unique_slugs', methods: ['POST'])]
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
        restaurant $restaurant,
        string $type,
        ?string $originalFilePath
    ): void {
        $workImageDir = $this->getParameter('app.workImageDir');
        $newFileName = uniqid().'.'.$file->guessExtension();

        try {
            $file->move(
                $this->getParameter('kernel.project_dir').$workImageDir.'/restaurant/',
                $newFileName
            );
        } catch (FileException $e) {
            throw new \RuntimeException($e->getMessage());
        }

        $newFilePath = '/restaurant/'.$newFileName;

        switch ($type) {
            case 'logo':
                $restaurant->setLogo($newFilePath);
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

    #[Route('/restaurant/{id}/zaposlenici', name: 'list_korisnici')]
    public function listKorisnici(int $id, Request $request, KorisnikRepository $korisnikRepository, EntityManagerInterface $entityManager): Response
    {
        $this->accessCheckerService->checkAdminOrManagerAccess($id);

        $restaurant = $entityManager->getRepository(Restaurant::class)->find($id);

        if (!$restaurant) {
            throw $this->createNotFoundException('restaurant not found');
        }

        // Get filter type from request (either 'active' or 'inactive')
        $filter = $request->query->get('filter', 'all');

        // Handle search query
        $searchQuery = $request->query->get('search');

        if ($searchQuery) {
            $korisnici = $korisnikRepository->searchByQueryAndRestaurant($searchQuery, $restaurant);
        } else {
            if ($filter === 'active') {
                $korisnici = $korisnikRepository->findActiveByRestaurant($restaurant);
            } elseif ($filter === 'inactive') {
                $korisnici = $korisnikRepository->findInactiveByRestaurant($restaurant);
            } else {
                $korisnici = $korisnikRepository->findByRestaurant($restaurant);
            }
        }

        return $this->render('korisnik/korisnici.html.twig', [
            'korisnici' => $korisnici,
            'restaurant' => $restaurant,
            'pageTitle' => 'Korisnici for Restoran: ' . $restaurant->getName(),
            'searchQuery' => $searchQuery,
            'filter' => $filter,
        ]);
    }
}