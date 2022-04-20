<?php

namespace App\DataFixtures;

use App\Entity\Contact;
use App\Entity\Ingredient;
use App\Entity\Mark;
use App\Entity\Recipe;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 1; $i < 10; $i++){
            $user = new User();
            $user->setFullname($faker->name())
                ->setPseudo(mt_rand(0, 1) == 1 ? $faker->firstName() : null)
                ->setEmail($faker->email())
                ->setRoles(['ROLE_USER'])
                ->setPlainPassword( 'password');

            $users[] = $user;
            $manager->persist($user);
        }


        for ($i = 1; $i <= 50; $i++){
            $ingredient = new Ingredient();
            $ingredient->setName($faker->word())
                ->setPrice(mt_rand(0, 100))
                ->setUser($users[mt_rand(0, count($users) - 1 )]);

            $ingredients[] = $ingredient;
            $manager->persist($ingredient);
        }

        for ($j = 1; $j < 25 ; $j++){
            $recipe = new Recipe();
            $recipe->setName($faker->word())
                ->setTime(mt_rand(0, 1) == 1 ? mt_rand(1,1440) : null)
                ->setNbPeople(mt_rand(0, 1) == 1 ? mt_rand(1,50) : null)
                ->setDifficulty(mt_rand(0, 1) == 1 ? mt_rand(1,5) : null)
                ->setDescription($faker->text(300))
                ->setPrice(mt_rand(0, 1) == 1 ? mt_rand(1,1000) : null)
                ->setIsFavorite(mt_rand(0, 1))
                ->setIsPublic(mt_rand(0, 1))

            ->setUser($users[mt_rand(0, count($users) - 1 )]);

            for ($k = 1; $k < mt_rand(5, 15) ; $k++){
                $recipe->addIngredient($ingredients[mt_rand(0, count($ingredients) - 1)]);
            }

            $recipes[] = $recipe;
            $manager->persist($recipe);
        }

        foreach ($recipes as $recipe) {
            for($i = 0; $i < mt_rand(0, 4); $i++){
                $mark = new Mark();
                $mark->setMark(mt_rand(1, 5))
                    ->setUser($users[mt_rand(0, count($users) - 1)])
                    ->setRecipe($recipe);

                $manager->persist($mark);
            }
        }

        for ($i = 0; $i < 5; $i++) {
            $contact = new Contact();
            $contact->setFullname($faker->name())
                ->setEmail($faker->email())
                ->setSubject('Demande n°' . ($i + 1))
                ->setMessage($faker->text());

            $manager->persist($contact);

        }

        $manager->flush();
    }
}
