<?php

namespace App\Service;

use App\Entity\Korisnik;
use App\Entity\Restaurant;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Common\Collections\Collection;

class RestaurantService
{
    private RestaurantRepository $restaurantRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(RestaurantRepository $restaurantRepository, EntityManagerInterface $entityManager)
    {
        $this->restaurantRepository = $restaurantRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Retrieves all Restaurant entities.
     *
     * @return Collection
     */
    public function getAllRestaurants(): array
    {
        return $this->restaurantRepository->findAll();
    }

    /**
     * Retrieves all Active Restaurants entities.
     *
     * @return Collection
     */
    public function getAllActiveRestaurants(): array
    {
        return $this->restaurantRepository->findBy(['isActive' => true]);
    }

    /**
     * Retrieves a restaurant entity by its ID.
     *
     * @param int $id
     * @return Restaurant
     * @throws EntityNotFoundException
     */
    public function getRestaurantById(int $id): Restaurant
    {
        $restaurant = $this->restaurantRepository->find($id);

        if (!$restaurant) {
            throw new EntityNotFoundException('restaurant not found');
        }

        return $restaurant;
    }

    /**
     * Adds a Korisnik to a restaurant.
     *
     * @param Restaurant $restaurant
     * @param Korisnik $korisnik
     */
    public function addUserToRestaurant(Restaurant $restaurant, Korisnik $korisnik): void
    {
        if (!$restaurant->getKorisniks()->contains($korisnik)) {
            $restaurant->addKorisnik($korisnik);
            $this->entityManager->persist($restaurant);
            $this->entityManager->flush();
        }
    }

    /**
     * Removes a Korisnik from a restaurant.
     *
     * @param Restaurant $restaurant
     * @param Korisnik $korisnik
     */
    public function removeUserFromRestaurant(Restaurant $restaurant, Korisnik $korisnik): void
    {
        if ($restaurant->getKorisniks()->contains($korisnik)) {
            $restaurant->removeKorisnik($korisnik);
            $this->entityManager->persist($restaurant);
            $this->entityManager->flush();
        }
    }

    /**
     * Creates a new restaurant.
     *
     * @param string $name
     * @return Restaurant
     */
    public function createRestaurant(Restaurant $restaurant): void
    {

        $this->entityManager->persist($restaurant);
        $this->entityManager->flush();

    }

    /**
     * Updates an existing restaurant.
     *
     * @param Restaurant $restaurant
     * @param string $name
     */
    public function updateRestaurant(Restaurant $restaurant): void
    {
        $restaurant->setModifiedAt(new \DateTimeImmutable());

        $this->entityManager->persist($restaurant);
        $this->entityManager->flush();
    }

    /**
     * Deletes a restaurant.
     *
     * @param Restaurant $restaurant
     */
    public function deleteRestaurant(Restaurant $restaurant): void
    {
        $this->entityManager->remove($restaurant);
        $this->entityManager->flush();
    }
}