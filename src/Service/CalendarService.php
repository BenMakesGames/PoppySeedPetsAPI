<?php
namespace App\Service;

use App\Enum\HolidayEnum;
use App\Functions\DateFunctions;
use App\Functions\JewishCalendarFunctions;
use App\Model\ChineseCalendarInfo;

final class CalendarService
{
    public static function isJelephantDay(\DateTimeInterface $dt): bool
    {
        return $dt->format('nd') === '812';
    }

    public static function isNoombatDay(\DateTimeInterface $dt)
    {
        $monthAndDay = (int)$dt->format('nd');

        // if it's not November, just get outta' here
        if($monthAndDay < 1100 || $monthAndDay >= 1200)
            return false;

        $firstSaturdayOfNovember = (int)(new \DateTimeImmutable('first Saturday of this month'))->format('md');

        return $monthAndDay === $firstSaturdayOfNovember;
    }

    public static function isPSPBirthday(\DateTimeInterface $dt)
    {
        $monthAndDay = (int)$dt->format('nd');
        return $monthAndDay >= 621 && $monthAndDay <= 623;
    }

    public static function isSummerSolstice(\DateTimeInterface $dt)
    {
        $monthAndDay = (int)$dt->format('nd');
        return $monthAndDay >= 619 && $monthAndDay <= 622;
    }

    public static function isWinterSolstice(\DateTimeInterface $dt)
    {
        $monthAndDay = (int)$dt->format('nd');
        return $monthAndDay >= 1220 && $monthAndDay <= 1223;
    }

    public static function isThanksgivingMonsters(\DateTimeInterface $dt)
    {
        $monthAndDay = (int)$dt->format('nd');

        // if it's not November, just get outta' here
        if($monthAndDay < 1100 || $monthAndDay >= 1200)
            return false;

        $fourthThursdayOfNovember = new \DateTimeImmutable('fourth Thursday of this month');

        return
            $monthAndDay >= (int)$fourthThursdayOfNovember->format('md') - 14 &&
            $monthAndDay <= (int)$fourthThursdayOfNovember->format('md')
        ;
    }

    public static function isThanksgiving(\DateTimeInterface $dt): bool
    {
        $monthAndDay = (int)$dt->format('nd');

        // if it's not November, just get outta' here
        if($monthAndDay < 1122 || $monthAndDay >= 1200)
            return false;

        $fourthThursdayOfNovember = new \DateTimeImmutable('fourth Thursday of this month');

        // within 1 day of thanksgiving
        return abs((int)$fourthThursdayOfNovember->format('md') - $monthAndDay) <= 1;
    }

    public static function isBlackFriday(\DateTimeInterface $dt): bool
    {
        $monthAndDay = (int)$dt->format('nd');

        // if it's not November, just get outta' here
        if($monthAndDay < 1122 || $monthAndDay >= 1200)
            return false;

        $blackFriday = (new \DateTimeImmutable('fourth Thursday of this month'))->modify('+1 day');

        return (int)$blackFriday->format('md') === $monthAndDay;
    }

    public static function isCyberMonday(\DateTimeInterface $dt): bool
    {
        $monthAndDay = (int)$dt->format('nd');

        if($monthAndDay < 1125 || $monthAndDay >= 1203)
            return false;

        $cyberMonday = (new \DateTimeImmutable('fourth Thursday of this month'))->modify('+4 day');

        return (int)$cyberMonday->format('md') === $monthAndDay;
    }

    public static function isHalloweenCrafting(\DateTimeInterface $dt)
    {
        $monthAndDay = (int)$dt->format('nd');
        return $monthAndDay >= 1017 && $monthAndDay <= 1031;
    }

    public static function isSaintMartinsDayCrafting(\DateTimeInterface $dt)
    {
        $monthAndDay = (int)$dt->format('nd');
        return $monthAndDay >= 1101 && $monthAndDay <= 1111;
    }

    public static function isSaintPatricksDay(\DateTimeInterface $dt)
    {
        $monthAndDay = (int)$dt->format('nd');
        return $monthAndDay >= 315 && $monthAndDay <= 317;
    }

    public static function isPiDayCrafting(\DateTimeInterface $dt): bool
    {
        $monthAndDay = (int)$dt->format('nd');

        return
            ($monthAndDay >= 313 && $monthAndDay <= 315) ||
            ($monthAndDay >= 721 && $monthAndDay <= 723)
        ;
    }

    public static function isHalloween(\DateTimeInterface $dt)
    {
        $monthAndDay = (int)$dt->format('nd');
        return $monthAndDay >= 1029 && $monthAndDay <= 1031;
    }

