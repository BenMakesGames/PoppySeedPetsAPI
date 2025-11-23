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

use App\Entity\Merit;
use App\Enum\EnumInvalidValueException;
use App\Enum\MeritEnum;
use App\Exceptions\PSPNotFoundException;
use App\Model\MeritInfo;
use App\Service\IRandom;
use Doctrine\ORM\EntityManagerInterface;

final class MeritRepository
{
    public static function findOneByName(EntityManagerInterface $em, string $name): Merit
    {
        if(!MeritEnum::isAValue($name))
            throw new EnumInvalidValueException(MeritEnum::class, $name);

        $merit = $em->getRepository(Merit::class)->createQueryBuilder('m')
            ->where('m.name=:name')
            ->setParameter('name', $name)
            ->getQuery()
            ->enableResultCache(24 * 60 * 60, CacheHelpers::getCacheItemName('MeritRepository_FindOneByName_' . $name))
            ->getOneOrNullResult();

        if(!$merit) throw new PSPNotFoundException('There is no Merit called ' . $name . '.');

        return $merit;
    }

    public static function getRandomStartingMerit(EntityManagerInterface $em, IRandom $rng): Merit
    {
        return MeritRepository::findOneByName($em, $rng->rngNextFromArray(MeritInfo::POSSIBLE_STARTING_MERITS));
    }

    public static function getRandomFirstPetStartingMerit(EntityManagerInterface $em, IRandom $rng): Merit
    {
        return MeritRepository::findOneByName($em, $rng->rngNextFromArray(MeritInfo::POSSIBLE_FIRST_PET_STARTING_MERITS));
    }

    public static function getRandomAdoptedPetStartingMerit(EntityManagerInterface $em, IRandom $rng): Merit
    {
        $possibleMerits = array_filter(MeritInfo::POSSIBLE_STARTING_MERITS, function(string $m) {
            return $m !== MeritEnum::HYPERCHROMATIC && $m !== MeritEnum::SPECTRAL;
        });

        return MeritRepository::findOneByName($em, $rng->rngNextFromArray($possibleMerits));
    }
}
