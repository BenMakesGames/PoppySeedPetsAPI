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

namespace App\Enum;

final class PetBadgeEnum
{
    use Enum;

    // affection!
    public const string REVEALED_FAVORITE_FLAVOR = 'revealedFavoriteFlavor';
    public const string COMPLETED_HEART_DIMENSION = 'completedHeartDimension';
    public const string TRIED_ON_A_NEW_STYLE = 'triedOnANewStyle';
    public const string HAD_A_FOOD_CRAVING_SATISFIED = 'hadAFoodCravingSatisfied';

    // park events
    public const string FIRST_PLACE_CHESS = 'chessWinner';
    public const string FIRST_PLACE_JOUSTING = 'joustingWinner';
    public const string FIRST_PLACE_KIN_BALL = 'kinBallWinner';

    // misc activities
    public const string CREATED_SENTIENT_BEETLE = 'createdSentientBeetle';
    public const string MET_THE_FLUFFMONGER = 'fluffmonger';
    public const string OUTSMARTED_A_THIEVING_MAGPIE = 'outsmartedAThievingMagpie';
    public const string DECEIVED_A_VAMPIRE = 'deceivedAVampire';
    public const string FOUND_CETGUELIS_TREASURE = 'foundCetguelisTreasure';
    public const string HAD_A_BABY = 'parent';
    public const string CLIMB_TO_TOP_OF_BEANSTALK = 'summitBeanstalk';
    public const string SING_WITH_WHALES = 'singWithWhales';
    public const string PRODUCED_A_SKILL_SCROLL = 'producedASkillScroll';
    public const string PULLED_AN_ITEM_FROM_A_DREAM = 'pulledAnItemFromADream';
    public const string GO = 'go';
    public const string VISITED_THE_BURNT_FOREST = 'visitedTheBurntForest';
    public const string JUMPED_ROPE_WITH_A_BUG = 'jumpedRopeWithABug';
    public const string WRANGLED_WITH_INFINITIES = 'wrangledWithInfinities';
    public const string EXPLORED_AN_ICY_MOON = 'exploredAnIcyMoon';
    public const string MET_A_FAMOUS_VIDEO_GAME_CHARACTER = 'metAFamousVideoGameCharacter';
    public const string MINTED_MONEYS = 'mintedMoneys';
    public const string RETURNED_A_SHIRIKODAMA = 'returnedAShirikodama';
    public const string DEFEATED_A_WERECREATURE_WITH_SILVER = 'defeatedAWerecreatureWithSilver';
    public const string POOPED_SHED_OR_BATHED = 'poopedShedOrBathed';
    public const string FISHED_AT_THE_ISLE_OF_RETREATING_TEETH = 'fishedAtTheIsleOfRetreatingTeeth';
    public const string EXTRACT_QUINT_FROM_COOKIES = 'extractQuintFromCookies';
    public const string STRUGGLED_WITH_A_3D_PRINTER = 'struggledWithA3DPrinter';
    public const string EMPTIED_THEIR_LUNCHBOX = 'emptiedTheirLunchbox';
    public const string CRAFTED_WITH_A_FULL_HOUSE = 'craftedWithAFullHouse';
    public const string CLIMBED_THE_TOWER_OF_TRIALS = 'climbedTheTowerOfTrials';
    public const string FOUND_A_CHOCOLATE_FEATHER_BONNET = 'foundAChocolateFeatherBonnet';

    // add-on helper activities
    public const string WAS_AN_ACCOUNTANT = 'increasedADragonsHoard';
    public const string WAS_A_CHIMNEY_SWEEP = 'sweptTheChimneyOfGnomes';
    public const string GREENHOUSE_FISHER = 'fishedInTheGreenhouse';
    public const string BEE_NANA = 'beeNana';

    // the quest for the philosopher's stone
    public const string FOUND_METATRONS_FIRE = 'foundMetatronsFire';
    public const string FOUND_VESICA_HYDRARGYRUM = 'foundVesicaHydrargyrum';
    public const string FOUND_EARTHS_EGG = 'foundEarthsEgg';
    public const string FOUND_MERKABA_OF_AIR = 'foundMerkabaOfAir';

    // fighting monsters at home
    public const string DEFEATED_NOETALAS_WING = 'defeatedNoetalasWing';
    public const string DEFEATED_CRYSTALLINE_ENTITY = 'defeatedCrystallineEntity';
    public const string DEFEATED_BIVUS_RELEASE = 'defeatedBivusRelease';

    // levels
    public const string LEVEL_20 = 'level20';
    public const string LEVEL_40 = 'level40';
    public const string LEVEL_60 = 'level60';
    public const string LEVEL_80 = 'level80';
    public const string LEVEL_100 = 'level100';

    // holiday events
    public const string FOUND_A_PLASTIC_EGG = 'foundAPlasticEgg';
    public const string FOUND_ONE_CLOVER_LEAF = 'foundOneCloverLeaf';
    public const string DEFEATED_A_TURKEY_KING = 'defeatedATurkeyKing';
    public const string WUVWY = 'wuvwy';
    public const string WAS_GIVEN_A_COSTUME_NAME = 'wasGivenACostumeName';
}
