<?php

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
                $record = (new UserQuest())
                    ->setUser($user)
                    ->setName($name)
                    ->setValue($default)
                ;

                $em->persist($record);
            }

            self::$userQuestPerRequestCache[$cacheKey] = $record;
        }

        return self::$userQuestPerRequestCache[$cacheKey];
    }
}
