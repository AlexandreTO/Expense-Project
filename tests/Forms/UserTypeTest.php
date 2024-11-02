<?php

declare(strict_types=1);

namespace App\Tests\Forms;

use App\Entity\User;
use App\Form\UserType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Metadata\Covers;
use Symfony\Component\Form\Test\TypeTestCase;

#[CoversClass(UserType::class)]
#[CoversClass(User::class)]
class UserTypeTest extends TypeTestCase
{
    #[Covers('App\Form\UserType::buildForm')]
    #[Covers('App\Form\UserType::configureOptions')]
    public function testValidFormData(): void
    {
        $formData = [
            'username' => 'Alexandre',
            'password' => [
                'first' => 'password123',
                'second' => 'password123',
            ],
        ];

        $expectedUser = new User();
        $expectedUser->setUsername($formData['username']);
        $expectedUser->setRoles(['ROLE_USER']);

        $form = $this->factory->create(UserType::class, new User());
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized(), 'Form should be synchronized.');

        $submittedUser = $form->getData();

        $this->assertSame($expectedUser->getUsername(), $submittedUser->getUsername());
        $this->assertSame($expectedUser->getRoles(), $submittedUser->getRoles());
    }
}
