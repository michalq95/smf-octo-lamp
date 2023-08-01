<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Offer;
use App\Entity\Tags;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private $hasher;
    private $faker;
    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->faker = Factory::create();
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadCompanies($manager);
        $this->loadTags($manager);
        $this->loadOffers($manager);
    }

    public function loadTags(ObjectManager $manager)
    {
        $tags = [
            'JavaScript',
            'PHP',
            'Junior',
            'Mid',
            'Python',
            'Java',
            'Symfony',
            'SQL',
            'Docker',
            'Laravel',
        ];
        foreach ($tags as $i => $t) {
            $tag = new Tags();
            $tag->setName($t);
            $tag->setAccepted(true);
            $this->addReference('tag' . $i, $tag);
            $manager->persist($tag);
        }
        $manager->flush();
    }

    public function loadCompanies(ObjectManager $manager)
    {
        for ($i = 0; $i < 10; $i++) {
            $user = $this->getReference('user' . $i);
            $company = new Company();
            $company->setTitle($this->faker->realText(20));
            $company->setEmail('company' . $i . '@company.pl');
            // $company->setPublished($this->faker->dateTime);

            $company->setContent($this->faker->realText(200));
            $company->setOwner($user);
            // $company->setSlug("title");

            $this->setReference("company" . $i, $company);

            $manager->persist($company);
        }
        $manager->flush();
    }

    public function loadOffers(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            $offer = new Offer();
            $offer->setTitle($this->faker->realText(20));
            $offer->setContent($this->faker->realText(200));
            $offer->setPublished($this->faker->dateTime);
            foreach (array_rand(range(0, 9), 3) as $a) {
                $offer->addTag($this->getReference('tag' . $a));
            }
            $offer->setCompany($this->getReference('company' . $i % 10));
            $offer->setStatus(1);
            $manager->persist($offer);
        }
        $manager->flush();
    }

    public function loadUsers(ObjectManager $manager)
    {

        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail('user' . $i . '@user.pl');
            $user->setName('user' . $i);
            $user->setActivated(true);
            $hashedPassword = $this->hasher->hashPassword($user, "password");
            $user->setPassword($hashedPassword);
            $this->addReference('user' . $i, $user);
            $manager->persist($user);
        }
        $manager->flush();
    }
}