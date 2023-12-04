<?php
namespace App\Model;

use App\Enum\EnumInvalidValueException;
use App\Enum\HolidayEnum;
use Symfony\Component\Serializer\Annotation\Groups;

class WeatherData
{
    /**
     * @var string[]
     */
    #[Groups(['weather'])]
    public $holidays;

    public $temperature;
    public $clouds;
    public $rainfall;

    /**
     * @var bool
     */
    #[Groups(['weather'])]
    public $isNight;

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

    public function isHoliday(string $holiday): bool
    {
        if(!HolidayEnum::isAValue($holiday))
            throw new EnumInvalidValueException(HolidayEnum::class, $holiday);

        return in_array($holiday, $this->holidays);
    }
}