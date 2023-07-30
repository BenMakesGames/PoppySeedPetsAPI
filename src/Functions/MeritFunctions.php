<?php
namespace App\Functions;

use App\Entity\Merit;
use App\Entity\Pet;
use App\Enum\MeritEnum;
use App\Enum\MeritInfo;

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
            $canUnlearn = array_diff($canUnlearn, [ MeritEnum::VOLAGAMY ]);
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
            switch($merit)
            {
                case MeritEnum::VOLAGAMY:
                    $available = $petAgeInDays >= 14;
                    break;

                case MeritEnum::INTROSPECTIVE:
                    $available = $pet->getRelationshipCount() >= 3;
                    break;

                // stat-based merits:

                case MeritEnum::MOON_BOUND:
                    $available = $pet->getSkills()->getStrength() >= 3;
                    break;

                case MeritEnum::DARKVISION:
                    $available = $pet->getSkills()->getPerception() >= 3;
                    break;

                case MeritEnum::EIDETIC_MEMORY:
                    $available = $pet->getSkills()->getIntelligence() >= 3;
                    break;

                case MeritEnum::GECKO_FINGERS:
                    $available = $pet->getSkills()->getDexterity() >= 3;
                    break;

                case MeritEnum::IRON_STOMACH:
                    $available = $pet->getSkills()->getStamina() >= 3;
                    break;

                // skill-based merits:

                case MeritEnum::CELESTIAL_CHORUSER:
                    $available = $pet->getSkills()->getMusic() >= 5;
                    break;

                case MeritEnum::NO_SHADOW_OR_REFLECTION:
                    $available = $pet->getSkills()->getStealth() >= 5;
                    break;

                case MeritEnum::SPIRIT_COMPANION:
                    $available = $pet->getSkills()->getUmbra() >= 5;
                    break;

                case MeritEnum::GREEN_THUMB:
                    $available = $pet->getSkills()->getNature() >= 5;
                    break;

                case MeritEnum::SHOCK_RESISTANT:
                    $available = $pet->getSkills()->getScience() >= 5;
                    break;

                case MeritEnum::WAY_OF_THE_EMPTY_HAND:
                    $available = $pet->getSkills()->getBrawl() >= 5;
                    break;

                case MeritEnum::ATHENAS_GIFTS:
                    $available = $pet->getSkills()->getCrafts() >= 5;
                    break;

                default:
                    $available = true;
            }

            if($available)
                $availableMerits[] = $merit;
        }

        return $availableMerits;
    }
}
