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

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetBadge;
use App\Enum\EnumInvalidValueException;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\PetBadgeEnum;
use Doctrine\ORM\EntityManagerInterface;

final class PetBadgeHelpers
{
    public static function awardBadge(EntityManagerInterface $em, Pet $pet, string $badgeName, PetActivityLog $log): void
    {
        if(!PetBadgeEnum::isAValue($badgeName))
            throw new EnumInvalidValueException(PetBadgeEnum::class, $badgeName);

        // if pet already has this badge, gtfo
        if($pet->getBadges()->exists(fn(int $i, PetBadge $b) => $b->getBadge() === $badgeName))
            return;

        $newBadge = new PetBadge(pet: $pet, badge: $badgeName);

        $em->persist($newBadge);

        $pet->addBadge($newBadge);

        $log
            ->setEntry($log->getEntry() . ' Also: whoa! ' . str_replace('%pet.name%', ActivityHelpers::PetName($pet), self::BadgeHurrahs[$badgeName]))
            ->addTag(PetActivityLogTagHelpers::findOneByName($em, PetActivityLogTagEnum::Badge))
            ->addInterestingness(PetActivityLogInterestingness::ActivityYieldingPetBadge)
        ;
    }

    public static function awardBadgeAndLog(EntityManagerInterface $em, Pet $pet, string $badgeName, ?string $logMessage): ?PetActivityLog
    {
        if(!PetBadgeEnum::isAValue($badgeName))
            throw new EnumInvalidValueException(PetBadgeEnum::class, $badgeName);

        // if pet already has this badge, gtfo
        if($pet->getBadges()->exists(fn(int $i, PetBadge $b) => $b->getBadge() === $badgeName))
            return null;

        $newBadge = new PetBadge(pet: $pet, badge: $badgeName);

        $em->persist($newBadge);

        $pet->addBadge($newBadge);

        if($logMessage)
            $logMessage .= ' ' . str_replace('%pet.name%', ActivityHelpers::PetName($pet), self::BadgeHurrahs[$badgeName]);
        else
            $logMessage = str_replace('%pet.name%', ActivityHelpers::PetName($pet), self::BadgeHurrahs[$badgeName]);

        return PetActivityLogFactory::createUnreadLog($em, $pet, $logMessage)
            ->addTag(PetActivityLogTagHelpers::findOneByName($em, PetActivityLogTagEnum::Badge))
            ->addInterestingness(PetActivityLogInterestingness::ActivityYieldingPetBadge)
        ;
    }

