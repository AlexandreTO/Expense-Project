<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Notification;
use App\Event\ExpenseAddEvent;
use Doctrine\ORM\EntityManagerInterface;

class NotificationListener
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
    }

    public function onExpenseAdded(ExpenseAddEvent $event)
    {
        $expense = $event->getExpense();
        $user = $expense->getUser();

        // Create a notification for the user
        $message = 'A new expense has been added: ' . $expense->getDescription();
        $notification = new Notification($user, $message);

        $this->em->persist($notification);
        $this->em->flush();
    }
}