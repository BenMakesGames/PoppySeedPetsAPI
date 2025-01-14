<?php

namespace App\Functions;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetBadge;
use App\Enum\EnumInvalidValueException;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\PetBadgeEnum;
use Doctrine\ORM\EntityManagerInterface;

final class PetBadgeHelpers
{
    public static function awardBadge(EntityManagerInterface $em, Pet $pet, string $badgeName, PetActivityLog $log)
    {
        if(!PetBadgeEnum::isAValue($badgeName))
            throw new EnumInvalidValueException(PetBadgeEnum::class, $badgeName);

        // if pet already has this badge, gtfo
        if($pet->getBadges()->exists(fn(PetBadge $b) => $b->getBadge() === $badgeName))
            return;

        $newBadge = (new PetBadge())
            ->setBadge($badgeName)
            ->setPet($pet);

        $em->persist($newBadge);

        $pet->addBadge($newBadge);

        $log
            ->setEntry($log->getEntry() . ' ' . str_replace('%pet.name%', ActivityHelpers::PetName($pet), self::BADGE_HURRAHS[$badgeName]))
            ->addTag(PetActivityLogTagHelpers::findOneByName($em, PetActivityLogTagEnum::Badge))
            ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_YIELDING_PET_BADGE)
        ;
    }

    private const BADGE_HURRAHS = [
        PetBadgeEnum::COMPLETED_HEART_DIMENSION => 'Also: A Suburb to the Brain - that\'s the name of the badge that %pet.name% just got!',
        PetBadgeEnum::FIRST_PLACE_CHESS => 'For demonstrating such chess prowess, %pet.name% received the Chess Master badge!',
        PetBadgeEnum::FIRST_PLACE_JOUSTING => 'For demonstrating such jousting prowess, %pet.name% received the Mount and Blade badge!',
        PetBadgeEnum::FIRST_PLACE_KIN_BALL => 'For demonstrating such kin-ball prowess, %pet.name% received the Expert Kin-baller badge!',
        PetBadgeEnum::CREATED_SENTIENT_BEETLE => 'Crimes against nature? More like... it\'d be criminal not to get a badge for that! %pet.name% received the Mad Scientist badge!',
        PetBadgeEnum::MET_THE_FLUFFMONGER => 'On their way out, the Fluffmonger gives %pet.name% the Very Fluffy badge.',
        PetBadgeEnum::OUTSMARTED_A_THIEVING_MAGPIE => 'Such a good civil servant! %pet.name% receives the Magpie Masher badge.',
        PetBadgeEnum::FOUND_CETGUELIS_TREASURE => 'And %pet.name% didn\'t just get a treasure, they got the Treasure Hunter badge!',
        PetBadgeEnum::HAD_A_BABY => 'And guess what: a pet that has a baby has a Baby-maker badge!',

        PetBadgeEnum::CLIMB_TO_TOP_OF_BEANSTALK => 'Such climb! Very high! %pet.name% gets the Castle in the Clouds badge!',
        PetBadgeEnum::SING_WITH_WHALES => 'Beautiful! %pet.name% received the Songs of the Deep badge.',

        PetBadgeEnum::FOUND_METATRONS_FIRE => 'A legendary accomplishment, to be sure! %pet.name% receives the Radiant badge.',
        PetBadgeEnum::FOUND_VESICA_HYDRARGYRUM => 'A legendary accomplishment, to be sure! %pet.name% receives the Mercurial badge.',
        PetBadgeEnum::FOUND_EARTHS_EGG => 'A legendary accomplishment, to be sure! %pet.name% receives the Most Egg badge.',
        PetBadgeEnum::FOUND_MERKABA_OF_AIR => 'A legendary accomplishment, to be sure! %pet.name% receives the Zephyrous badge.',

        PetBadgeEnum::DEFEATED_NOETALAS_WING => '%pet.name% received the Noetala\'s Wing badge! Well deserved!',
        PetBadgeEnum::DEFEATED_CRYSTALLINE_ENTITY => 'An enterprising pet such as %pet.name% deserves every thread of this Data & Lore badge!',
    ];
}