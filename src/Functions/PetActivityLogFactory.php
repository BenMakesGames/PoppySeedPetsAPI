<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Functions;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetActivityLogPet;
use App\Entity\UnreadPetActivityLog;
use Doctrine\ORM\EntityManagerInterface;

final class PetActivityLogFactory
{
    public static function createUnreadPetLog(EntityManagerInterface $em, Pet $pet, string $message): PetActivityLogPet
    {
        $log = self::createReadPetLog($em, $pet, $message);

        $unreadLog = new UnreadPetActivityLog($log);
        $em->persist($unreadLog);

        return $log;
    }

    public static function createReadPetLog(EntityManagerInterface $em, Pet $pet, string $message): PetActivityLogPet
    {
        $log = new PetActivityLog($message);

        $petLog = new PetActivityLogPet($pet, $log);

        $em->persist($log);
        $em->persist($petLog);

        return $petLog;
    }

    /**
     * @param Pet[] $pets
     * @return PetActivityLogPet[]
     */
    public static function createUnreadGroupLogs(EntityManagerInterface $em, array $pets, string $message): array
    {
        $logs = self::createReadGroupLogs($em, $pets, $message);

        foreach($logs as $log)
        {
            $unreadLog = new UnreadPetActivityLog($log);
            $em->persist($unreadLog);
        }

        return $logs;
    }

    /**
     * @param Pet[] $pets
     * @return PetActivityLogPet[]
     */
    public static function createReadGroupLogs(EntityManagerInterface $em, array $pets, string $message): array
    {
        $log = new PetActivityLog($message);
        $em->persist($log);

        $petLogs = [];

        foreach ($pets as $pet) {
            $petLog = new PetActivityLogPet($pet, $log);
            $em->persist($petLog);
            $petLogs[] = $petLog;
        }

        return $petLogs;
    }

    /**
     * @deprecated Use {@see createUnreadPetLog} or {@see createUnreadGroupLogs} instead
     */
    public static function createUnreadLog(EntityManagerInterface $em, Pet $pet, string $message): PetActivityLog
    {
        $log = self::createReadLog($em, $pet, $message);

        foreach($log->getPetActivityLogPets() as $petLog) {
            $unreadLog = new UnreadPetActivityLog($petLog);
            $em->persist($unreadLog);
        }

        return $log;
    }

    /**
     * @deprecated Use {@see createReadPetLog} or {@see createReadGroupLogs} instead
     */
    public static function createReadLog(EntityManagerInterface $em, Pet $pet, string $message): PetActivityLog
    {
        $log = new PetActivityLog($message);

        $petLog = new PetActivityLogPet($pet, $log);

        $em->persist($log);
        $em->persist($petLog);

        return $log;
    }
}