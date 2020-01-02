<?php

namespace App\DataFixtures;

use App\Entity\MediaFile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class MediaFileFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $dt = new \DateTime();
        $mediaFile = new MediaFile();
        $mediaFile->setName('New File 1');
        $mediaFile->setTimestamp($dt);
        $mediaFile->setSize(1024);
        $mediaFile->setMimeType('image/jpeg');
        $mediaFile->setPath("/{$dt->format('Y')}/{$dt->format('m')}/test1.jpg");
        $manager->persist($mediaFile);

        $manager->flush();
    }
}
