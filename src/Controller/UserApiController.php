<?php

declare(strict_types=1);

namespace App\Controller;

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

class UserApiController extends AbstractController
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

    #[Route('/api/users', name: 'api_users_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users',
        summary: 'Returns a list of all the users'
    )]
    #[OA\Response(
        response: 200,
        description: 'List of users',
        content: new Model(type: User::class)
    )]
    #[Security(name: 'Bearer')]
    #[OA\Tag(name: 'Users')]
    public function index(): JsonResponse
    {
        $users = $this->em->getRepository(User::class)->findAll();
        $data = $this->serializer->serialize($users, 'json', ['groups' => ['default']]);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/{id}', name: 'user', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users/{id}',
        summary: 'Returns a specific user by ID'
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID of the user'
    )]
    #[OA\Response(
        response: 200,
        description: 'User found',
        content: new Model(type: User::class)
    )]
    #[OA\Response(
        response: 404,
        description: 'User not found',
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
    #[OA\Tag(name: 'Users')]
    public function getOneUserById(int $id): JsonResponse
    {
        $user = $this->em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->serializer->serialize($user, 'json', ['groups' => ['default']]);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}