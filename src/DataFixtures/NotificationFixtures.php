<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Notification;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class NotificationFixtures extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 5; $i++) {
            $userRef = 'user_' . $faker->numberBetween(0, 4);
            $user = $this->getReference($userRef);

            $notification = new Notification($user, $faker->sentence());

            $notification->setUser($user);
            $manager->persist($notification);

            $this->addReference("notification_$i", $notification);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
