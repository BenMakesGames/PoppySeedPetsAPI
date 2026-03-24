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

enum StatusEffectEnum: string
{
    case BittenByAVampire = 'Bitten (by a Vampire)';
    case BittenByAWerecreature = 'Bitten (by a Werecreature)';
    case Caffeinated = 'Caffeinated';
    case Tired = 'Tired';
    case Inspired = 'Inspired';
    case Oneiric = 'Oneiric';
    case ExtraExtroverted = 'Extra Extroverted';
    case EggplantCursed = 'Eggplant-cursed';
    case GobbleGobble = '*gobble gobble*';
    case SilkInfused = 'Silk-infused';
    case HexHexed = 'Hex Hexed';
    case Invisible = 'Invisible';
    case AntiGravd = 'Anti-grav\'d';
    case OilCovered = 'Oil-covered';
    case BubbleGumd = 'Bubblegum\'d';
    case LapineWhispers = 'Attuned to Lapine Whispers';
    case Wereform = 'Wereform';
    case Vivacious = 'Vivacious';
    case FruitClobbering = 'Fruit-clobbering';
    case HotToTheTouch = 'Hot to the Touch';
    case Hoppin = 'Hoppin\'';
    case OutOfThisWorld = 'Out of this World';
    case FocusedArcana = 'Focused (Arcana)';
    case FocusedBrawl = 'Focused (Brawl)';
    case FocusedNature = 'Focused (Nature)';
    case FocusedCrafts = 'Focused (Crafts)';
    case FocusedScience = 'Focused (Science)';
    case FocusedMusic = 'Focused (Music)';
    case FocusedStealth = 'Focused (Stealth)';
    case GlitterBombed = 'Glitter-bombed';
    case Rawr = 'Rawr!';
    case Moonstruck = 'Moonstruck';
    case Cordial = 'Cordial';
    case DancingLikeAFool = 'Dancing Like a Fool';
    case Thirsty = 'Thirsty';
    case HeatResistant = 'Heat-resistant';
    case Jaune = 'Jaune';
    case Spiced = 'Spiced';
    case XRayd = 'X-ray\'d';
    case CacheEmpty = 'Cache-empty';

    case FatedDeliciously = 'Fated (Deliciously)';
    case FatedSoakedly = 'Fated (Soakedly)';
    case FatedElectrically = 'Fated (Electrically)';
    case FatedFerally = 'Fated (Ferally)';
    case FatedLunarly = 'Fated (Lunarly)';
    case FatedCheesewardly = 'Fated (Cheesewardly)';

    case DaydreamingIceCream = 'Daydreaming (Ice Cream)';
    case DaydreamingPizza = 'Daydreaming (Pizza)';
    case DaydreamingFoodFight = 'Daydreaming (Food Fight)';
    case DaydreamingNoodles = 'Daydreaming (Noodles)';
}
