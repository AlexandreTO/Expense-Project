<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class NotificationController extends AbstractController
{
    public function listNotifications(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $notifications = $em->getRepository(Notification::class)->findBy(['user' => $user]);

        return $this->render('notifications/list.html.twig', [
            'notifications' => $notifications,
        ]);
    }
}