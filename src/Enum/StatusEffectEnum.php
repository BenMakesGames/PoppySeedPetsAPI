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

    public const string BittenByAVampire = 'Bitten (by a Vampire)';
    public const string BittenByAWerecreature = 'Bitten (by a Werecreature)';
    public const string Caffeinated = 'Caffeinated';
    public const string Tired = 'Tired';
    public const string Inspired = 'Inspired';
    public const string Oneiric = 'Oneiric';
    public const string ExtraExtroverted = 'Extra Extroverted';
    public const string EggplantCursed = 'Eggplant-cursed';
    public const string GobbleGobble = '*gobble gobble*';
    public const string SilkInfused = 'Silk-infused';
    public const string HexHexed = 'Hex Hexed';
    public const string Invisible = 'Invisible';
    public const string AntiGravd = 'Anti-grav\'d';
    public const string OilCovered = 'Oil-covered';
    public const string BubbleGumd = 'Bubblegum\'d';
    public const string LapineWhispers = 'Attuned to Lapine Whispers';
    public const string Wereform = 'Wereform';
    public const string Vivacious = 'Vivacious';
    public const string FruitClobbering = 'Fruit-clobbering';
    public const string HotToTheTouch = 'Hot to the Touch';
    public const string Hoppin = 'Hoppin\'';
    public const string OutOfThisWorld = 'Out of this World';
    public const string FocusedArcana = 'Focused (Arcana)';
    public const string FocusedBrawl = 'Focused (Brawl)';
    public const string FocusedNature = 'Focused (Nature)';
    public const string FocusedCrafts = 'Focused (Crafts)';
    public const string FocusedScience = 'Focused (Science)';
    public const string FocusedMusic = 'Focused (Music)';
    public const string FocusedStealth = 'Focused (Stealth)';
    public const string GlitterBombed = 'Glitter-bombed';
    public const string Rawr = 'Rawr!';
    public const string Moonstruck = 'Moonstruck';
    public const string Cordial = 'Cordial';
    public const string DancingLikeAFool = 'Dancing Like a Fool';
    public const string Thirsty = 'Thirsty';
    public const string HeatResistant = 'Heat-resistant';
    public const string Jaune = 'Jaune';
    public const string Spiced = 'Spiced';
    public const string XRayd = 'X-ray\'d';
    public const string CacheEmpty = 'Cache-empty';

    public const string FatedDeliciously = 'Fated (Deliciously)';
    public const string FatedSoakedly = 'Fated (Soakedly)';
    public const string FatedElectrically = 'Fated (Electrically)';
    public const string FatedFerally = 'Fated (Ferally)';
    public const string FatedLunarly = 'Fated (Lunarly)';
    public const string FatedCheesewardly = 'Fated (Cheesewardly)';

    public const string DaydreamingIceCream = 'Daydreaming (Ice Cream)';
    public const string DaydreamingPizza = 'Daydreaming (Pizza)';
    public const string DaydreamingFoodFight = 'Daydreaming (Food Fight)';
    public const string DaydreamingNoodles = 'Daydreaming (Noodles)';
}
