<?php

namespace App\Repository;

use App\Entity\Expense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ExpenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expense::class);
    }

    public function findAllWithUsers()
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.user', 'u')
            ->addSelect('u')
            ->getQuery()
            ->getResult();
    }
}
