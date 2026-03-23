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
use App\Entity\UserUnlockedFeature;
use App\Enum\UnlockableFeatureEnum;
use Doctrine\ORM\EntityManagerInterface;

final class UserUnlockedFeatureHelpers
{
    // warning: side effects!
    private static array $createdThisRequest = [];

    public static function create(EntityManagerInterface $em, User $user, UnlockableFeatureEnum $feature): void
    {
        if(in_array($feature, UserUnlockedFeatureHelpers::$createdThisRequest))
            return;

        UserUnlockedFeatureHelpers::$createdThisRequest[] = $feature;

        $entity = new UserUnlockedFeature($user, $feature);

        $user->addUnlockedFeature($entity);

        $em->persist($entity);
    }
}
