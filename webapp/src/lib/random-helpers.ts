/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
/**
 * @see {@link https://gist.github.com/BenMakesGames/58c5307108b74b4995c60a7b056ab04f}
 * @file These methods make no attempt to be SECURE, in terms of the sorts of concerns that cryptography might be
 * worried about. They're intended to be used to quickly make FUN things, like procedurally-generated content for a
 * game. If you care about generating passwords or other secure keys, do not use these methods.
 * @author Ben Hendel-Doying
 */

/**
 * Returns a random integer between min and max, inclusive. (The way a human might "naturally" expect.)
 *
 * @example let dieRoll = Math.randomNatural(1, 6); // gets the roll of a six-sided die
 *
 * @returns {int}
 */
Math.randomNatural = function(min: number, max: number): number
{
    return Math.floor(Math.random() * (max - min + 1)) + min;
};

/**
 * Returns a random integer from 0 to max (excluding max). Useful for generating array indices, and other
 * more-computer-y things.
 *
 * @example let randomIndex = Math.randomFrom0(10); // gets an integer from 0 to 9
 *
 * @returns {int}
 */
Math.randomFrom0 = function(max: number): number
{
    return Math.floor(Math.random() * max);
};

/**
 * Returns a random alphanumeric string of the given length. Useful for generating quick "unique" IDs; NOT for
 * generating passwords or other things where security is a priority.
 *
 * @example let saveGameId = Math.randomString(20); // 62^20 = a shit ton; chance of collision is "quite low"
 *
 * @returns {string}
 */
Math.randomString = function(length: number): string
{
    let characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let text = '';

    for(let i = 0; i < length; i++)
        text += Math.randomCharacter(characters);

    return text;
};

/**
 * Returns a random array index from the array.
 *
 * @example return [ 'mangoes', 'watermelons', 'brussel sprouts' ].randomIndex(); // returns 0, 1, or 2
 *
 * @returns {int}
 */
Math.randomIndexFromList = function(list: any[]): number
{
    return Math.randomFrom0(list.length);
};

/**
 * Returns a random element from the array.
 *
 * @example let animal = [ 'fox', 'cat', 'goblin' ].randomElement(); // gets "fox", "cat", or "goblin"
 *
 * @returns {*}
 */
Math.randomFromList = function(list: any[]): any
{
    return list[Math.randomIndexFromList(list)];
};

Math.randomCharacter = function(text: string): string
{
  return text[Math.randomFrom0(text.length)];
};

interface Math
{
  randomNatural(min: number, max: number): number;
  randomFrom0(max: number): number;
  randomString(length: number): string;
  randomIndexFromList(list: any[]): number;
  randomFromList(list: any[]): any;
  randomCharacter(text: string): string;
}