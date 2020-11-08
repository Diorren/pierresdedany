<?php

namespace App\DataFixtures;

use App\Entity\Categories;
use Faker\Factory;
use App\Entity\Products;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr-FR');
        
        // CATEGORIES
    
        for ($j = 1; $j <= 10; ++$j) {
            $categorie = new Categories();

            $name = $faker->sentence();

            $categorie->setName($name)
        ;

            $manager->persist($categorie);

            // PRODUITS

                $product = new Products();

                $name = $faker->userName();
                $image = $faker->imageUrl(300, 200);
                $content = '<p>'.join('</p><p>', $faker->paragraphs(1)).'</p>';
                $createdAt = $faker->dateTimeBetween('-6 months');
                $promo = $faker->boolean(70);

                $product->setName($name)
                    ->setImage($image)
                    ->setContent($content)
                    ->setCreated_at($createdAt)
                    ->setQuantity(mt_rand(3, 10))
                    ->setPrice(mt_rand(30, 100))
                    ->setPromo($promo)
                    ->setCategories($categorie)
            ;
                $manager->persist($product);
        }
        
        $manager->flush();
    }    
}