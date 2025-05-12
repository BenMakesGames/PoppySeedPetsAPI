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
use App\Entity\Pet;
use App\Enum\MeritEnum;
use App\Enum\PetPregnancyStyleEnum;
use App\Model\MeritInfo;

class MeritFunctions
{
    /**
     * @return string[]
     */
    public static function getUnlearnableMerits(Pet $pet): array
    {
        $petMerits = array_map(fn(Merit $m) => $m->getName(), $pet->getMerits()->toArray());
        $canUnlearn = array_values(array_intersect(
            $petMerits,
            MeritInfo::FORGETTABLE_MERITS
        ));

        if($pet->getPregnancy())
        {
            // remove MeritEnum::VOLAGAMY from $canUnlearn:
            $canUnlearn = array_values(array_diff($canUnlearn, [ MeritEnum::VOLAGAMY ]));
        }

        return $canUnlearn;
    }

    /**
     * @return string[]
     */
    public static function getAvailableMerits(Pet $pet): array
    {
        /** @var string[] $availableMerits */
        $availableMerits = [];

        foreach(MeritInfo::AFFECTION_REWARDS as $merit)
        {
            if($pet->hasMerit($merit))
                continue;

            $petAgeInDays = (new \DateTimeImmutable())->diff($pet->getBirthDate())->days >= 14;

            // some merits have additional requirements:
            $available = match($merit) {
                MeritEnum::VOLAGAMY => $petAgeInDays >= 14 && $pet->getSpecies()->getPregnancyStyle() !== PetPregnancyStyleEnum::Impossible,
                MeritEnum::INTROSPECTIVE => $pet->getRelationshipCount() >= 3,

                // stat-based merits:
                MeritEnum::MOON_BOUND => $pet->getSkills()->getStrength() >= 3,
                MeritEnum::DARKVISION => $pet->getSkills()->getPerception() >= 3,
                MeritEnum::EIDETIC_MEMORY => $pet->getSkills()->getIntelligence() >= 3,
                MeritEnum::GECKO_FINGERS => $pet->getSkills()->getDexterity() >= 3,
                MeritEnum::IRON_STOMACH => $pet->getSkills()->getStamina() >= 3,

                // skill-based merits:
                MeritEnum::CELESTIAL_CHORUSER => $pet->getSkills()->getMusic() >= 5,
                MeritEnum::NO_SHADOW_OR_REFLECTION => $pet->getSkills()->getStealth() >= 5,
                MeritEnum::SPIRIT_COMPANION => $pet->getSkills()->getArcana() >= 5,
                MeritEnum::GREEN_THUMB => $pet->getSkills()->getNature() >= 5,
                MeritEnum::SHOCK_RESISTANT => $pet->getSkills()->getScience() >= 5,
                MeritEnum::WAY_OF_THE_EMPTY_HAND => $pet->getSkills()->getBrawl() >= 5,
                MeritEnum::ATHENAS_GIFTS => $pet->getSkills()->getCrafts() >= 5,

                default => true
            };

            if($available)
                $availableMerits[] = $merit;
        }

        return $availableMerits;
    }
}