    public static function isHalloweenDay(\DateTimeInterface $dt)
    {
        return $dt->format('nd') === '1031';
    }

    public static function isPiDay(\DateTimeInterface $dt)
    {
        $monthAndDay = $dt->format('nd');
        return $monthAndDay === '314' || $monthAndDay === '722';
    }

    public static function isPsyPetsBirthday(\DateTimeInterface $dt)
    {
        $monthAndDay = $dt->format('nd');
        return $monthAndDay === '321';
    }

    public static function isAprilFools(\DateTimeInterface $dt)
    {
        $monthAndDay = $dt->format('nd');
        return $monthAndDay === '401';
    }

    public static function isBastilleDay(\DateTimeInterface $dt)
    {
        $monthAndDay = (int)$dt->format('nd');
        return $monthAndDay >= 713 && $monthAndDay <= 715;
    }

    public static function isCincoDeMayo(\DateTimeInterface $dt)
    {
        $monthAndDay = (int)$dt->format('nd');
        return $monthAndDay >= 504 && $monthAndDay <= 506;
    }

    public static function isWhiteDay(\DateTimeInterface $dt)
    {
        $monthAndDay = $dt->format('nd');
        return $monthAndDay === '314';
    }

    public static function isMayThe4th(\DateTimeInterface $dt)
    {
        $monthAndDay = $dt->format('nd');
        return $monthAndDay === '504';
    }

    public static function isAwaOdori(\DateTimeInterface $dt)
    {
        $monthAndDay = (int)$dt->format('nd');
        return $monthAndDay >= 812 && $monthAndDay <= 815;
    }

    public static function isTalkLikeAPirateDay(\DateTimeInterface $dt)
    {
        $monthAndDay = $dt->format('nd');
        return $monthAndDay === '919';
    }

    public static function isJuly4th(\DateTimeInterface $dt)
    {
        $monthAndDay = (int)$dt->format('nd');
        return $monthAndDay >= 703 && $monthAndDay <= 705;
    }

    public static function isEarthDay(\DateTimeInterface $dt)
    {
        $monthAndDay = (int)$dt->format('nd');
        return $monthAndDay >= 420 && $monthAndDay <= 422;
    }

    public static function getChineseCalendarInfo(\DateTimeInterface $dt): ChineseCalendarInfo
    {
        $info = (new \Overtrue\ChineseCalendar\Calendar())
            ->solar($dt->format('Y'), $dt->format('m'), $dt->format('d'))
        ;

        $result = new ChineseCalendarInfo();

        $result->year = (int)$info['lunar_year'];
        $result->month = (int)$info['lunar_month'];
        $result->day = (int)$info['lunar_day'];
        $result->animal = $info['animal'];
        $result->isLeapYear = $info['is_leap'];

        return $result;
    }

    public static function isHanukkah(\DateTimeInterface $dt): bool
    {
        $jdCurrent = gregoriantojd($dt->format('m'), $dt->format('d'), $dt->format('Y'));
        [$jewishYear, $jewishMonth, $jewishDay] = JewishCalendarFunctions::getJewishDate($dt);

        $hanukkahStart = jewishtojd(JewishCalendarFunctions::KISLEV, 25, $jewishYear);
        $hanukkahNo = (int)($jdCurrent - $hanukkahStart + 1);

        return $hanukkahNo >= 1 && $hanukkahNo <= 8;
    }

    public static function isValentinesOrAdjacent(\DateTimeInterface $dt)
    {
        $monthAndDay = (int)$dt->format('nd');
        return $monthAndDay >= 213 && $monthAndDay <= 215;
    }

    public static function isEaster(\DateTimeInterface $dt): bool
    {
        $easter = \DateTimeImmutable::createFromFormat('U', easter_date((int)$dt->format('Y')));
        $easter = $easter->setTime(0, 0, 0);

        if($dt > $easter)
            return false;

        $diff = $dt->diff($easter)->days;

        return $diff < 3;
    }

    public static function isHoli(\DateTimeInterface $dt)
    {
        $monthAndDay = $dt->format('nd');
        return $monthAndDay === self::HOLI_MONTH_DAYS[(int)$dt->format('Y')];
    }

    public static function isNewYearsHoliday(\DateTimeInterface $dt)
    {
        $monthAndDay = (int)$dt->format('nd');
        return $monthAndDay === 1231 && $monthAndDay <= 102;
    }

    public static function isStockingStuffingSeason(\DateTimeInterface $dt)
    {
        $monthAndDay = (int)$dt->format('nd');
        return $monthAndDay >= 1200 && $monthAndDay <= 1231;
    }

