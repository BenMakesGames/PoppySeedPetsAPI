<?php
namespace App\Service;

use App\Entity\Pet;
use App\Functions\RandomFunctions;
use App\Model\WeatherData;
use App\Model\WeatherForecastData;

class WeatherService
{
    private $calendarService;
    private $cache;

    private const HOUR_OF_DAY_TEMPERATURE_MODIFIER = [
        // 12 am ...
        1, 1, 1, 1, 1, 0,
        0, 1, 2, 4, 6, 7,
        8, 8, 9, 8, 8, 7,
        5, 4, 3, 3, 2, 2
        //       ... 11pm
    ];

    public function __construct(
        CalendarService  $calendarService, CacheHelper $cache
    )
    {
        $this->calendarService = $calendarService;
        $this->cache = $cache;
    }

    public static function getHourSince2000(\DateTimeImmutable $dt): float
    {
        $year = $dt->format('Y') - 2000;

        $isLeapYear = $dt->format('L') == 1;
        $dayOfYear = (int)$dt->format('z');

        // we don't want an extra day in leap years, for weather purposes
        if($isLeapYear && $dayOfYear > 60)
            $dayOfYear--;

        $hourOfDay = (int)$dt->format('G');
        $hourOfYear = $dayOfYear * 24 + $hourOfDay + ($dt->format('i') / 60); // + minute as fraction of hour

        // 8760 = 365 * 24
        return $year * 8760 + $hourOfYear;
    }

    public function getWeather(\DateTimeImmutable $dt, ?Pet $pet, $getHolidays = true): WeatherData
    {
        if($pet)
            $dt = $dt->modify('-' . max(0, $pet->getHouseTime()->getActivityTime()) . ' minutes');

        if($dt->format('nd') === '229')
            return WeatherService::getLeapDayWeather($dt);

        $weather = new WeatherData();

        $hourSince2000 = WeatherService::getHourSince2000($dt);

        $weather->holidays = $getHolidays ? CalendarService::getEventData($dt) : [];
        $weather->clouds = WeatherService::getClouds($hourSince2000);
        $weather->rainfall = WeatherService::getRainfall($hourSince2000);
        $weather->temperature = WeatherService::getTemperature($hourSince2000, $weather->rainfall);
        $weather->isNight = WeatherService::isNight($hourSince2000 % 24);

        return $weather;
    }

    /**
     * @return float Degrees Celsius
     */
    public static function getTemperature(float $hourOfYear, float $rainfall): float
    {
        $temp = 25 +
            2 * sin(M_PI * 4 * ($hourOfYear - 1500) / 8760) +
            2 * sin(M_PI * 2 * ($hourOfYear - 1500) / 8760)
        ;

        $hourOfDay = $hourOfYear % 24;

        $temp += self::HOUR_OF_DAY_TEMPERATURE_MODIFIER[$hourOfDay];

        // some random wiggling; less if it's raining
        // (13, 23, 11, 31, and 47 are all primes)
        $temp +=
            WeatherService::getNoise($hourOfYear, 0.13, 0.23, 0.11, 0.31, 0.47)
            / ($rainfall + 1)
        ;

        return $temp;
    }

    public static function getNoise($hourOfYear, $p1, $p2, $p3, $p4, $p5)
    {
        return (
            sin($p1 * M_E * $hourOfYear) +
            sin($p2 * M_PI * $hourOfYear) -
            sin($p3 * M_E * $hourOfYear) -
            sin($p4 * M_PI * $hourOfYear)
        ) / (
            cos($p5 * $hourOfYear) + 3
        );
    }

    /**
     * @return float ??? unit of measure ???
     */
    public static function getRainfall(float $hourOfYear): float
    {
        $moisture = WeatherService::getMoisture($hourOfYear);

        return 5 * max(0, $moisture - 0.3);
    }

