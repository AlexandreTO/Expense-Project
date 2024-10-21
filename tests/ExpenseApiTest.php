<?php

namespace App\Tests;

use App\Controller\ExpenseApiController;
use App\Entity\User;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

#[CoversClass(ExpenseApiController::class)]
class ExpenseApiTest extends WebTestCase
{
    private ?EntityManagerInterface $entityManager = null;
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    }

    public function testCreateExpense(): void
    {
        // Fetch the user from the database to use for the expense
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy([]);

        // Ensure we have a user
        $this->assertNotNull($user, 'No user found in the database.');

        $this->client->request(
            'POST',
            '/api/expenses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'category' => 'Food',
                'amount' => 20.50,
                'date' => '2024-10-18T00:00:00+00:00',
                'description' => 'Lunch',
                'user_id' => $user->getId(),
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($user->getId(), $responseData['user']['id']); // Validate the user ID

        // Comparing dates as DateTime objects
        $date = \DateTime::createFromFormat(\DateTime::ISO8601, $responseData['date']);
        $this->assertInstanceOf(\DateTime::class, $date);

        $this->assertEquals('Food', $responseData['category']);
        $this->assertEquals(20.50, $responseData['amount']);
        $this->assertEquals('Lunch', $responseData['description']);
    }

    public function testListExpenses(): void
    {
        $this->client->request('GET', '/api/expenses');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);

        if (count($responseData) > 0) {
            $expense = $responseData[0];
            $this->assertArrayHasKey('category', $expense);
            $this->assertArrayHasKey('amount', $expense);
            $this->assertArrayHasKey('date', $expense);
            $this->assertArrayHasKey('description', $expense);
        }
    }

    public function testUpdateExpense(): void
    {
        // Fetch a user from fixtures
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy([]);

        // Create a new expense first
        $this->client->request('POST', '/api/expenses', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'category' => 'Transport',
            'amount' => 10.00,
            'date' => '2023-01-02',
            'description' => 'Bus fare',
            'user_id' => $user->getId()
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $expenseData = json_decode($this->client->getResponse()->getContent(), true);
        $expenseId = $expenseData['user']['id'];

        // Now update the expense
        $this->client->request('PUT', "/api/expenses/{$expenseId}", [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'category' => 'Transport',
            'amount' => 15.00,
            'date' => '2023-01-02',
            'description' => 'Taxi fare',
            'user_id' => $user->getId()
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $updatedData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(15.00, $updatedData['amount']);
        $this->assertEquals('Taxi fare', $updatedData['description']);
    }

    public function testDeleteExpense(): void
    {

        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy([]);

        // Create a new expense
        $this->client->request('POST', '/api/expenses', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'category' => 'Entertainment',
            'amount' => 50.00,
            'date' => '2023-01-03',
            'description' => 'Concert ticket',
            'user_id' => $user->getId() // Adding user_id
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $expenseData = json_decode($this->client->getResponse()->getContent(), true);

        $expenseId = $expenseData['user']['id'];

        $this->client->request('DELETE', "/api/expenses/{$expenseId}");

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
