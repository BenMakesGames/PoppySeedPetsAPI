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

use App\Enum\EnumInvalidValueException;
use App\Enum\HolidayEnum;
use Symfony\Component\Serializer\Attribute\Groups;

final class WeatherData
{
    /** @var HolidayEnum[] */
    #[Groups(['weather'])]
    public array $holidays;

    public float $temperature;
    public float $clouds;
    public float $rainfall;

    #[Groups(['weather'])]
    public bool $isNight;

    #[Groups(['weather'])]
    public function getTemperature(): float
    {
        return round($this->temperature, 1);
    }

    #[Groups(['weather'])]
    public function getClouds(): float
    {
        return round($this->clouds, 2);
    }

    #[Groups(['weather'])]
    public function getRainfall(): float
    {
        return round($this->rainfall, 2);
    }

    public function isHoliday(HolidayEnum $holiday): bool
    {
        return in_array($holiday, $this->holidays);
    }
}