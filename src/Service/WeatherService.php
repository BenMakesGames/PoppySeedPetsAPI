<?php
namespace App\Service;

use App\Entity\Pet;
use App\Functions\RandomFunctions;
use App\Model\WeatherData;

class WeatherService
{
    private $calendarService;

    private const HOUR_OF_DAY_TEMPERATURE_MODIFIER = [
        // 12 am ...
        1, 1, 1, 1, 1, 0,
        0, 1, 2, 4, 6, 7,
        8, 8, 9, 8, 8, 7,
        5, 4, 3, 3, 2, 2
        //       ... 11pm
    ];

    public function __construct(CalendarService  $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    public function getHourSince2000(\DateTimeImmutable $dt): float
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

    public function getWeather(\DateTimeImmutable $dt, ?Pet $pet): WeatherData
    {
        if($pet)
            $dt->modify('-' . max(0, $pet->getHouseTime()->getActivityTime()) . 'minutes');

        if($dt->format('nd') === '229')
            return $this->getLeapDayWeather($dt);

        $weather = new WeatherData();

        $hourSince2000 = $this->getHourSince2000($dt);

        $weather->rainfall = $this->getRainfall($hourSince2000);
        $weather->temperature = $this->getTemperature($hourSince2000, $weather->rainfall);
        $weather->isNight = $this->isNight($hourSince2000 % 24);

        return $weather;
    }

    /**
     * @return float Degrees Celsius
     */
    public function getTemperature(float $hourOfYear, float $rainfall): float
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
            $this->getNoise($hourOfYear, 0.13, 0.23, 0.11, 0.31, 0.47)
            / ($rainfall + 1)
        ;

        return $temp;
    }

    public function getNoise($hourOfYear, $p1, $p2, $p3, $p4, $p5)
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
    public function getRainfall(float $hourOfYear): float
    {
        $seasonal =
            1.5 * sin(M_PI * 2 * ($hourOfYear - 3500) / 8760) +
            sin(M_PI * 4 * ($hourOfYear - 600) / 8760) +
            5
        ;
        $n1 = $this->getNoise($hourOfYear, 0.011, 0.041, 0.019, 0.037, 0.71) + 1.25;
        $n2 = $this->getNoise($hourOfYear, 0.17, 0.23, 0.13, 0.37, 0.53) + 1.25;

        $rain = 5 * max(0, ($seasonal * $n1 * $n2) / 50 - 0.2);

        return $rain;
    }

    public function isNight(int $hourOfDay): bool
    {
        return ($hourOfDay < 6 || $hourOfDay >= 18);
    }

    private function getLeapDayWeather(\DateTimeImmutable $dt): WeatherData
    {
        $seed = 1618; // first four digits of the golden ratio

        $weather = new WeatherData();
        $weather->rainfall = 0;
        $weather->temperature = 18 + (RandomFunctions::squirrel3Noise((int)$dt->format('Y'), $seed) % 5);
        $weather->isNight = $this->isNight($dt->format('G'));

        return $weather;
    }
}