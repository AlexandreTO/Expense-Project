<?php

declare(strict_types=1);

namespace App\Tests;

use App\Controller\UserApiController;
use App\Entity\User;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[CoversClass(UserApiController::class)]
class UserApiTest extends WebTestCase
{
    private ?EntityManagerInterface $entityManager = null;
    private ?UserPasswordHasherInterface $hasher = null;
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    }

    public function testCreateUser(): void
    {
        $hasherMock = $this->createMock(UserPasswordHasherInterface::class);
        $password = getenv('TEST_PASSWORD');

        $hashedPassword = $hasherMock->hashPassword(new User(), $password);
        $hasherMock->method('hashPassword')
            ->willReturn($password);

        $this->client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => 'testuser',
                'password' => $password,
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Retrieve the newly created user to assert
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'testuser']);
        $this->assertNotNull($user);
        $this->assertEquals($hashedPassword, $user->getPassword());
    }
}