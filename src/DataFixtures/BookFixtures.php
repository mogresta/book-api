<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Enum\Genre;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class BookFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 20; $i++) {
            $book = new Book();

            $book->setIsbn($faker->isbn13())
                ->setTitle($faker->words(3, true))
                ->setAuthor($faker->name())
                ->setPublishedYear($faker->numberBetween(1900, 2024))
                ->setGenre($faker->randomElement(Genre::cases()));

            $manager->persist($book);
        }

        $manager->flush();
    }
}