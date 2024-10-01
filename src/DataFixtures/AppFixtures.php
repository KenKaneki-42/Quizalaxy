<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 6; $i++) {

            $user = new User();
            if ( $i % 2 === 0){
                $user->setEmail('admin' . $i . '@email.com')
                ->setPlainPassword('Pas$word1')
                ->setUsername('Admin' . $i)
                ->setRoles(['ROLE_ADMIN']);
            } else {
                $user->setEmail('user' . $i . '@email.com')
                ->setPlainPassword('Pas$word1')
                ->setUsername('User' . $i)
                ->setRoles(['ROLE_USER']);
            }
            $manager->persist($user);
        }

        $manager->flush();
    }
}
