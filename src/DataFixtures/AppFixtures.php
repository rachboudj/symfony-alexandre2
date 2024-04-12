<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Articles;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $categoryList = [];

        $user = new User();
            $user->setEmail($faker->name)
                ->setPassword('password')
                ->setRoles(['ROLE_ADMIN']);

            $manager->persist($user);

        for ($i = 0; $i < 10; $i++) {
            $category = new Category();
            $category->setTitle($faker->cityPrefix)
            ->setCreatedAt($faker->datetime)
            ->setUpdatedAt($faker->datetime)
            ->setStatus('active');

            array_push($categoryList,$category);

            $manager->persist($category);
        }


        for ($i = 0; $i < 10; $i++) {
            $articles = new Articles();
            $articles->setTitle($faker->cityPrefix)
            ->setPrice($faker->randomDigitNotNull)
            ->setCreatedAt($faker->datetime)
            ->setUpdatedAt($faker->datetime)
            ->setStatus('active')
            ->addCategory($categoryList[rand(0,count($categoryList)-1)])
            ->addCategory($categoryList[rand(0,count($categoryList)-1)]);
            
            $manager->persist($articles);
        }
            

        // for ($i = 0; $i < 20; $i++) {
        //     $user = new User();
        //     $user->setName($faker->name)
        //         ->setPassword($faker->password)
        //         ->setCreatedAt($faker->datetime)
        //         ->setUpdatedAt($faker->datetime);

        //     $manager->persist($user);
        // }

      


        $manager->flush();
    }
}
