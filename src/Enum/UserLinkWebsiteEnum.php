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

class UserLinkWebsiteEnum
{
    use FakeEnum;

    public const string CHICKEN_SMOOTHIE = 'ChickenSmoothie';
    public const string DEVIANT_ART = 'DeviantArt';
    public const string FLIGHT_RISING = 'FlightRising';
    public const string FUR_AFFINITY = 'FurAffinity';
    public const string GAIA_ONLINE = 'GaiaOnline';
    public const string GITHUB = 'GitHub';
    public const string GOATLINGS = 'Goatlings';
    public const string INSTAGRAM = 'Instagram';
    public const string LORWOLF = 'Lorwolf';
    public const string MASTODON = 'Mastodon';
    public const string NINTENDO = 'Nintendo';
    public const string PIXEL_CATS_END = 'PixelCatsEnd';
    public const string POPPY_SEED_PETS = 'PSP';
    public const string STEAM = 'Steam';
    public const string TUMBLR = 'Tumblr';
    public const string TWITCH = 'Twitch';
    public const string YOUTUBE = 'YouTube';
}