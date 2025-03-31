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

final class StatusEffectEnum
{
    use Enum;

    public const BITTEN_BY_A_VAMPIRE = 'Bitten (by a Vampire)';
    public const BITTEN_BY_A_WERECREATURE = 'Bitten (by a Werecreature)';
    public const CAFFEINATED = 'Caffeinated';
    public const TIRED = 'Tired';
    public const INSPIRED = 'Inspired';
    public const ONEIRIC = 'Oneiric';
    public const EXTRA_EXTROVERTED = 'Extra Extroverted';
    public const EGGPLANT_CURSED = 'Eggplant-cursed';
    public const GOBBLE_GOBBLE = '*gobble gobble*';
    public const SILK_INFUSED = 'Silk-infused';
    public const HEX_HEXED = 'Hex Hexed';
    public const INVISIBLE = 'Invisible';
    public const ANTI_GRAVD = 'Anti-grav\'d';
    public const OIL_COVERED = 'Oil-covered';
    public const BUBBLEGUMD = 'Bubblegum\'d';
    public const LAPINE_WHISPERS = 'Attuned to Lapine Whispers';
    public const WEREFORM = 'Wereform';
    public const VIVACIOUS = 'Vivacious';
    public const FRUIT_CLOBBERING = 'Fruit-clobbering';
    public const HOT_TO_THE_TOUCH = 'Hot to the Touch';
    public const HOPPIN = 'Hoppin\'';
    public const OUT_OF_THIS_WORLD = 'Out of this World';
    public const FOCUSED_ARCANA = 'Focused (Arcana)';
    public const FOCUSED_BRAWL = 'Focused (Brawl)';
    public const FOCUSED_NATURE = 'Focused (Nature)';
    public const FOCUSED_CRAFTS = 'Focused (Crafts)';
    public const FOCUSED_SCIENCE = 'Focused (Science)';
    public const FOCUSED_MUSIC = 'Focused (Music)';
    public const FOCUSED_STEALTH = 'Focused (Stealth)';
    public const GLITTER_BOMBED = 'Glitter-bombed';
    public const RAWR = 'Rawr!';
    public const MOONSTRUCK = 'Moonstruck';
    public const CORDIAL = 'Cordial';
    public const DANCING_LIKE_A_FOOL = 'Dancing Like a Fool';
    public const THIRSTY = 'Thirsty';
    public const HEAT_RESISTANT = 'Heat-resistant';
    public const JAUNE = 'Jaune';
    public const SPICED = 'Spiced';
    public const X_RAYD = 'X-ray\'d';
    public const CACHE_EMPTY = 'Cache-empty';

    public const FATED_DELICIOUSNESS = 'Fated (Deliciously)';
    public const FATED_SOAKEDLY = 'Fated (Soakedly)';
    public const FATED_ELECTRICALLY = 'Fated (Electrically)';
    public const FATED_FERALLY = 'Fated (Ferally)';
    public const FATED_LUNARLY = 'Fated (Lunarly)';
    public const FATED_CHEESEWARDLY = 'Fated (Cheesewardly)';

    public const DAYDREAM_ICE_CREAM = 'Daydreaming (Ice Cream)';
    public const DAYDREAM_PIZZA = 'Daydreaming (Pizza)';
    public const DAYDREAM_FOOD_FIGHT = 'Daydreaming (Food Fight)';
    public const DAYDREAM_NOODLES = 'Daydreaming (Noodles)';
}
