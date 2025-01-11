<?php

namespace App\Service;

use App\Entity\Renter;
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
        $repository = $this->entityManager->getRepository(Renter::class);
        $renter = $repository->findOneBy(['slug' => $slug]);

        return $renter === null;
    }
}