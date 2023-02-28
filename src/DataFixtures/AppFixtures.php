<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Category;
use Faker\Factory as Faker;
use Doctrine\Persistence\ObjectManager;
use function Symfony\Component\String\u;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    /**
     * Permet d'encoder le mot de passe de l'utilisateur
     * 
     * @var UserPasswordHasherInterface
     */

    public function __construct(private UserPasswordHasherInterface $passwordHasher, private SluggerInterface $sluggerInterface)
    {
    }

    /**
     * Jeu de données pour les entity
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {

        $faker = Faker::create('fr_FR');
        for ($i = 0; $i < 50; $i++) {
            $user = new User();
            $user
                ->setEmail($faker->email())
                ->setUsername($faker->userName())
                ->setPassword($this->passwordHasher->hashPassword($user, '123456789'))
                ->setIsActive(rand(0, 1))
                ->setCreatedAt(DateTimeImmutable::createFromMutable($faker->dateTime($max = 'now')))
                ->setUpdatedAt(DateTimeImmutable::createFromMutable($faker->dateTimeBetween($user->getCreatedAt()->format('Y-m-d'), 'now')));
            //  1er user avec role admin
            if ($i == 0) $user->setRoles(['ROLE_ADMIN'])
                ->setIsActive(1);
            //  2eme user avec role ROLE_AUTHOR
            if ($i == 1) $user->setRoles(['ROLE_AUTHOR'])
                ->setIsActive(1);
            $manager->persist($user);
            $users[] = $user;
            shuffle($users);
        }

        for($i = 0; $i < 10; $i++)
        {
            $category = new Category();
            $category
                ->setName('Category_'.$faker->word())
                ->setColor($faker->safeColorName())
                ->setCreatedAt(DateTimeImmutable::createFromMutable($faker->dateTime($max = 'now')))
                ->setUpdatedAt(DateTimeImmutable::createFromMutable($faker->dateTimeBetween($category->getCreatedAt()->format('Y-m-d'), 'now')));

            $manager->persist($category);
            $categories[] = $category;
            shuffle($categories);
        }

        for($i = 0; $i < 100; $i++)
        {    
            
            $randCategory = array_rand($categories);
            $category = $categories[$randCategory];

            $article = new Article();
            $article
                ->setTitle($faker->words(3, true))
                ->setSlug($this->sluggerInterface->slug($article->getTitle()))
                ->setContent($faker->sentences(3, true))
                ->setStatus($faker->numberBetween(0, 3))
                ->setCreatedAt(DateTimeImmutable::createFromMutable($faker->dateTimeBetween($category->getCreatedAt()->format('Y-m-d'), 'now')))
                ->setUpdaedAt(DateTimeImmutable::createFromMutable($faker->dateTimeBetween($article->getCreatedAt()->format('Y-m-d'), 'now')))
                ->setFeaturedText(substr($article->getContent(), 0, 200))
                ->setFeaturedImage(str_replace('public/uploads\\','',$faker->image('public/uploads',640, 480, $article->getSlug(), true)))
                ->setCategory($category)
                ->setAuthor($users[1]);

            $manager->persist($article);
            $articles[] = $article;
            shuffle($articles);
        }

        for($i = 0; $i < 300; $i++)
        {    
            $randomKey = array_rand($users);
            if($randomKey === 0 || $randomKey === 1 ) $randomKey++;
            $user = $users[$randomKey];
            $randomKeyArticles = array_rand($articles);
            $article = $articles[$randomKeyArticles];

            $commentaire = new Comment();
            $commentaire
                ->setAuthor($user)
                ->setArticle($article)
                ->setContent($faker->sentences(3, true))
                ->setIsActive(rand(0, 1))
                ->setCreatedAt(DateTimeImmutable::createFromMutable($faker->dateTimeBetween($article->getCreatedAt()->format('Y-m-d'), 'now')))
            ;

            $manager->persist($commentaire);
        }

        $manager->flush();
    }

    
}
