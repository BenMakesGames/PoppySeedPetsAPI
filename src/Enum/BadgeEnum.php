<?php

namespace App\Enum;

final class BadgeEnum
{
    use Enum;

    public const RECYCLED_10 = 'Recycled10';
    public const RECYCLED_100 = 'Recycled100';
    public const RECYCLED_1000 = 'Recycled1000';
    public const RECYCLED_10000 = 'Recycled10000';
    public const BAABBLES_OPENED_1 = 'BaabblesOpened1';
    public const BAABBLES_OPENED_10 = 'BaabblesOpened10';
    public const BAABBLES_OPENED_100 = 'BaabblesOpened100';
    public const BAABBLES_OPENED_1000 = 'BaabblesOpened1000';
    public const MONEYS_SPENT_10 = 'MoneysSpent10';
    public const MONEYS_SPENT_100 = 'MoneysSpent100';
    public const MONEYS_SPENT_1000 = 'MoneysSpent1000';
    public const MONEYS_SPENT_10000 = 'MoneysSpent10000';
    public const MONEYS_SPENT_100000 = 'MoneysSpent100000';

    public const PETTED_10 = 'Petted10';
    public const PETTED_100 = 'Petted100';
    public const PETTED_1000 = 'Petted1000';
    public const PETTED_10000 = 'Petted10000';

    public const MAX_PETS_4 = 'MaxPets4';
    public const COMPLETE_THE_HEARTSTONE_DIMENSION = 'HeartstoneDimensionCompleted';

    public const TROPHIES_EARNED_1 = 'TrophiesEarned1';
    public const TROPHIES_EARNED_10 = 'TrophiesEarned10';
    public const TROPHIES_EARNED_100 = 'TrophiesEarned100';

    public const OPENED_CEREAL_BOX = 'OpenedCerealBox';
    public const OPENED_CAN_OF_FOOD_10 = 'OpenedCanOfFood10';
    public const OPENED_CAN_OF_FOOD_100 = 'OpenedCanOfFood100';
    public const OPENED_PAPER_BAG_10 = 'OpenedPaperBag10';
    public const OPENED_PAPER_BAG_100 = 'OpenedPaperBag100';
    public const OPENED_CAN_OF_FOOD_PAPER_BAG_100 = 'OpenedCerealBoxCanOfFoodPaperBag100';

    public const HORRIBLE_EGGPLANT_1 = 'HorribleEggplant1';
    public const HORRIBLE_EGGPLANT_10 = 'HorribleEggplant10';
    public const HOT_POTATO_TOSSED_1 = 'HotPotatoTossed1';
    public const HOT_POTATO_TOSSED_10 = 'HotPotatoTossed10';
    public const HOT_POTATO_TOSSED_100 = 'HotPotatoTossed100';

    public const FERTILIZED_PLANT_10 = 'FertilizedPlant10';
    public const FERTILIZED_PLANT_100 = 'FertilizedPlant100';
    public const FERTILIZED_PLANT_1000 = 'FertilizedPlant1000';
    public const HARVESTED_PLANT_10 = 'HarvestedPlant10';
    public const HARVESTED_PLANT_100 = 'HarvestedPlant100';
    public const HARVESTED_PLANT_1000 = 'HarvestedPlant1000';
    public const COMPOSTED_10 = 'Composted10';
    public const COMPOSTED_100 = 'Composted100';
    public const COMPOSTED_1000 = 'Composted1000';
    public const FERTILIZED_HARVESTED_COMPOSTED_1000 = 'FertilizedHarvestedComposted1000';

    public const TREASURES_GIVEN_TO_DRAGON_10 = 'TreasuresGivenToDragon10';
    public const TREASURES_GIVEN_TO_DRAGON_100 = 'TreasuresGivenToDragon100';
    public const TREASURES_GIVEN_TO_DRAGON_1000 = 'TreasuresGivenToDragon1000';

    public const COOKED_10 = 'Cooked10';
    public const COOKED_100 = 'Cooked100';
    public const COOKED_1000 = 'Cooked1000';
    public const COOKED_10000 = 'Cooked10000';

    public const DRAGON_VASE_DIPPING_10 = 'DragonVaseDipping10';
    public const HOT_POT_DIPPING_10 = 'HotPotDipping10';

