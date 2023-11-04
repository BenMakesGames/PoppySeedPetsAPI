<?php

namespace App\Functions;

use App\Entity\Dragon;
use App\Entity\User;
use App\Entity\UserStats;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class DragonHelpers
{
    public static function getAdultDragon(EntityManagerInterface $em, User $user): ?Dragon
    {
        return $em->getRepository(Dragon::class)->findOneBy([
            'owner' => $user,
            'isAdult' => true
        ]);
    }

    public static function createDragonResponse(EntityManagerInterface $em, NormalizerInterface $normalizer, User $user, Dragon $dragon): array
    {
        $treasuresGiven = $em->getRepository(UserStats::class)->findOneBy([
            'user' => $user,
            'stat' => UserStatEnum::TREASURES_GIVEN_TO_DRAGON_HOARD
        ]);

        $response = $normalizer->normalize($dragon, null, [ 'groups' => [
            SerializationGroupEnum::MY_DRAGON,
            SerializationGroupEnum::HELPER_PET
        ]]);

        $response['treasureCount'] = $treasuresGiven ? $treasuresGiven->getValue() : 0;

        return $response;
    }
}