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
    use FakeEnum;

    // affection!
    public const string RevealedFavoriteFlavor = 'revealedFavoriteFlavor';
    public const string CompletedHeartDimension = 'completedHeartDimension';
    public const string TriedOnANewStyle = 'triedOnANewStyle';
    public const string HadAFoodCravingSatisfied = 'hadAFoodCravingSatisfied';

    // park events
    public const string FirstPlaceChess = 'chessWinner';
    public const string FirstPlaceJousting = 'joustingWinner';
    public const string FirstPlaceKinBall = 'kinBallWinner';

    // misc activities
    public const string CreatedSentientBeetle = 'createdSentientBeetle';
    public const string MetTheFluffmonger = 'fluffmonger';
    public const string OutsmartedAThievingMagpie = 'outsmartedAThievingMagpie';
    public const string DeceivedAVampire = 'deceivedAVampire';
    public const string FoundCetguelisTreasure = 'foundCetguelisTreasure';
    public const string HadABaby = 'parent';
    public const string ClimbedToTopOfBeanstalk = 'summitBeanstalk';
    public const string SungWithWhales = 'singWithWhales';
    public const string ProducedASkillScroll = 'producedASkillScroll';
    public const string PulledAnItemFromADream = 'pulledAnItemFromADream';
    public const string Go = 'go';
    public const string VisitedTheBurntForest = 'visitedTheBurntForest';
    public const string JumpedRopeWithABug = 'jumpedRopeWithABug';
    public const string WrangledWithInfinities = 'wrangledWithInfinities';
    public const string ExploredAnIcyMoon = 'exploredAnIcyMoon';
    public const string MetAFamousVideoGameCharacter = 'metAFamousVideoGameCharacter';
    public const string MintedMoneys = 'mintedMoneys';
    public const string ReturnedAShirikodama = 'returnedAShirikodama';
    public const string DefeatedAWerecreatureWithSilver = 'defeatedAWerecreatureWithSilver';
    public const string PoopedShedOrBathed = 'poopedShedOrBathed';
    public const string FishedAtTheIsleOfRetreatingTeeth = 'fishedAtTheIsleOfRetreatingTeeth';
    public const string ExtractQuintFromCookies = 'extractQuintFromCookies';
    public const string StruggledWithA3DPrinter = 'struggledWithA3DPrinter';
    public const string EmptiedTheirLunchbox = 'emptiedTheirLunchbox';
    public const string CraftedWithAFullHouse = 'craftedWithAFullHouse';
    public const string ClimbedTheTowerOfTrials = 'climbedTheTowerOfTrials';
    public const string FoundAChocolateFeatherBonnet = 'foundAChocolateFeatherBonnet';
    public const string VisitedTheFructalPlane = 'visitedTheFructalPlane';
    public const string Hoopmaster = 'hoopmaster';
    public const string TasteTheRainbow = 'tasteTheRainbow';

    // add-on helper activities
    public const string WasAnAccountant = 'increasedADragonsHoard';
    public const string WasAChimneySweep = 'sweptTheChimneyOfGnomes';
    public const string GreenhouseFisher = 'fishedInTheGreenhouse';
    public const string BeeNana = 'beeNana';

    // the quest for the philosopher's stone
    public const string FoundMetatronsFire = 'foundMetatronsFire';
    public const string FoundVesicaHydrargyrum = 'foundVesicaHydrargyrum';
    public const string FoundEarthsEgg = 'foundEarthsEgg';
    public const string FoundMerkabaOfAir = 'foundMerkabaOfAir';

    // fighting monsters at home
    public const string DefeatedNoetalasWing = 'defeatedNoetalasWing';
    public const string DefeatedCrystallineEntity = 'defeatedCrystallineEntity';
    public const string DefeatedBivusRelease = 'defeatedBivusRelease';

    // levels
    public const string Level20 = 'level20';
    public const string Level40 = 'level40';
    public const string Level60 = 'level60';
    public const string Level80 = 'level80';
    public const string Level100 = 'level100';

    // holiday events
    public const string FoundAPlasticEgg = 'foundAPlasticEgg';
    public const string FoundOneCloverLeaf = 'foundOneCloverLeaf';
    public const string DefeatedATurkeyKing = 'defeatedATurkeyKing';
    public const string Wuvwy = 'wuvwy';
    public const string WasGivenACostumeName = 'wasGivenACostumeName';
}
