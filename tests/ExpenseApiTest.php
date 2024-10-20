<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ExpenseApiController::class)]
class ExpenseApiTest extends WebTestCase
{
    public function testCreateExpense(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/expenses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'category' => 'Food',
                'amount' => 20.50,
                'date' => '2024-10-18T00:00:00+00:00',
                'description' => 'Lunch'
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $responseData);

        $date = \DateTime::createFromFormat(\DateTime::ISO8601, $responseData['date']);
        $this->assertInstanceOf(\DateTime::class, $date);

        $this->assertEquals('Food', $responseData['category']);
        $this->assertEquals(20.50, $responseData['amount']);
        $this->assertEquals('2024-10-18T00:00:00+00:00', $responseData['date']); // Ensure correct date format
        $this->assertEquals('Lunch', $responseData['description']);
    }

    public function testListExpenses(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/expenses');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = json_decode($client->getResponse()->getContent(), true);
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
        $client = static::createClient();

        // Create a new expense first (or use fixtures to load one)
        $client->request('POST', '/api/expenses', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'category' => 'Transport',
            'amount' => 10.00,
            'date' => '2023-01-02',
            'description' => 'Bus fare'
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $expenseData = json_decode($client->getResponse()->getContent(), true);

        $expenseId = $expenseData['id'];

        $client->request('PUT', "/api/expenses/{$expenseId}", [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'category' => 'Transport',
            'amount' => 15.00,
            'date' => '2023-01-02',
            'description' => 'Taxi fare'
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $updatedData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(15.00, $updatedData['amount']);
        $this->assertEquals('Taxi fare', $updatedData['description']);
    }

    public function testDeleteExpense(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/expenses', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'category' => 'Entertainment',
            'amount' => 50.00,
            'date' => '2023-01-03',
            'description' => 'Concert ticket'
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $expenseData = json_decode($client->getResponse()->getContent(), true);

        $expenseId = $expenseData['id'];

        $client->request('DELETE', "/api/expenses/{$expenseId}");


        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }
}
