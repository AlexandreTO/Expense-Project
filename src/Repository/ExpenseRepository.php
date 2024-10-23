<?php

namespace App\Repository;

use App\Entity\Expense;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ExpenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expense::class);
    }

    public function findUserById(int $userId, string $sortField, string $sortDirection): array
    {
        return $this->createQueryBuilder("e")
            ->where("e.user = :user")
            ->setParameter('user', $userId)
            ->orderBy('e.' . $sortField, $sortDirection)
            ->getQuery()
            ->getResult();
    }
}
