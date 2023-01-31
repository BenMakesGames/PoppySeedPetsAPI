<?php

namespace App\Functions;

use App\Entity\User;
use App\Entity\UserActivityLog;
use App\Entity\UserActivityLogTag;
use Doctrine\ORM\EntityManagerInterface;

class PlayerLogHelpers
{
    public static function Create(EntityManagerInterface $em, User $user, string $entry, array $tagNames): UserActivityLog
    {
        $em->getRepository(UserActivityLogTag::class)->findByNames($tagNames);

        $log = (new UserActivityLog())
            ->setUser($user)
            ->setEntry($entry)
            ->addTags($tagNames)
        ;

        $em->persist($log);

        return $log;
    }
}