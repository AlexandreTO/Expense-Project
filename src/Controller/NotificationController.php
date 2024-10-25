<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class NotificationController extends AbstractController
{
    public function listUnreadNotifications(NotificationRepository $notificationRepository): Response
    {
        $user = $this->getUser();
        $notifications = $notificationRepository->findBy(['user' => $user, 'isRead' => false]);

        return $this->render('notifications/list.html.twig', [
            'notifications' => $notifications,
        ]);
    }
}