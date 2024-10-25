<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class NotificationApiController extends AbstractController
{
    private EntityManagerInterface $em;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->em = $em;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    #[Route('/api/notifications', name: 'api_notifications_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/notifications',
        summary: 'Returns a list of all the notifications'
    )]
    #[OA\Response(
        response: 200,
        description: 'List of notifications',
        content: new Model(type: Notification::class)
    )]
    #[Security(name: 'Bearer')]
    #[OA\Tag(name: 'Notifications')]
    public function index(): JsonResponse
    {
        $notifications = $this->em->getRepository(@Notification::class)->findAll();
        $data = $this->serializer->serialize($notifications, 'json', ['groups' => ['default']]);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}