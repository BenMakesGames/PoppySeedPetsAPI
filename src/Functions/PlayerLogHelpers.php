<?php

namespace App\Functions;

use App\Entity\User;
use App\Entity\UserActivityLog;
use App\Entity\UserActivityLogTag;
use Doctrine\ORM\EntityManagerInterface;

final class PlayerLogHelpers
{
    /**
     * @param string[] $tagNames
     */
    public static function create(EntityManagerInterface $em, User $user, string $entry, array $tagNames): UserActivityLog
    {
        $tags = $em->getRepository(UserActivityLogTag::class)->findBy([ 'title' => $tagNames ]);

        $log = (new UserActivityLog())
            ->setUser($user)
            ->setEntry($entry)
            ->addTags($tags)
        ;

        $em->persist($log);

        return $log;
    }
}