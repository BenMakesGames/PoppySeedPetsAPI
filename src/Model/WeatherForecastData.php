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


namespace App\Model;

use App\Enum\HolidayEnum;
use Symfony\Component\Serializer\Attribute\Groups;

final class WeatherForecastData
{
    #[Groups(["weather"])]
    public \DateTimeImmutable $date;

    /** @var HolidayEnum[] */
    #[Groups(["weather"])]
    public array $holidays;

    public float $minClouds;
    public float $maxClouds;
    public float $avgClouds;
    public float $minRainfall;
    public float $maxRainfall;
    public float $avgRainfall;

    #[Groups(["weather"])]
    public function getMinClouds(): float
    {
        return round($this->minClouds, 1);
    }

    #[Groups(["weather"])]
    public function getMaxClouds(): float
    {
        return round($this->maxClouds, 1);
    }

    #[Groups(["weather"])]
    public function getAvgClouds(): float
    {
        return round($this->avgClouds, 1);
    }

    #[Groups(["weather"])]
    public function getMinRainfall(): float
    {
        return round($this->minRainfall, 1);
    }

    #[Groups(["weather"])]
    public function getMaxRainfall(): float
    {
        return round($this->maxRainfall, 1);
    }

    #[Groups(["weather"])]
    public function getAvgRainfall(): float
    {
        return round($this->avgRainfall, 1);
    }
}