<?php

namespace App\DataFixtures;

use App\Entity\Organization;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class OrganizationFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $organization = new Organization();
        $organization->setName('DigiDev LLC');
        $manager->persist($organization);

        $manager->flush();
    }
}
