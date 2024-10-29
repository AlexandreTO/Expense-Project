<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Expense;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ExpenseFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 5; $i++) {
            $expense = new Expense();
            $expense->setCategory($faker->randomElement(['Food', 'Healthcare', 'Entertainment', 'Other']));
            $expense->setAmount($faker->randomFloat(2, 5, 200));
            $expense->setDate($faker->dateTimeBetween('-1 year', 'now'));
            $expense->setDescription($faker->sentence);

            // Assign a random user to each expense
            $userRef = rand(0, 4);
            $user = $this->getReference("user_$userRef");
            $expense->setUser($user);

            $manager->persist($expense);

            $this->addReference("expense_$i", $expense);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
