<?php

namespace App\DataFixtures;

use App\Entity\Expense;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ExpenseFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Initialize Faker for generating random data
        $faker = Factory::create();

        for ($i = 0; $i < 5; $i++) {
            $expense = new Expense();
            $expense->setCategory($faker->randomElement(['Food', 'Healthcare', 'Entertainment', 'Other']));
            $expense->setAmount($faker->randomFloat(2, 5, 200));
            $expense->setDate($faker->dateTimeBetween('-1 year', 'now'));
            $expense->setDescription($faker->sentence);

            $manager->persist($expense);
        }

        // Flush all expenses to the database
        $manager->flush();
    }
}