    public const DEFEATED_SUMMONED_MONSTER_1 = 'DefeatedSummonedMonster1';
    public const DEFEATED_SUMMONED_MONSTER_10 = 'DefeatedSummonedMonster10';
    public const DEFEATED_SUMMONED_MONSTER_100 = 'DefeatedSummonedMonster100';

    public const ASCENDED_TOWER_OF_TRIALS_1 = 'TowerOfTrials1';
    public const ASCENDED_TOWER_OF_TRIALS_10 = 'TowerOfTrials10';
    public const ASCENDED_TOWER_OF_TRIALS_100 = 'TowerOfTrials100';

    public const HOLLOW_EARTH_TRAVEL_10 = 'HollowEarthTravel10';
    public const HOLLOW_EARTH_TRAVEL_100 = 'HollowEarthTravel100';
    public const HOLLOW_EARTH_TRAVEL_1000 = 'HollowEarthTravel1000';

    public const MISREAD_SCROLL = 'MisreadScroll1';
    public const READ_SCROLL_1 = 'ReadScroll1';
    public const READ_SCROLL_10 = 'ReadScroll10';
    public const READ_SCROLL_100 = 'ReadScroll100';

    public const WHISPER_STONE = 'WhisperStone';

    public const OPENED_HAT_BOX_1 = 'OpenedHatBox1';
    public const OPENED_HAT_BOX_10 = 'OpenedHatBox10';
    public const HATTIER_STYLES_10 = 'UnlockedHattierStyles10';
    public const HATTIER_STYLES_20 = 'UnlockedHattierStyles20';
    public const HATTIER_STYLES_30 = 'UnlockedHattierStyles30';

    public const OPENED_BOX_BOX_1 = 'OpenedBoxBox1';
    public const OPENED_BOX_BOX_10 = 'OpenedBoxBox10';
    public const BOX_BOX_BOX_BOX = 'BoxBoxBoxBox'; // found a box box inside a box box
    public const PLAZA_BOX_1 = 'PlazaBox1';
    public const PLAZA_BOX_10 = 'PlazaBox10';
    public const PLAZA_BOX_100 = 'PlazaBox100';

    public const FEED_THE_ANTS_10 = 'FedAnts10';
    public const FEED_THE_BEES_10 = 'FedBees10';
    public const FEED_THE_CENTIPEDES_10 = 'FedCentipedes10';
    public const PLAYING_BOTH_SIDES = 'FedAntsAndBees10';

    public const LONGEST_FIRE_1_HOUR = 'LongestFire60';
    public const LONGEST_FIRE_1_DAY = 'LongestFire1440';
    public const LONGEST_FIRE_1_WEEK = 'LongestFire10080';
    public const FIREPLACE_FUEL_10 = 'FireplaceFuel10';
    public const FIREPLACE_FUEL_100 = 'FireplaceFuel100';
    public const FIREPLACE_FUEL_1000 = 'FireplaceFuel1000';
    public const FIREPLACE_FUEL_10000 = 'FireplaceFuel10000';

    public const TEACH_COOKING_BUDDY_100 = 'CookingBuddyRecipes100';
    public const TEACH_COOKING_BUDDY_200 = 'CookingBuddyRecipes200';
    public const TEACH_COOKING_BUDDY_300 = 'CookingBuddyRecipes300';
    public const TEACH_COOKING_BUDDY_400 = 'CookingBuddyRecipes400';
    public const TEACH_COOKING_BUDDY_500 = 'CookingBuddyRecipes500';
    public const TEACH_COOKING_BUDDY_600 = 'CookingBuddyRecipes600';

    public const ACHIEVEMENTS_10 = 'Achievements10';
    public const ACHIEVEMENTS_20 = 'Achievements20';
    public const ACHIEVEMENTS_30 = 'Achievements30';
    public const ACHIEVEMENTS_40 = 'Achievements40';
    public const ACHIEVEMENTS_50 = 'Achievements50';
    public const ACHIEVEMENTS_60 = 'Achievements60';
    public const ACHIEVEMENTS_70 = 'Achievements70';
    public const ACHIEVEMENTS_80 = 'Achievements80';
    public const ACHIEVEMENTS_90 = 'Achievements90';
    public const ACHIEVEMENTS_100 = 'Achievements100';
}