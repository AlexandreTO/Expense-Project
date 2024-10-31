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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->em = $em;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->userPasswordHasher = $userPasswordHasher;
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

    #[Route('/api/user/{id}', name: 'user', methods: ['GET'])]
    #[OA\Get(
        path: '/api/user/{id}',
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

    #[Route('/api/users', name: 'api_user_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/users',
        summary: 'Creates a new user'
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'username', type: 'string', description: 'Username of the user'),
                new OA\Property(property: 'password', type: 'string', description: 'Password of the user'),
            ],
            required: ['username', 'password']
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'User created',
        content: new OA\JsonContent(
            ref: new Model(type: User::class)
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
    #[OA\Tag(name: 'Users')]
    public function create(Request $request): JsonResponse
    {
        $data = $this->serializer->deserialize($request->getContent(), User::class, 'json');

        $errors = $this->validator->validate($data);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        // Hashing the password before inserting in the database
        $hashedPassword = $this->userPasswordHasher->hashPassword($data, $data->getPassword());
        $data->setPassword($hashedPassword);

        $this->em->persist($data);
        $this->em->flush();

        return $this->json($data, Response::HTTP_CREATED);
    }


    #[Route('/api/user/{id}', name: 'api_user_update', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/user/{id}',
        summary: 'Update an user by its ID'
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID of the user'
    )]
    #[OA\Response(
        response: 200,
        description: 'User updated',
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
    #[OA\Tag(name: 'Users')]
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $this->em->getRepository(User::class)->find($id);

        if (!$data) {
            throw new NotFoundHttpException("User not found");
        }

        // Deserialize only fields provided in the request to update the existing user
        $this->serializer->deserialize(
            $request->getContent(),
            User::class,
            'json',
            ['object_to_populate' => $data] // This updates the existing user object
        );

        // Validate the updated User data
        $errors = $this->validator->validate($data);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // Hash the password if it's changed
        if ($data->getPassword()) {
            $hashedPassword = $this->userPasswordHasher->hashPassword($data, $data->getPassword());
            $data->setPassword($hashedPassword);
        }

        $this->em->persist(object: $data);
        $this->em->flush();

        return $this->json($data, Response::HTTP_OK);
    }

    #[Route('/api/user/{id}', name: 'api_user_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/user/{id}',
        summary: 'Deletes an user'
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID of the user'
    )]
    #[OA\Response(
        response: 204,
        description: 'User deleted',
        content: new Model(type: User::class)
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
    #[OA\Tag(name: 'Users')]
    public function delete(int $id): JsonResponse
    {
        $data = $this->em->getRepository(User::class)->find($id);
        if (!$data) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($data);
        $this->em->flush();

        return $this->json(['message' => 'User deleted successfully'], Response::HTTP_NO_CONTENT);
    }

}