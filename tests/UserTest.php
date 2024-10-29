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

        $userPage = $client->request("GET", "/user/new");

        // Check if the page is correctly loading
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Create an account');

        $form = $userPage->selectButton('Register')->form();
        $form['user[username]'] = 'testuser';
        $form['user[password][first]'] = 'password123';
        $form['user[password][second]'] = 'password123';

        $client->submit($form);
        $client->followRedirect();

        $this->assertResponseIsSuccessful();

        // Fetch the user from the database after the creation from the test
        $user = self::getContainer()->get(EntityManagerInterface::class)->getRepository(User::class)->findOneBy(['username' => 'testuser']);

        $this->assertNotNull($user);
        $this->assertEquals('testuser', $user->getUsername());

        $this->assertNotEquals('password123', $user->getPassword());
    }
}