    /**
     * @return float ??? unit of measure ???
     */
    public static function getClouds(float $hourOfYear): float
    {
        $moistureMinus3 = WeatherService::getMoisture($hourOfYear - 3);
        $moistureMinus2 = WeatherService::getMoisture($hourOfYear - 2);
        $moistureMinus1 = WeatherService::getMoisture($hourOfYear - 1);
        $moisture = WeatherService::getMoisture($hourOfYear);
        $moisturePlus1 = WeatherService::getMoisture($hourOfYear + 1);
        $moisturePlus2 = WeatherService::getMoisture($hourOfYear + 2);
        $moisturePlus3 = WeatherService::getMoisture($hourOfYear + 3);

        return max(0,
            $moistureMinus3 / 8 +
            $moistureMinus2 / 8 +
            $moistureMinus1 / 5 +
            $moisture / 2 +
            $moisturePlus1 / 5 +
            $moisturePlus2 / 8 +
            $moisturePlus3 / 8 -
            0.18
        );
    }

    public static function getMoisture(float $hourOfYear): float
    {
        $seasonal =
            1.5 * sin(M_PI * 2 * ($hourOfYear - 3500) / 8760) +
            sin(M_PI * 4 * ($hourOfYear - 600) / 8760) +
            5
        ;
        $n1 = WeatherService::getNoise($hourOfYear, 0.011, 0.041, 0.019, 0.037, 0.71) + 1.25;
        $n2 = WeatherService::getNoise($hourOfYear, 0.17, 0.23, 0.13, 0.37, 0.53) + 1.25;

        return ($seasonal * $n1 * $n2) / 50;
    }

    public static function isNight(int $hourOfDay): bool
    {
        return ($hourOfDay < 6 || $hourOfDay >= 18);
    }

    private static function getLeapDayWeather(\DateTimeImmutable $dt): WeatherData
    {
        $seed = 1618; // first four digits of the golden ratio

        $weather = new WeatherData();
        $weather->rainfall = 0;
        $weather->temperature = 18 + (RandomFunctions::squirrel3Noise((int)$dt->format('Y'), $seed) % 5);
        $weather->isNight = WeatherService::isNight($dt->format('G'));

        return $weather;
    }

    /**
     * @return WeatherData[]
     */
    public function get24HourForecast(): array
    {
        $now = new \DateTimeImmutable();

        return $this->cache->getOrCompute(
            'Weather Forecast ' . $now->format('Y-m-d G'),
            \DateInterval::createFromDateString('1 hour'),
            fn() => $this->compute24HourForecast()
        );
    }

    private function compute24HourForecast()
    {
        $forecast = [];
        $now = new \DateTimeImmutable();

        for($hour = 0; $hour < 24; $hour++)
        {
            $now = $now->modify('+1 hour');
            $forecast[] = $this->getWeather($now, null, false);
        }

        return $forecast;
    }

    /**
     * @return WeatherForecastData[]
     */
    public function get6DayForecast(): array
    {
        $forecast = [];

        for($day = 1; $day <= 6; $day++)
            $forecast[] = $this->getWeatherForecast((new \DateTimeImmutable())->modify('+' . $day . 'days'));

        return $forecast;
    }

    public function getWeatherForecast(\DateTimeImmutable $date): WeatherForecastData
    {
        return $this->cache->getOrCompute(
            'Weather Forecast ' . $date->format('Y-m-d'),
            \DateInterval::createFromDateString('1 day'),
            fn() => $this->computeWeatherForecast($date->setTime(0, 0, 0))
        );
    }

    private function computeWeatherForecast(\DateTimeImmutable $date): WeatherForecastData
    {
        $temperatures = [];
        $clouds = [];
        $rainfalls = [];

        for($hour = 0; $hour < 24; $hour++)
        {
            $dateToConsider = $date->setTime($hour, 30, 0);
            $weather = $this->getWeather($dateToConsider, null, false);

            $temperatures[] = $weather->temperature;
            $clouds[] = $weather->clouds;
            $rainfalls[] = $weather->rainfall;
        }

        $forecast = new WeatherForecastData();

        $forecast->date = $date->setTime(0, 0, 0);

        $forecast->holidays = CalendarService::getEventData($forecast->date);

        $forecast->maxRainfall = max($rainfalls);
        $forecast->minRainfall = min($rainfalls);
        $forecast->avgRainfall = array_sum($rainfalls) / 24;

        $forecast->maxClouds = max($clouds);
        $forecast->minClouds = min($clouds);
        $forecast->avgClouds = array_sum($clouds) / 24;

        $forecast->maxTemperature = max($temperatures);
        $forecast->minTemperature = min($temperatures);
        $forecast->avgTemperature = array_sum($temperatures) / 24;

        return $forecast;
    }
}