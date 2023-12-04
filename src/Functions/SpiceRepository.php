<?php

namespace App\Functions;

use App\Entity\Spice;
use Doctrine\ORM\EntityManagerInterface;

class SpiceRepository
{
    public static function findOneByName(EntityManagerInterface $em, string $name): ?Spice
    {
        return $em->getRepository(Spice::class)->findOneBy([ 'name' => $name ]);
    }
}