    public static function isLeonidPeakOrAdjacent(\DateTimeInterface $dt): bool
    {
        $year = (int)$dt->format('Y');
        $monthAndDay = (int)$dt->format('nd');

        $leonidPeakDay = array_key_exists($year, self::LEONID_PEAK_DAYS)
            ? self::LEONID_PEAK_DAYS[$year]
            : self::LEONID_PEAK_DAY_DEFAULT
        ;

        return abs($monthAndDay - $leonidPeakDay) <= 1;
    }

    public static function isChineseNewYear(\DateTimeInterface $dt): bool
    {
        $chineseCalendarInfo = self::getChineseCalendarInfo($dt);

        return $chineseCalendarInfo->month === 1 && $chineseCalendarInfo->day <= 6;
    }

    /**
     * @return string[]
     */
    public static function getEventData(\DateTimeImmutable $dt): array
    {
        $events = [];

        $fullMoonName = DateFunctions::getFullMoonName($dt);

        if($fullMoonName)
            $events[] = $fullMoonName . ' Moon';

        if(self::isStockingStuffingSeason($dt))
            $events[] = HolidayEnum::STOCKING_STUFFING_SEASON;

        if(self::isHalloween($dt))
            $events[] = HolidayEnum::HALLOWEEN;

        if(self::isEaster($dt))
            $events[] = HolidayEnum::EASTER;

        if(self::isSaintPatricksDay($dt))
            $events[] = HolidayEnum::SAINT_PATRICKS;

        if(self::isValentinesOrAdjacent($dt))
            $events[] = HolidayEnum::VALENTINES;

        if(self::isCyberMonday($dt))
            $events[] = HolidayEnum::CYBER_MONDAY;

        if(self::isBlackFriday($dt))
            $events[] = HolidayEnum::BLACK_FRIDAY;

        if(self::isPiDay($dt))
            $events[] = HolidayEnum::PI_DAY;

        if(self::isPSPBirthday($dt))
            $events[] = HolidayEnum::PSP_BIRTHDAY;

        if(self::isSummerSolstice($dt))
            $events[] = HolidayEnum::SUMMER_SOLSTICE;

        if(self::isWinterSolstice($dt))
            $events[] = HolidayEnum::WINTER_SOLSTICE;

        if(self::isPsyPetsBirthday($dt))
            $events[] = HolidayEnum::PSYPETS_BIRTHDAY;

        if(self::isAprilFools($dt))
            $events[] = HolidayEnum::APRIL_FOOLS;

        if(self::isHanukkah($dt))
            $events[] = HolidayEnum::HANUKKAH;

        if(self::isWhiteDay($dt))
            $events[] = HolidayEnum::WHITE_DAY;

        if(self::isTalkLikeAPirateDay($dt))
            $events[] = HolidayEnum::TALK_LIKE_A_PIRATE_DAY;

        if(self::isThanksgiving($dt))
            $events[] = HolidayEnum::THANKSGIVING;

        if(self::isNewYearsHoliday($dt))
            $events[] = HolidayEnum::NEW_YEARS_DAY;

        if(self::isJuly4th($dt))
            $events[] = HolidayEnum::FOURTH_OF_JULY;

        if(self::isBastilleDay($dt))
            $events[] = HolidayEnum::BASTILLE_DAY;

        if(self::isAwaOdori($dt))
            $events[] = HolidayEnum::AWA_ODORI;

        if(self::isEarthDay($dt))
            $events[] = HolidayEnum::EARTH_DAY;

        if(self::isCincoDeMayo($dt))
            $events[] = HolidayEnum::CINCO_DE_MAYO;

        if(self::isNoombatDay($dt))
            $events[] = HolidayEnum::NOOMBAT_DAY;

        if(self::isJelephantDay($dt))
            $events[] = HolidayEnum::JELEPHANT_DAY;

        if(self::isChineseNewYear($dt))
            $events[] = HolidayEnum::CHINESE_NEW_YEAR;

        if(self::isHoli($dt))
            $events[] = HolidayEnum::HOLI;

        if(self::isLeonidPeakOrAdjacent($dt))
            $events[] = HolidayEnum::LEONIDS;

        return $events;
    }

    public const HOLI_MONTH_DAYS = [
        2021 => '328',
        2022 => '317',
        2023 => '307',
        2024 => '324',
        2025 => '313',
        2026 => '303',
        2027 => '322',
        2028 => '310',
        2029 => '228',
        2030 => '319',
        2031 => '309',
        2032 => '327',
        2033 => '316',
    ];

    public const LEONID_PEAK_DAY_DEFAULT = 1117;

    public const LEONID_PEAK_DAYS = [
        // for years which are omitted, LEONID_PEAK_DAY_DEFAULT is assumed
        2022 => 1119,
    ];

}
