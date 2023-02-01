<?php

namespace App\Functions;

use App\Entity\User;
use App\Entity\UserActivityLog;
use App\Entity\UserActivityLogTag;
use Doctrine\ORM\EntityManagerInterface;

class PlayerLogHelpers
{
    /**
     * @param string[] $tagNames
     */
    public static function Create(EntityManagerInterface $em, User $user, string $entry, array $tagNames): UserActivityLog
    {
        $tags = $em->getRepository(UserActivityLogTag::class)->findByNames($tagNames);

        $log = (new UserActivityLog())
            ->setUser($user)
            ->setEntry($entry)
            ->addTags($tags)
        ;

        $em->persist($log);

        return $log;
    }
}