<?php

namespace App\Service;

use App\Entity\Restaurant;
use Doctrine\ORM\EntityManagerInterface;

class SlugUniqueChecker
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function isSlugUnique(string $slug)
    {
        $repository = $this->entityManager->getRepository(Restaurant::class);
        $restaurant = $repository->findOneBy(['slug' => $slug]);

        return $restaurant === null;
    }
}