<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Images;
use App\Entity\Products;
use App\Entity\Categories;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

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
                for($k = 1; $k <=10; $k++)
                
                $product = new Products();

                $name = $faker->userName();
                $content = '<p>'.join('</p><p>', $faker->paragraphs(1)).'</p>';
                $createdAt = $faker->dateTimeBetween('-6 months');
                $promo = $faker->boolean(70);

                $product->setName($name)                    
                    ->setContent($content)
                    ->setCreatedAt($createdAt)
                    ->setStock(mt_rand(3, 10))
                    ->setPrice(mt_rand(30, 100))
                    ->setPromo($promo)
                    ->setCategories($categorie)
            ;
                $manager->persist($product);
                
                // IMAGES
                {
                    $image = new Images();
    
                    $name = $faker->imageUrl(300, 200);
    
                    $image->setName($name)
                          ->setProducts($product);
    
                    $manager->persist($image);
                }
        }
        
        $manager->flush();
    }    
}