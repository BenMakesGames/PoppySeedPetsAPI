<?php
declare(strict_types=1);

namespace App\Functions;

use App\Entity\User;
use App\Entity\UserUnlockedFeature;
use Doctrine\ORM\EntityManagerInterface;

final class UserUnlockedFeatureHelpers
{
    // warning: side effects!
    private static array $createdThisRequest = [];

    public static function create(EntityManagerInterface $em, User $user, string $feature)
    {
        if(in_array($feature, UserUnlockedFeatureHelpers::$createdThisRequest))
            return;

        UserUnlockedFeatureHelpers::$createdThisRequest[] = $feature;

        $entity = (new UserUnlockedFeature())
            ->setUser($user)
            ->setFeature($feature)
        ;

        $user->addUnlockedFeature($entity);

        $em->persist($entity);
    }
}
