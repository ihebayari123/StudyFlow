<?php

namespace App\Tests\Service;

use App\Entity\Sponsor;
use App\Service\SponsorManager;
use PHPUnit\Framework\TestCase;

class SponsorManagerTest extends TestCase
{
    public function testValidSponsor()
    {
        $sponsor = new Sponsor();
        $sponsor->setNomSponsor('Orange');
        $sponsor->setType('Gold');
        $sponsor->setMontant(5000);

        $manager = new SponsorManager();
        $this->assertTrue($manager->validate($sponsor));
    }

    public function testSponsorWithoutName()
    {
        $this->expectException(\InvalidArgumentException::class);

        $sponsor = new Sponsor();
        $sponsor->setNomSponsor('');
        $sponsor->setType('Gold');
        $sponsor->setMontant(5000);

        $manager = new SponsorManager();
        $manager->validate($sponsor);
    }

    public function testSponsorWithInvalidType()
    {
        $this->expectException(\InvalidArgumentException::class);

        $sponsor = new Sponsor();
        $sponsor->setNomSponsor('Orange');
        $sponsor->setType('Platinum');
        $sponsor->setMontant(5000);

        $manager = new SponsorManager();
        $manager->validate($sponsor);
    }

    public function testSponsorWithInvalidMontant()
    {
        $this->expectException(\InvalidArgumentException::class);

        $sponsor = new Sponsor();
        $sponsor->setNomSponsor('Orange');
        $sponsor->setType('Silver');
        $sponsor->setMontant(-100);

        $manager = new SponsorManager();
        $manager->validate($sponsor);
    }

    public function testSponsorWithZeroMontant()
    {
        $this->expectException(\InvalidArgumentException::class);

        $sponsor = new Sponsor();
        $sponsor->setNomSponsor('Orange');
        $sponsor->setType('Bronze');
        $sponsor->setMontant(0);

        $manager = new SponsorManager();
        $manager->validate($sponsor);
    }

    public function testValidSponsorWithSilverType()
    {
        $sponsor = new Sponsor();
        $sponsor->setNomSponsor('Samsung');
        $sponsor->setType('Silver');
        $sponsor->setMontant(3000);

        $manager = new SponsorManager();
        $this->assertTrue($manager->validate($sponsor));
    }
}