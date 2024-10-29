<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\UserType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Metadata\Covers;
use Symfony\Component\Form\Test\TypeTestCase;

#[CoversClass(UserType::class)]
#[CoversClass(User::class)]
class UserTypeTest extends TypeTestCase
{
    public function testValidFormData(): void
    {
        $user = new User();
        $user->setUsername("Alexandre");
        $user->setRoles(['ROLE_USER']);
    }
}