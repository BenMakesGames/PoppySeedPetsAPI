<?php
namespace App\Functions;

use App\Enum\MoonPhaseEnum;

final class DateFunctions
{
    // adapted from https://github.com/BenMakesGames/PsyPets/blob/master/lib/commons/moonphase.php
    // which was adapted from http://home.att.net/~srschmitt/script_moon_phase.html#contents
    // which was adapted from a BASIC program in the Astronomical Computing column of Sky & Telescope, April 1994.
    // what a history :P
    public static function moonPhase(\DateTimeInterface $dt): string
    {
        $year = (int)$dt->format('Y');
        $month = (int)$dt->format('n');
        $day = (int)$dt->format('j');
        $YY = $year - floor((12 - $month) / 10);
        $MM = ($month + 9) % 12;
        $K1 = floor(365.25 * ($YY + 4712));
        $K2 = floor(30.6 * $MM + .5);
        $K3 = floor(floor(($YY / 100) + 49) * .75) - 38;

        $JD = $K1 + $K2 + $day + 59;
        if($JD > 2299160)
            $JD -= $K3;
        $IP = ($JD - 2451550.1) / 29.530588853;
        // normalize IP
        $IP = $IP - floor($IP);
        if($IP < 0)
            $IP++;
        $AG = $IP * 29.53;

        if($AG < 1.84566)
            return MoonPhaseEnum::NEW_MOON;
        else if($AG < 5.53699)
            return MoonPhaseEnum::WAXING_CRESCENT;
        else if($AG < 9.22831)
            return MoonPhaseEnum::FIRST_QUARTER;
        else if($AG < 12.91963)
            return MoonPhaseEnum::WAXING_GIBBOUS;
        else if($AG < 16.61096)
            return MoonPhaseEnum::FULL_MOON;
        else if($AG < 20.30228)
            return MoonPhaseEnum::WANING_GIBBOUS;
        else if($AG < 23.99361)
            return MoonPhaseEnum::LAST_QUARTER;
        else if($AG < 27.68493)
            return MoonPhaseEnum::WANING_CRESCENT;
        else
            return MoonPhaseEnum::NEW_MOON;
    }

    public static function moonStrength(\DateTimeInterface $dt): integer
    {
        $year = (int)$dt->format('Y');
        $month = (int)$dt->format('n');
        $day = (int)$dt->format('j');
        $YY = $year - floor((12 - $month) / 10);
        $MM = ($month + 9) % 12;
        $K1 = floor(365.25 * ($YY + 4712));
        $K2 = floor(30.6 * $MM + .5);
        $K3 = floor(floor(($YY / 100) + 49) * .75) - 38;

        $JD = $K1 + $K2 + $day + 59;
        if($JD > 2299160)
            $JD -= $K3;
        $IP = ($JD - 2451550.1) / 29.530588853;
        // normalize IP
        $IP = $IP - floor($IP);
        if($IP < 0)
            $IP++;
        $AG = $IP * 29.53;

        if($AG < 1.84566)
            return -1; // new
        else if($AG < 5.53699)
            return 0;
        else if($AG < 9.22831)
            return 1;
        else if($AG < 12.91963)
            return 2;
        else if($AG < 16.61096)
            return 3; // full
        else if($AG < 20.30228)
            return 2;
        else if($AG < 23.99361)
            return 1;
        else if($AG < 27.68493)
            return 0;
        else
            return -1; // new
    }
}