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

final class UserStatEnum
{
    use FakeEnum;

    public const string TOTAL_MONEYS_SPENT = 'Total Moneys Spent';
    public const string TOTAL_MONEYS_EARNED_IN_MARKET = 'Total Moneys Earned in Market';
    public const string ITEMS_SOLD_IN_MARKET = 'Items Sold in Market';
    public const string ITEMS_BOUGHT_IN_MARKET = 'Items Bought in Market';
    public const string COOKED_SOMETHING = 'Cooked Something';
    public const string ITEMS_RECYCLED = 'Items Recycled';
    public const string ITEMS_DONATED_TO_MUSEUM = 'Items Donated to Museum';
    public const string FOOD_HOURS_FED_TO_PETS = 'Food Hours Fed to Pets';
    public const string PETTED_A_PET = 'Petted a Pet';
    public const string MONEYS_STOLEN_BY_THIEVING_MAGPIES = 'Moneys Stolen by Thieving Magpies';
    public const string RECIPES_LEARNED_BY_COOKING_BUDDY = 'Recipes Learned by Cooking Buddy';
    public const string BUGS_SQUISHED = 'Bugs Squished';
    public const string BUGS_PUT_OUTSIDE = 'Bugs Put Outside';
    public const string BUGS_FED = 'Bugs Fed';
    public const string FED_A_LINE_OF_ANTS = 'Fed a Line of Ants';
    public const string EVOLVED_A_CENTIPEDE = 'Evolved a Centipede';
    public const string FED_THE_BEEHIVE = 'Fed the Beehive';
    public const string FERTILIZED_PLANT = 'Fertilized a Plant';
    public const string HARVESTED_PLANT = 'Harvested a Plant';
    public const string PETS_ADOPTED = 'Pets Adopted';
    public const string MAGIC_HOURGLASSES_SMASHED = 'Magic Hourglasses Smashed';
    public const string READ_A_SCROLL = 'Read a Scroll';
    public const string PETS_BIRTHED = 'Babies Born in Your House';
    public const string BURNT_LOGS_BROKEN = 'Broke Apart a Burnt Log';
    public const string TRADED_WITH_THE_FLUFFMONGER = 'Fluff Traded to the Fluffmonger';
    public const string EGGS_HARVESTED_FROM_EGGPLANTS = 'Eggs Harvested from Eggplants';
    public const string ROTTEN_EGGPLANTS = 'Eggplants Which Were Horrible and Rotten';
    public const string ITEMS_COMPOSTED = 'Items Composted';
    public const string LARGE_BIRDS_APPROACHED = 'Large Birds Approached in Greenhouse';
    public const string TREASURES_GIVEN_TO_DRAGON_HOARD = 'Treasures Given to Dragon';
    public const string CANS_OF_FOOD_OPENED = 'Cans of Food Opened';
    public const string LOOTED_A_POT_OF_GOLD = 'Looted a Pot of Gold';
    public const string STRANGE_FIELDS_COLLAPSED = 'Strange Fields Collapsed';
    public const string COMPLETED_A_SAGA_SAGA = 'Completed a Sága Saga';
    public const string TOSSED_A_HOT_POTATO = 'Tossed a Hot Potato';
    public const string ACHIEVEMENTS_CLAIMED = 'Achievements Claimed';
    public const string ITEMS_THROWN_INTO_THE_FIREPLACE = 'Items Thrown into the Fireplace';
    public const string PLAZA_BOXES_RECEIVED = 'Boxes (and Bags) Received at the Plaza';
    public const string HOLLOW_EARTH_SPACES_MOVED = 'Spaces Moved in the Hollow Earth';
    public const string TOOLS_DIPPED_IN_A_DRAGON_VASE = 'Tools Dipped in a Dragon Vase';
    public const string FOODS_DIPPED_IN_A_HOT_POT = 'Foods Dipped in a Hot Pot';
    public const string ROLLED_SATYR_DICE = 'Rolled Satyr Dice';
    public const string SHATTERED_ICE_MANGO = 'Shattered an Ice "Mango"';

    public const string RECEIVED_A_MINOR_PRIZE_FROM_A_GREAT_SPIRIT = 'Received a Minor Reward from a Great Spirit';
    public const string RECEIVED_A_MODERATE_PRIZE_FROM_A_GREAT_SPIRIT = 'Received a Moderate Reward from a Great Spirit';
    public const string RECEIVED_A_MAJOR_PRIZE_FROM_A_GREAT_SPIRIT = 'Received a Major Reward from a Great Spirit';

    public const string RECEIVED_A_MINOR_PRIZE_FROM_A_HUNTER_OF_ANHUR = 'Received a Minor Reward from a Hunter of Anhur';
    public const string RECEIVED_A_MODERATE_PRIZE_FROM_A_HUNTER_OF_ANHUR = 'Received a Moderate Reward from a Hunter of Anhur';
    public const string RECEIVED_A_MAJOR_PRIZE_FROM_A_HUNTER_OF_ANHUR = 'Received a Major Reward from a Hunter of Anhur';

    public const string RECEIVED_A_MINOR_PRIZE_FROM_SOME_BOSHINOGAMI = 'Received a Minor Reward from some Boshinogami';
    public const string RECEIVED_A_MODERATE_PRIZE_FROM_SOME_BOSHINOGAMI = 'Received a Moderate Reward from some Boshinogami';
    public const string RECEIVED_A_MAJOR_PRIZE_FROM_SOME_BOSHINOGAMI = 'Received a Major Reward from some Boshinogami';

    public const string RECEIVED_A_MINOR_PRIZE_FROM_CARDEAS_LOCKBEARER = 'Received a Minor Reward from Cardea\'s Lockbearer';
    public const string RECEIVED_A_MODERATE_PRIZE_FROM_CARDEAS_LOCKBEARER = 'Received a Moderate Reward from Cardea\'s Lockbearer';
    public const string RECEIVED_A_MAJOR_PRIZE_FROM_CARDEAS_LOCKBEARER = 'Received a Major Reward from Cardea\'s Lockbearer';

    public const string RECEIVED_A_MINOR_PRIZE_FROM_DIONYSUSS_HUNGER = 'Received a Minor Reward from Dionysus\'s Hunger';
    public const string RECEIVED_A_MODERATE_PRIZE_FROM_DIONYSUSS_HUNGER = 'Received a Moderate Reward from Dionysus\'s Hunger';
    public const string RECEIVED_A_MAJOR_PRIZE_FROM_DIONYSUSS_HUNGER = 'Received a Major Reward from Dionysus\'s Hunger';

    public const string RECEIVED_A_MINOR_PRIZE_FROM_HUEHUECOYOTLS_FOLLY = 'Received a Minor Reward from Huehuecoyotl\'s Folly';
    public const string RECEIVED_A_MODERATE_PRIZE_FROM_HUEHUECOYOTLS_FOLLY = 'Received a Moderate Reward from Huehuecoyotl\'s Folly';
    public const string RECEIVED_A_MAJOR_PRIZE_FROM_HUEHUECOYOTLS_FOLLY = 'Received a Major Reward from Huehuecoyotl\'s Folly';

    public const string RECEIVED_A_MINOR_PRIZE_FROM_AN_EIRI_PERSONA = 'Received a Minor Reward from an Eiri Persona';
    public const string RECEIVED_A_MODERATE_PRIZE_FROM_AN_EIRI_PERSONA = 'Received a Moderate Reward from an Eiri Persona';
    public const string RECEIVED_A_MAJOR_PRIZE_FROM_AN_EIRI_PERSONA = 'Received a Major Reward from an Eiri Persona';
}