    private const array BadgeHurrahs = [
        PetBadgeEnum::RevealedFavoriteFlavor => '(Also, there\'s a badge for that: Flavor YESknown!, and %pet.name% just got it!)',
        PetBadgeEnum::CompletedHeartDimension => 'Also: A Suburb to the Brain - that\'s the name of the badge that %pet.name% just got!',
        PetBadgeEnum::TriedOnANewStyle => 'Check out those new, um, digs? Also: that new badge?! Yeah, I dunno if you knew, but %pet.name% just got the Fashionista badge.',
        PetBadgeEnum::HadAFoodCravingSatisfied => 'So satisfying, %pet.name% got the Satisfaction badge.',

        PetBadgeEnum::FirstPlaceChess => 'For demonstrating such chess prowess, %pet.name% received the Chess Master badge!',
        PetBadgeEnum::FirstPlaceJousting => 'For demonstrating such jousting prowess, %pet.name% received the Mount and Blade badge!',
        PetBadgeEnum::FirstPlaceKinBall => 'For demonstrating such kin-ball prowess, %pet.name% received the Expert Kin-baller badge!',

        PetBadgeEnum::CreatedSentientBeetle => 'Crimes against nature? More like... it\'d be criminal not to get a badge for that! %pet.name% received the Mad Scientist badge!',
        PetBadgeEnum::MetTheFluffmonger => 'On their way out, the Fluffmonger gives %pet.name% the Very Fluffy badge.',
        PetBadgeEnum::OutsmartedAThievingMagpie => 'Such a good civil servant! %pet.name% receives the Magpie Masher badge.',
        PetBadgeEnum::DeceivedAVampire => 'Deceiving masters of deceit?! That definitely deserves a badge! %pet.name% received the Vampire Deceiver badge!',
        PetBadgeEnum::FoundCetguelisTreasure => 'And %pet.name% didn\'t just get a treasure, they got the Treasure Hunter badge!',
        PetBadgeEnum::HadABaby => 'And guess what: a pet that has a baby has a Baby-maker badge!',
        PetBadgeEnum::ClimbedToTopOfBeanstalk => 'Such climb! Very high! %pet.name% gets the Castle in the Clouds badge!',
        PetBadgeEnum::SungWithWhales => 'Beautiful! %pet.name% received the Songs of the Deep badge.',
        PetBadgeEnum::ProducedASkillScroll => '(Dang! That\'s a hard one! %pet.name% FOR SURE gets the Skill Scribe badge!)',
        PetBadgeEnum::PulledAnItemFromADream => 'Pulling an item out of a hat is one thing, but pulling one out of a dream?! %pet.name% DEFINITELY gets the Oneirokinetic badge for that!',
        PetBadgeEnum::Go => 'Let\'s goooooooo! (%pet.name% gets the Go badge!)',
        PetBadgeEnum::VisitedTheBurntForest => '%pet.name% received the The Burnt Forest badge for visiting this oft-forgotten corner of the Umbra.',
        PetBadgeEnum::JumpedRopeWithABug => 'Adorable! Jump rope with a bug! %pet.name% received the Grasshopper badge.',
        PetBadgeEnum::WrangledWithInfinities => 'When it comes to infinities, you win some, and you lose some. In this case, %pet.name% definitely wins a badge, at any rate: the Infinity Wrangler badge!',
        PetBadgeEnum::ExploredAnIcyMoon => 'One small step for a pet... \'s badge collection - %pet.name% got the Icy Moon Explorer badge!',
        PetBadgeEnum::MetAFamousVideoGameCharacter => '(That name sounds familiar... someone famous, maybe?) %pet.name% received the Internet Famous badge.',
        PetBadgeEnum::MintedMoneys => 'When life gives you lemons, just mint your own money! %pet.name% gets the Mint (the Moneys Kind) badge.',
        PetBadgeEnum::ReturnedAShirikodama => 'There isn\'t a shirikodama postal service to give %pet.name% the Shirikodama Courier badge, but they get it anyway. Somehow. Don\'t worry about it.',
        PetBadgeEnum::DefeatedAWerecreatureWithSilver => 'For super-effective use of silver against a werecreature, %pet.name% received the Type-advantaged badge.',
        PetBadgeEnum::PoopedShedOrBathed => 'Life - so messy! %pet.name% got the Definitely Alive badge.',
        PetBadgeEnum::FishedAtTheIsleOfRetreatingTeeth => 'Fascinating place! Fascinating enough to warrant the Fish Teeth badge!',
        PetBadgeEnum::ExtractQuintFromCookies => 'This is one of the very few times a pet might be said to cook food, and deserving of the Quintessential Cooking badge!',
        PetBadgeEnum::StruggledWithA3DPrinter => 'So frustrating! Perhaps receiving the More Dimensions, More Problems badge will make %pet.name% feel a little better?',
        PetBadgeEnum::EmptiedTheirLunchbox => '(I guess there could be a badge for that? Sure, why not: %pet.name% got the Om-nom-nom badge!)',
        PetBadgeEnum::CraftedWithAFullHouse => 'Hopefully that cleared up some space? For their efforts, %pet.name% got the Under Pressure badge.',
        PetBadgeEnum::ClimbedTheTowerOfTrials => 'And getting to the very tippy top of the Tower of Trials absolutely warrants an award of the Tower-toppler badge! Let it be so!',
        PetBadgeEnum::FoundAChocolateFeatherBonnet => 'For finding a Chocolate Feather Bonnet in a Chocolate Mansion by using a Chocolate Key, %pet.name% gets the Chocolate Riddles badge. ... ... Chocolate.',
        PetBadgeEnum::VisitedTheFructalPlane => 'Very few non-fruit-based lifeforms see the Fructal Plane - %pet.name% should count themselves... lucky? Well, sticky, at least. They should also count themselves as having received the Fructal Plane badge, because they totally did!',
        PetBadgeEnum::Hoopmaster => 'Nothing but net! %pet.name% is now officially a Hoopmaster!',
        PetBadgeEnum::TasteTheRainbow => '%pet.name% tasted a Rainbow while skittling, and got the badge to prove it!',
        PetBadgeEnum::Musashi => 'The way of the oar-sword is the way of victory! %pet.name% has mastered the art and earned the Musashi badge!',

        PetBadgeEnum::WasAnAccountant => 'Accounting is FUN! :D (Incidentally, that\'s also the name of the badge %pet.name% just earned!)',
        PetBadgeEnum::WasAChimneySweep => '🎵 With gnomes and with smoke all billered and curled, your pet got a badge: Chimney Sweep World! 🎵',
        PetBadgeEnum::GreenhouseFisher => 'Dang greenhouse fish - makin\' a mess! Why, it\'s enough to make someone give a pet the Greenhouse Fishin\' badge, it is!',
        PetBadgeEnum::BeeNana => 'Nana\'d a Naner with the bees?? %pet.name% is given the Bee Naner badge.',

        PetBadgeEnum::FoundMetatronsFire => 'A legendary accomplishment, to be sure! %pet.name% receives the Radiant badge.',
        PetBadgeEnum::FoundVesicaHydrargyrum => 'A legendary accomplishment, to be sure! %pet.name% receives the Mercurial badge.',
        PetBadgeEnum::FoundEarthsEgg => 'A legendary accomplishment, to be sure! %pet.name% receives the Most Egg badge.',
        PetBadgeEnum::FoundMerkabaOfAir => 'A legendary accomplishment, to be sure! %pet.name% receives the Zephyrous badge.',

        PetBadgeEnum::DefeatedNoetalasWing => '%pet.name% has definitely gained the attention of Noetala... and gained the She Watches badge! (Pros and cons?)',
        PetBadgeEnum::DefeatedCrystallineEntity => 'An enterprising pet such as %pet.name% deserves every thread of this Data & Lore badge!',
        PetBadgeEnum::DefeatedBivusRelease => 'It\'s not every day you witness an angel create a supernova... or defeat said supernova! %pet.name% received the Supernova Survivor badge.',

        PetBadgeEnum::Level20 => 'Level 20! %pet.name% is now a level 20 pet! %pet.name% gets the Level 20 badge!',
        PetBadgeEnum::Level40 => 'Level 40! %pet.name% is now a level 40 pet! %pet.name% gets the Level 40 baadge!! :)',
        PetBadgeEnum::Level60 => 'Level 60! %pet.name% is now a level 60 pet! %pet.name% gets the Level 60 baaadge!!! :P',
        PetBadgeEnum::Level80 => 'Level 80! %pet.name% is now a level 80 pet! %pet.name% gets the Level 80 baaaadge!!!! :D',
        PetBadgeEnum::Level100 => 'Level 100! %pet.name% is now a level 100 pet! %pet.name% gets the Level 100 baaaaadge!!!1! :O',

        PetBadgeEnum::FoundAPlasticEgg => 'Those things aren\'t edible. And speaking of, neither is the Incredible, Inedible Egg badge %pet.name% just earned!',
        PetBadgeEnum::FoundOneCloverLeaf => 'Just one leaf? Eh, still: %pet.name% should probably get a badge for that. But only be a 1-leaf 4-leaf Clover badge!',
        PetBadgeEnum::DefeatedATurkeyKing => 'Down with the monarchy! %pet.name% receives the Turkicide badge for their heroism!',
        PetBadgeEnum::Wuvwy => 'Such handiwork is definitely deserving of the Wuwvy badge!',
        PetBadgeEnum::WasGivenACostumeName => '%pet.name% received the Dressed for the Occasion badge.',
    ];
}