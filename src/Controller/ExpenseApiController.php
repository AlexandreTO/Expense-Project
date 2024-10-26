<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Expense;
use App\Entity\User;
use App\DTO\ExpenseDTO;
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

class ExpenseApiController extends AbstractController
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

    #[Route('/api/expenses', name: 'api_expense_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/expenses',
        summary: 'Returns a list of all the expenses'
    )]
    #[OA\Response(
        response: 200,
        description: 'List of expenses',
        content: new Model(type: Expense::class)
    )]
    #[Security(name: 'Bearer')]
    #[OA\Tag(name: 'Expenses')]
    public function index(): JsonResponse
    {
        $expenses = $this->em->getRepository(Expense::class)->findAllWithUsers();
        $data = $this->serializer->serialize($expenses, 'json', ['groups' => ['default']]);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/expenses/{id}', name: 'api_expense_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/expenses/{id}',
        summary: 'Returns a specific expense by ID'
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID of the expense'
    )]
    #[OA\Response(
        response: 200,
        description: 'Expense found',
        content: new Model(type: Expense::class)
    )]
    #[OA\Response(
        response: 404,
        description: 'Expense not found',
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
    #[OA\Tag(name: 'Expenses')]
    public function getOneExpenseById(int $id): JsonResponse
    {
        $expense = $this->em->getRepository(Expense::class)->find($id);

        if (!$expense) {
            return $this->json(['message' => 'Expense not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->serializer->serialize($expense, 'json', ['groups' => ['default']]);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/expenses', name: 'api_expense_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/expenses',
        summary: 'Creates a new expense'
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'category', type: 'string', description: 'The category of the expense'),
                new OA\Property(property: 'amount', type: 'number', format: 'float', description: 'The amount of the expense'),
                new OA\Property(property: 'date', type: 'string', format: 'date-time', description: 'The date of the expense'),
                new OA\Property(property: 'description', type: 'string', description: 'A description of the expense'),
                new OA\Property(property: 'user_id', type: 'integer', description: 'The ID of the user associated with this expense'),
            ],
            required: ['category', 'amount', 'date', 'user_id']
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Expense created',
        content: new OA\JsonContent(
            ref: new Model(type: Expense::class)
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
    #[OA\Tag(name: 'Expenses')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = $this->em->getRepository(User::class)->find($data['user_id']);
        if (!$user) {
            return $this->json(['errors' => 'User not found'], Response::HTTP_BAD_REQUEST);
        }

        $expense = new Expense();
        $expense->setCategory($data['category']);
        $expense->setAmount($data['amount']);
        $expense->setDate(new \DateTime($data['date']));
        $expense->setDescription($data['description']);
        $expense->setUser($user);

        // Validate the expense entity
        $errors = $this->validator->validate($expense);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($expense);
        $this->em->flush();

        // Serialize response
        $jsonData = $this->serializer->serialize($expense, 'json', ['groups' => ['create']]);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, [], true);
    }


    #[Route('/api/expenses/{id}', name: 'api_expense_update', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/expenses/{id}',
        summary: 'Update an expense by its ID'
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID of the expense'
    )]
    #[OA\Response(
        response: 200,
        description: 'Expense updated',
        content: new Model(type: Expense::class)
    )]
    #[OA\Response(
        response: 404,
        description: 'Expense not found',
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
    #[OA\Tag(name: 'Expenses')]
    public function update(Request $request, int $id): JsonResponse
    {
        $expense = $this->em->getRepository(Expense::class)->find($id);

        if (!$expense) {
            return $this->json(['message' => 'Expense not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        // Update only the fields that are provided in the request
        if (array_key_exists('category', $data)) {
            $expense->setCategory($data['category']);
        }
        if (array_key_exists('amount', $data)) {
            $expense->setAmount($data['amount']);
        }
        if (array_key_exists('date', $data)) {
            // Validate date format if needed
            $expense->setDate(new \DateTime($data['date']));
        }
        if (array_key_exists('description', $data)) {
            $expense->setDescription($data['description']);
        }
        if (array_key_exists('userId', $data)) {
            return $this->json(['errors' => 'Changing the user is not allowed.'], Response::HTTP_BAD_REQUEST);
        }

        $errors = $this->validator->validate($expense);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        $jsonData = $this->serializer->serialize($expense, 'json', ['groups' => ['update']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/api/expenses/{id}', name: 'api_expense_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/expenses/{id}',
        summary: 'Deletes an expense'
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID of the expense'
    )]
    #[OA\Response(
        response: 204,
        description: 'Expense deleted',
        content: new Model(type: Expense::class)
    )]
    #[OA\Response(
        response: 404,
        description: 'Expense not found',
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
    #[OA\Tag(name: 'Expenses')]
    public function delete(int $id): JsonResponse
    {
        $expense = $this->em->getRepository(Expense::class)->find($id);

        if (!$expense) {
            return $this->json(['message' => 'Expense not found'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($expense);
        $this->em->flush();

        return $this->json(['message' => 'Expense deleted successfully'], Response::HTTP_NO_CONTENT);
    }
}
