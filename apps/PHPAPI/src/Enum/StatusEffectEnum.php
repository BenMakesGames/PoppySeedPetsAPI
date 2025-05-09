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
    use FakeEnum;

    public const string BITTEN_BY_A_VAMPIRE = 'Bitten (by a Vampire)';
    public const string BITTEN_BY_A_WERECREATURE = 'Bitten (by a Werecreature)';
    public const string CAFFEINATED = 'Caffeinated';
    public const string TIRED = 'Tired';
    public const string INSPIRED = 'Inspired';
    public const string ONEIRIC = 'Oneiric';
    public const string EXTRA_EXTROVERTED = 'Extra Extroverted';
    public const string EGGPLANT_CURSED = 'Eggplant-cursed';
    public const string GOBBLE_GOBBLE = '*gobble gobble*';
    public const string SILK_INFUSED = 'Silk-infused';
    public const string HEX_HEXED = 'Hex Hexed';
    public const string INVISIBLE = 'Invisible';
    public const string ANTI_GRAVD = 'Anti-grav\'d';
    public const string OIL_COVERED = 'Oil-covered';
    public const string BUBBLEGUMD = 'Bubblegum\'d';
    public const string LAPINE_WHISPERS = 'Attuned to Lapine Whispers';
    public const string WEREFORM = 'Wereform';
    public const string VIVACIOUS = 'Vivacious';
    public const string FRUIT_CLOBBERING = 'Fruit-clobbering';
    public const string HOT_TO_THE_TOUCH = 'Hot to the Touch';
    public const string HOPPIN = 'Hoppin\'';
    public const string OUT_OF_THIS_WORLD = 'Out of this World';
    public const string FOCUSED_ARCANA = 'Focused (Arcana)';
    public const string FOCUSED_BRAWL = 'Focused (Brawl)';
    public const string FOCUSED_NATURE = 'Focused (Nature)';
    public const string FOCUSED_CRAFTS = 'Focused (Crafts)';
    public const string FOCUSED_SCIENCE = 'Focused (Science)';
    public const string FOCUSED_MUSIC = 'Focused (Music)';
    public const string FOCUSED_STEALTH = 'Focused (Stealth)';
    public const string GLITTER_BOMBED = 'Glitter-bombed';
    public const string RAWR = 'Rawr!';
    public const string MOONSTRUCK = 'Moonstruck';
    public const string CORDIAL = 'Cordial';
    public const string DANCING_LIKE_A_FOOL = 'Dancing Like a Fool';
    public const string THIRSTY = 'Thirsty';
    public const string HEAT_RESISTANT = 'Heat-resistant';
    public const string JAUNE = 'Jaune';
    public const string SPICED = 'Spiced';
    public const string X_RAYD = 'X-ray\'d';
    public const string CACHE_EMPTY = 'Cache-empty';

    public const string FATED_DELICIOUSNESS = 'Fated (Deliciously)';
    public const string FATED_SOAKEDLY = 'Fated (Soakedly)';
    public const string FATED_ELECTRICALLY = 'Fated (Electrically)';
    public const string FATED_FERALLY = 'Fated (Ferally)';
    public const string FATED_LUNARLY = 'Fated (Lunarly)';
    public const string FATED_CHEESEWARDLY = 'Fated (Cheesewardly)';

    public const string DAYDREAM_ICE_CREAM = 'Daydreaming (Ice Cream)';
    public const string DAYDREAM_PIZZA = 'Daydreaming (Pizza)';
    public const string DAYDREAM_FOOD_FIGHT = 'Daydreaming (Food Fight)';
    public const string DAYDREAM_NOODLES = 'Daydreaming (Noodles)';
}
