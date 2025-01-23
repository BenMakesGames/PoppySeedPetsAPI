<?php

namespace App\Enum;

final class PetBadgeEnum
{
    use Enum;

    // affection!
    public const REVEALED_FAVORITE_FLAVOR = 'revealedFavoriteFlavor';
    public const COMPLETED_HEART_DIMENSION = 'completedHeartDimension';
    public const TRIED_ON_A_NEW_STYLE = 'triedOnANewStyle';

    // park events
    public const FIRST_PLACE_CHESS = 'chessWinner';
    public const FIRST_PLACE_JOUSTING = 'joustingWinner';
    public const FIRST_PLACE_KIN_BALL = 'kinBallWinner';

    // misc activities
    public const CREATED_SENTIENT_BEETLE = 'createdSentientBeetle';
    public const MET_THE_FLUFFMONGER = 'fluffmonger';
    public const OUTSMARTED_A_THIEVING_MAGPIE = 'outsmartedAThievingMagpie';
    public const DECEIVED_A_VAMPIRE = 'deceivedAVampire';
    public const FOUND_CETGUELIS_TREASURE = 'foundCetguelisTreasure';
    public const HAD_A_BABY = 'parent';
    public const CLIMB_TO_TOP_OF_BEANSTALK = 'summitBeanstalk';
    public const SING_WITH_WHALES = 'singWithWhales';
    public const PRODUCED_A_SKILL_SCROLL = 'producedASkillScroll';
    public const PULLED_AN_ITEM_FROM_A_DREAM = 'pulledAnItemFromADream';
    public const GO = 'go';
    public const VISITED_THE_BURNT_FOREST = 'visitedTheBurntForest';
    public const JUMPED_ROPE_WITH_A_BUG = 'jumpedRopeWithABug';
    public const WRANGLED_WITH_INFINITIES = 'wrangledWithInfinities';
    public const EXPLORED_AN_ICY_MOON = 'exploredAnIcyMoon';
    public const MET_A_FAMOUS_VIDEO_GAME_CHARACTER = 'metAFamousVideoGameCharacter';
    public const MINTED_MONEYS = 'mintedMoneys';
    public const RETURNED_A_SHIRIKODAMA = 'returnedAShirikodama';

    // add-on helper activities
    public const WAS_AN_ACCOUNTANT = 'increasedADragonsHoard';
    public const WAS_A_CHIMNEY_SWEEP = 'sweptTheChimneyOfGnomes';
    public const GREENHOUSE_FISHER = 'fishedInTheGreenhouse';
    public const BEE_NANA = 'beeNana';

    // the quest for the philosopher's stone
    public const FOUND_METATRONS_FIRE = 'foundMetatronsFire';
    public const FOUND_VESICA_HYDRARGYRUM = 'foundVesicaHydrargyrum';
    public const FOUND_EARTHS_EGG = 'foundEarthsEgg';
    public const FOUND_MERKABA_OF_AIR = 'foundMerkabaOfAir';

    // fighting monsters at home
    public const DEFEATED_NOETALAS_WING = 'defeatedNoetalasWing';
    public const DEFEATED_CRYSTALLINE_ENTITY = 'defeatedCrystallineEntity';
    public const DEFEATED_BIVUS_RELEASE = 'defeatedBivusRelease';

    // levels
    public const LEVEL_20 = 'level20';
    public const LEVEL_40 = 'level40';
    public const LEVEL_60 = 'level60';
    public const LEVEL_80 = 'level80';
    public const LEVEL_100 = 'level100';

    // holiday events
    public const FOUND_A_PLASTIC_EGG = 'foundAPlasticEgg';
    public const FOUND_ONE_CLOVER_LEAF = 'foundOneCloverLeaf';
    public const DEFEATED_A_TURKEY_KING = 'defeatedATurkeyKing';
    public const WUVWY = 'wuvwy';
}
