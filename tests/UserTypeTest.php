<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use App\Form\UserType;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\Test\TypeTestCase;

#[CoversClass(UserType::class)]
#[CoversClass(User::class)]
class UserTypeTest extends TypeTestCase
{
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

        $submittedUser = $form->getData();

        $this->assertTrue($form->isSynchronized(), 'Form should be synchronized.');
        $this->assertSame($expectedUser->getUsername(), $submittedUser->getUsername());
        $this->assertSame($expectedUser->getRoles(), $submittedUser->getRoles());

        $view = $form->createView();
        $children = $view->children;

        $this->assertArrayHasKey('username', $children);
        $this->assertArrayHasKey('password', $children);
    }
}
