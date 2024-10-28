<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\User;
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
        $notifications = $this->em->getRepository(Notification::class)->findAll();
        $data = $this->serializer->serialize($notifications, 'json', ['groups' => ['default']]);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/notifications/{id}', name: 'api_notification_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/notifications/{id}',
        summary: 'Returns a specific notification by ID'
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID of the notification'
    )]
    #[OA\Response(
        response: 200,
        description: 'Notification found',
        content: new Model(type: Notification::class)
    )]
    #[OA\Response(
        response: 404,
        description: 'Notification not found',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                properties: [
                    'errors' => new OA\Property(type: 'string'),
                ]
            )
        )
    )]
    #[Security(name: 'Bearer')]
    #[OA\Tag(name: 'Notifications')]
    public function getOneNotificationById(int $id): JsonResponse
    {
        $notification = $this->em->getRepository(Notification::class)->find($id);

        if (!$notification) {
            return $this->json(['message' => 'Notification not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->serializer->serialize($notification, 'json', ['groups' => ['default']]);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/notifications', name: 'api_notification_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/notifications',
        summary: 'Creates a new notification'
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'title', type: 'string', description: 'Title of the notification'),
                new OA\Property(property: 'message', type: 'string', description: 'Notification message content'),
                new OA\Property(property: 'userId', type: 'integer', description: 'ID of the user to receive the notification'),
            ],
            required: ['title', 'message', 'userId']
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Expense created',
        content: new OA\JsonContent(
            ref: new Model(type: Notification::class)
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Validation errors',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'errors', type: 'string', description: 'Validation error messages'),
            ]
        )
    )]
    #[Security(name: 'Bearer')]
    #[OA\Tag(name: 'Notifications')]
    public function create(Request $request): JsonResponse
    {
        $data = $this->serializer->deserialize($request->getContent(), Notification::class, 'json');

        $errors = $this->validator->validate($data);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->em->getRepository(User::class)->find($data['user_id']);
        if (!$user) {
            return $this->json(['errors' => 'User not found'], Response::HTTP_BAD_REQUEST);
        }

        $data->setUser($user);

        $this->em->persist($data);
        $this->em->flush();

        return $this->json($data, Response::HTTP_CREATED);
    }

    #[Route('/api/notification/{id}', name: 'api_notification_update', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/notification/{id}',
        summary: 'Update an notification by its ID'
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID of the expense'
    )]
    #[OA\Response(
        response: 200,
        description: 'Notification updated',
        content: new Model(type: Notification::class)
    )]
    #[OA\Response(
        response: 404,
        description: 'Notification not found',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                properties: [
                    'errors' => new OA\Property(type: 'string'),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Validation errors',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                properties: [
                    'errors' => new OA\Property(type: 'string'),
                ]
            )
        )
    )]
    #[Security(name: 'Bearer')]
    #[OA\Tag(name: 'Notifications')]
    public function update(Request $request, int $id): JsonResponse
    {
        $notification = $this->em->getRepository(Notification::class)->find($id);

        if (!$notification) {
            return $this->json(['message' => 'Notification not found'], Response::HTTP_NOT_FOUND);
        }

        $this->serializer->deserialize($request->getContent(), Notification::class, 'json');

        $errors = $this->validator->validate($notification);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $userId = json_decode($request->getContent(), true)['userId'] ?? null;
        if ($userId) {
            $user = $this->em->getRepository(User::class)->find($userId);
            if (!$user) {
                return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
            }
            $notification->setUser($user);
        }

        $this->em->persist($notification);
        $this->em->flush();

        return $this->json($notification, Response::HTTP_OK);
    }

    #[Route('/api/notification/{id}', name: 'api_notification_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/notifications/{id}',
        summary: 'Deletes an notification'
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID of the notification'
    )]
    #[OA\Response(
        response: 204,
        description: 'Notification deleted',
        content: new Model(type: Notification::class)
    )]
    #[OA\Response(
        response: 404,
        description: 'Notification not found',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                properties: [
                    'errors' => new OA\Property(type: 'string'),
                ]
            )
        )
    )]
    #[Security(name: 'Bearer')]
    #[OA\Tag(name: 'Notifications')]
    public function delete(int $id): JsonResponse
    {
        $notification = $this->em->getRepository(Notification::class)->find($id);
        if (!$notification) {
            return $this->json(['error' => 'Notification not found'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($notification);
        $this->em->flush();

        return $this->json(['message' => 'Notification deleted successfully'], Response::HTTP_NO_CONTENT);
    }
}