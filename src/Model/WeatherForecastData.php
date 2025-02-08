<?php
declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Serializer\Annotation\Groups;

class WeatherForecastData
{
    /**
     * @var \DateTimeImmutable
     * @Groups({"weather"})
     */
    public $date;

    /**
     * @var string[]
     * @Groups({"weather"})
     */
    public $holidays;

    public $minTemperature;
    public $maxTemperature;
    public $avgTemperature;
    public $minClouds;
    public $maxClouds;
    public $avgClouds;
    public $minRainfall;
    public $maxRainfall;
    public $avgRainfall;

    #[Groups(["weather"])]
    public function getMinTemperature(): float
    {
        return round($this->minTemperature, 1);
    }

    #[Groups(["weather"])]
    public function getMaxTemperature(): float
    {
        return round($this->maxTemperature, 1);
    }

    #[Groups(["weather"])]
    public function getAvgTemperature(): float
    {
        return round($this->avgTemperature, 1);
    }

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