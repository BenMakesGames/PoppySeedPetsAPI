<?php

namespace App\Functions;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\UnreadPetActivityLog;
use Doctrine\ORM\EntityManagerInterface;

final class PetActivityLogFactory
{
    public static function createUnreadLog(EntityManagerInterface $em, Pet $pet, string $message): PetActivityLog
    {
        $log = self::createReadLog($em, $pet, $message);

        $unreadLog = (new UnreadPetActivityLog())
            ->setPet($pet)
            ->setPetActivityLog($log);

        $em->persist($unreadLog);

        return $log;
    }

    /**
     * Create a log which is already marked as having been read.
     */
    public static function createReadLog(EntityManagerInterface $em, Pet $pet, string $message): PetActivityLog
    {
        $log = (new PetActivityLog())
            ->setPet($pet)
            ->setEntry($message);

        $em->persist($log);

        return $log;
    }
}