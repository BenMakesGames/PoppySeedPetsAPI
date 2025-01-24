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
        if($pet->getBadges()->exists(fn(int $i, PetBadge $b) => $b->getBadge() === $badgeName))
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

    public static function awardBadgeAndLog(EntityManagerInterface $em, Pet $pet, string $badgeName, ?string $logMessage): ?PetActivityLog
    {
        if(!PetBadgeEnum::isAValue($badgeName))
            throw new EnumInvalidValueException(PetBadgeEnum::class, $badgeName);

        // if pet already has this badge, gtfo
        if($pet->getBadges()->exists(fn(int $i, PetBadge $b) => $b->getBadge() === $badgeName))
            return null;

        $newBadge = (new PetBadge())
            ->setBadge($badgeName)
            ->setPet($pet);

        $em->persist($newBadge);

        $pet->addBadge($newBadge);

        if($logMessage)
            $logMessage .= ' ' . str_replace('%pet.name%', ActivityHelpers::PetName($pet), self::BADGE_HURRAHS[$badgeName]);
        else
            $logMessage = str_replace('%pet.name%', ActivityHelpers::PetName($pet), self::BADGE_HURRAHS[$badgeName]);

        return PetActivityLogFactory::createUnreadLog($em, $pet, $logMessage)
            ->addTag(PetActivityLogTagHelpers::findOneByName($em, PetActivityLogTagEnum::Badge))
            ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_YIELDING_PET_BADGE)
        ;
    }

    private const BADGE_HURRAHS = [
        PetBadgeEnum::REVEALED_FAVORITE_FLAVOR => '(Also, there\'s a badge for that: Flavor YESknown!, and %pet.name% just got it!)',
        PetBadgeEnum::COMPLETED_HEART_DIMENSION => 'Also: A Suburb to the Brain - that\'s the name of the badge that %pet.name% just got!',
        PetBadgeEnum::TRIED_ON_A_NEW_STYLE => 'Check out those new, um, digs? Also: that new badge?! Yeah, I dunno if you knew, but %pet.name% just got the Fashionista badge.',

        PetBadgeEnum::FIRST_PLACE_CHESS => 'For demonstrating such chess prowess, %pet.name% received the Chess Master badge!',
        PetBadgeEnum::FIRST_PLACE_JOUSTING => 'For demonstrating such jousting prowess, %pet.name% received the Mount and Blade badge!',
        PetBadgeEnum::FIRST_PLACE_KIN_BALL => 'For demonstrating such kin-ball prowess, %pet.name% received the Expert Kin-baller badge!',

        PetBadgeEnum::CREATED_SENTIENT_BEETLE => 'Crimes against nature? More like... it\'d be criminal not to get a badge for that! %pet.name% received the Mad Scientist badge!',
        PetBadgeEnum::MET_THE_FLUFFMONGER => 'On their way out, the Fluffmonger gives %pet.name% the Very Fluffy badge.',
        PetBadgeEnum::OUTSMARTED_A_THIEVING_MAGPIE => 'Such a good civil servant! %pet.name% receives the Magpie Masher badge.',
        PetBadgeEnum::DECEIVED_A_VAMPIRE => 'Deceiving masters of deceit?! That definitely deserves a badge! %pet.name% received the Vampire Deceiver badge!',
        PetBadgeEnum::FOUND_CETGUELIS_TREASURE => 'And %pet.name% didn\'t just get a treasure, they got the Treasure Hunter badge!',
        PetBadgeEnum::HAD_A_BABY => 'And guess what: a pet that has a baby has a Baby-maker badge!',
        PetBadgeEnum::CLIMB_TO_TOP_OF_BEANSTALK => 'Such climb! Very high! %pet.name% gets the Castle in the Clouds badge!',
        PetBadgeEnum::SING_WITH_WHALES => 'Beautiful! %pet.name% received the Songs of the Deep badge.',
        PetBadgeEnum::PRODUCED_A_SKILL_SCROLL => '(Dang! That\'s a hard one! %pet.name% FOR SURE gets the Skill Scribe badge!)',
        PetBadgeEnum::PULLED_AN_ITEM_FROM_A_DREAM => 'Pulling an item out of a hat is one thing, but pulling one out of a dream?! %pet.name% DEFINITELY gets the Oneirokinetic badge for that!',
        PetBadgeEnum::GO => 'Let\'s goooooooo! (%pet.name% gets the Go badge!)',
        PetBadgeEnum::VISITED_THE_BURNT_FOREST => '%pet.name% received the The Burnt Forest badge for visiting this oft-forgotten corner of the Umbra.',
        PetBadgeEnum::JUMPED_ROPE_WITH_A_BUG => 'Adorable! Jump rope with a bug! %pet.name% received the Grasshopper badge.',
        PetBadgeEnum::WRANGLED_WITH_INFINITIES => 'When it comes to infinities, you win some, and you lose some. In this case, %pet.name% definitely wins a badge, at any rate: the Infinity Wrangler badge!',
        PetBadgeEnum::EXPLORED_AN_ICY_MOON => 'One small step for a pet... \'s badge collection - %pet.name% got the Icy Moon Explorer badge!',
        PetBadgeEnum::MET_A_FAMOUS_VIDEO_GAME_CHARACTER => '(That name sounds familiar... someone famous, maybe?) %pet.name% received the Internet Famous badge.',
        PetBadgeEnum::MINTED_MONEYS => 'When life gives you lemons, just mint your own money! %pet.name% gets the Mint (the Moneys Kind) badge.',
        PetBadgeEnum::RETURNED_A_SHIRIKODAMA => 'There isn\'t a shirikodama postal service to give %pet.name% the Shirikodama Courier badge, but they get it anyway. Somehow. Don\'t worry about it.',
        PetBadgeEnum::DEFEATED_A_WERECREATURE_WITH_SILVER => 'For super-effective use of silver against a werecreature, %pet.name% received the Type-advantaged badge.',
        PetBadgeEnum::POOPED_SHED_OR_BATHED => 'Life - so messy! %pet.name% got the Definitely Alive badge.',
        PetBadgeEnum::FISHED_AT_THE_ISLE_OF_RETREATING_TEETH => 'Fascinating place! Fascinating enough to warrant the Fish Teeth badge!',
        PetBadgeEnum::EXTRACT_QUINT_FROM_COOKIES => 'This is one of the very few times a pet might be said to cook food, and deserving of the Quintessential Cooking badge!',
        PetBadgeEnum::STRUGGLED_WITH_A_3D_PRINTER => 'So frustrating! Perhaps receiving the More Dimensions, More Problems badge will make %pet.name% feel a little better?',
        PetBadgeEnum::EMPTIED_THEIR_LUNCHBOX => '(I guess there could be a badge for that? Sure, why not: %pet.name% got the Om-nom-nom badge!)',
        PetBadgeEnum::CRAFTED_WITH_A_FULL_HOUSE => 'Hopefully that cleared up some space? For their efforts, %pet.name% got the Under Pressure badge.',
        PetBadgeEnum::CLIMBED_THE_TOWER_OF_TRIALS => 'And getting to the very tippy top of the Tower of Trials absolutely warrants an award of the Tower-toppler badge! Let it be so!',

        PetBadgeEnum::WAS_AN_ACCOUNTANT => 'Accounting is FUN! :D (Incidentally, that\'s also the name of the badge %pet.name% just earned!)',
        PetBadgeEnum::WAS_A_CHIMNEY_SWEEP => '🎵 With gnomes and with smoke all billered and curled, your pet got a badge: Chimney Sweep World! 🎵',
        PetBadgeEnum::GREENHOUSE_FISHER => 'Dang greenhouse fish - makin\' a mess! Why, it\'s enough to make someone give a pet the Greenhouse Fishin\' badge, it is!',
        PetBadgeEnum::BEE_NANA => 'Nana\'d a Naner with the bees?? %pet.name% is given the Bee Naner badge.',

        PetBadgeEnum::FOUND_METATRONS_FIRE => 'A legendary accomplishment, to be sure! %pet.name% receives the Radiant badge.',
        PetBadgeEnum::FOUND_VESICA_HYDRARGYRUM => 'A legendary accomplishment, to be sure! %pet.name% receives the Mercurial badge.',
        PetBadgeEnum::FOUND_EARTHS_EGG => 'A legendary accomplishment, to be sure! %pet.name% receives the Most Egg badge.',
        PetBadgeEnum::FOUND_MERKABA_OF_AIR => 'A legendary accomplishment, to be sure! %pet.name% receives the Zephyrous badge.',

        PetBadgeEnum::DEFEATED_NOETALAS_WING => '%pet.name% has definitely gained the attention of Noetala... and gained the She Watches badge! (Pros and cons?)',
        PetBadgeEnum::DEFEATED_CRYSTALLINE_ENTITY => 'An enterprising pet such as %pet.name% deserves every thread of this Data & Lore badge!',
        PetBadgeEnum::DEFEATED_BIVUS_RELEASE => 'It\'s not every day you witness an angel create a supernova... or defeat said supernova! %pet.name% received the Supernova Survivor badge.',

        PetBadgeEnum::LEVEL_20 => 'Level 20! %pet.name% is now a level 20 pet! %pet.name% gets the Level 20 badge!',
        PetBadgeEnum::LEVEL_40 => 'Level 40! %pet.name% is now a level 40 pet! %pet.name% gets the Level 40 baadge!! :)',
        PetBadgeEnum::LEVEL_60 => 'Level 60! %pet.name% is now a level 60 pet! %pet.name% gets the Level 60 baaadge!!! :P',
        PetBadgeEnum::LEVEL_80 => 'Level 80! %pet.name% is now a level 80 pet! %pet.name% gets the Level 80 baaaadge!!!! :D',
        PetBadgeEnum::LEVEL_100 => 'Level 100! %pet.name% is now a level 100 pet! %pet.name% gets the Level 100 baaaaadge!!!1! :O',

        PetBadgeEnum::FOUND_A_PLASTIC_EGG => 'Those things aren\'t edible. And speaking of, neither is the Incredible, Inedible Egg badge %pet.name% just earned!',
        PetBadgeEnum::FOUND_ONE_CLOVER_LEAF => 'Just one leaf? Eh, still: %pet.name% should probably get a badge for that. But only be a 1-leaf 4-leaf Clover badge!',
        PetBadgeEnum::DEFEATED_A_TURKEY_KING => 'Down with the monarchy! %pet.name% receives the Turkicide badge for their heroism!',
        PetBadgeEnum::WUVWY => 'Such handiwork is definitely deserving of the Wuwvy badge!',
        PetBadgeEnum::WAS_GIVEN_A_COSTUME_NAME => '%pet.name% received the Dressed for the Occasion badge.',
    ];
}