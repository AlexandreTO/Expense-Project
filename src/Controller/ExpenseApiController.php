<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Expense;
use Doctrine\ORM\EntityManagerInterface;
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

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $this->em = $em;
        $this->serializer = $serializer;
    }

    #[Route('/api/expenses', name: 'api_expense_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $expenses = $this->em->getRepository(Expense::class)->findAll();
        $data = $this->serializer->serialize($expenses, 'json');

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/expenses/{id}', name: 'api_expense_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $expense = $this->em->getRepository(Expense::class)->find($id);

        if (!$expense) {
            return $this->json(['message' => 'Expense not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->serializer->serialize($expense, 'json');
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/expenses', name: 'api_expense_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $expense = new Expense();
        $expense->setCategory($data['category']);
        $expense->setAmount($data['amount']);
        $expense->setDate(new \DateTime($data['date']));
        $expense->setDescription($data['description']);

        $this->em->persist($expense);
        $this->em->flush();

        // Serialize the created expense
        $jsonData = $this->serializer->serialize($expense, 'json');

        return new JsonResponse($jsonData, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/expenses/{id}', name: 'api_expense_update', methods: ['PUT'])]
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

        $this->em->flush();

        $jsonData = $this->serializer->serialize($expense, 'json');

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/api/expenses/{id}', name: 'api_expense_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $expense = $this->em->getRepository(Expense::class)->find($id);

        if (!$expense) {
            return $this->json(['message' => 'Expense not found'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($expense);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
