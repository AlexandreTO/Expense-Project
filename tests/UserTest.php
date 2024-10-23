<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\User;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

#[CoversClass(User::class)]
class UserTest extends WebTestCase
{
    public function testCreateUser(): void
    {
        $client = static::createClient();

        $userPage = $client->request("GET", "/user/user/new");

        // Check if the page is correctly loading
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Create an account');

    }
}