<?php

namespace App\Tests;

use App\Controller\UserApiController;
use App\Entity\User;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

#[CoversClass(UserApiController::class)]
class UserApiTest extends WebTestCase
{

}