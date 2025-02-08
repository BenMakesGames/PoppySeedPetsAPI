<?php
declare(strict_types=1);

namespace App\Functions;

use App\Entity\Dragon;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class DragonRepository
{
    public static function findWhelp(EntityManagerInterface $em, User $user): ?Dragon
    {
        return $em->getRepository(Dragon::class)->findOneBy([
            'owner' => $user,
            'isAdult' => false
        ]);
    }
}
