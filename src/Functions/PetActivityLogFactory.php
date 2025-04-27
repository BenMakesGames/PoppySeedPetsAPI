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
     * Create a log which is already marked as having been read.
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