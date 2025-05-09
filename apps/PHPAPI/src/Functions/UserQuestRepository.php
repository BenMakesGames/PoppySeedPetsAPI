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

use App\Entity\User;
use App\Entity\UserQuest;
use Doctrine\ORM\EntityManagerInterface;

class UserQuestRepository
{
    private static array $userQuestPerRequestCache = [];

    public static function find(EntityManagerInterface $em, User $user, string $name): ?UserQuest
    {
        $cacheKey = $user->getId() . '-' . $name;

        if(array_key_exists($cacheKey, self::$userQuestPerRequestCache))
            return self::$userQuestPerRequestCache[$cacheKey];

        $value = $em->getRepository(UserQuest::class)->findOneBy([
            'user' => $user,
            'name' => $name,
        ]);

        if($value)
            self::$userQuestPerRequestCache[$cacheKey] = $value;

        return $value;
    }

    public static function findOrCreate(EntityManagerInterface $em, User $user, string $name, $default): UserQuest
    {
        $cacheKey = $user->getId() . '-' . $name;

        if(!array_key_exists($cacheKey, self::$userQuestPerRequestCache))
        {
            $record = $em->getRepository(UserQuest::class)->findOneBy([
                'user' => $user,
                'name' => $name,
            ]);

            if(!$record)
            {
                $record = new UserQuest(user: $user, name: $name, value: $default);

                $em->persist($record);
            }

            self::$userQuestPerRequestCache[$cacheKey] = $record;
        }

        return self::$userQuestPerRequestCache[$cacheKey];
    }
}
