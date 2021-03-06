<?php
namespace App\Model;

use Symfony\Component\Serializer\Annotation\Groups;

class WeatherData
{
    public $temperature;
    public $rainfall;

    /**
     * @var bool
     * @Groups({"weather"})
     */
    public $isNight;

    /**
     * @Groups({"weather"})
     */
    public function getTemperature(): float
    {
        return round($this->temperature, 1);
    }

    /**
     * @Groups({"weather"})
     */
    public function getRainfall(): float
    {
        return round($this->rainfall, 1);
    }
}