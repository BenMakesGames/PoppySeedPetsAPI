<?php
namespace App\Service;

use App\Enum\HolidayEnum;
use App\Functions\DateFunctions;
use App\Functions\JewishCalendarFunctions;
use App\Model\ChineseCalendarInfo;

class CalendarService
{
    private \DateTimeImmutable $today;
    private int $monthAndDay;

    /**
     * @deprecated
     */
    public function __construct()
    {
        $this->setToday(new \DateTimeImmutable());
    }

    /**
     * @deprecated
     */
    public function setToday(\DateTimeImmutable $date)
    {
        $this->today = $date->setTime(0, 0, 0);
        $this->monthAndDay = (int)$this->today->format('nd');
    }

    /**
     * @deprecated
     */
    public function deprecatedIsJelephantDay(): bool
    {
        return $this->monthAndDay === 812;
    }

    public static function isJelephantDay(\DateTimeInterface $dt): bool
    {
        return $dt->format('nd') === '812';
    }

    /**
     * @deprecated
     */
    public function deprecatedIsNoombatDay(): bool
    {
        // if it's not November, just get outta' here
        if($this->monthAndDay < 1100 || $this->monthAndDay >= 1200)
            return false;

        $firstSaturdayOfNovember = (int)(new \DateTimeImmutable('first Saturday of this month'))->format('md');

        return $this->monthAndDay === $firstSaturdayOfNovember;
    }

    public function getMonthAndDay(): int
    {
        return $this->monthAndDay;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsPSPBirthday(): bool
    {
        return $this->monthAndDay >= 621 && $this->monthAndDay <= 623;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsSummerSolstice(): bool
    {
        return $this->monthAndDay >= 619 && $this->monthAndDay <= 622;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsWinterSolstice(): bool
    {
        return $this->monthAndDay >= 1220 && $this->monthAndDay <= 1223;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsThanksgivingMonsters(): bool
    {
        // if it's not November, just get outta' here
        if($this->monthAndDay < 1100 || $this->monthAndDay >= 1200)
            return false;

        $fourthThursdayOfNovember = new \DateTimeImmutable('fourth Thursday of this month');

        return
            $this->monthAndDay >= (int)$fourthThursdayOfNovember->format('md') - 14 &&
            $this->monthAndDay <= (int)$fourthThursdayOfNovember->format('md')
        ;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsThanksgiving(): bool
    {
        // if it's not November, just get outta' here
        if($this->monthAndDay < 1122 || $this->monthAndDay >= 1200)
            return false;

        $fourthThursdayOfNovember = new \DateTimeImmutable('fourth Thursday of this month');

        // within 1 day of thanksgiving
        return abs((int)$fourthThursdayOfNovember->format('md') - $this->monthAndDay) <= 1;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsBlackFriday(): bool
    {
        // if it's not November, just get outta' here
        if($this->monthAndDay < 1122 || $this->monthAndDay >= 1200)
            return false;

        $blackFriday = (new \DateTimeImmutable('fourth Thursday of this month'))->modify('+1 day');

        return (int)$blackFriday->format('md') == $this->monthAndDay;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsCyberMonday(): bool
    {
        if($this->monthAndDay < 1125 || $this->monthAndDay >= 1203)
            return false;

        $cyberMonday = (new \DateTimeImmutable('fourth Thursday of this month'))->modify('+4 day');

        return (int)$cyberMonday->format('md') == $this->monthAndDay;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsHalloweenCrafting(): bool
    {
        return $this->monthAndDay >= 1017 && $this->monthAndDay <= 1031;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsSaintMartinsDayCrafting(): bool
    {
        return $this->monthAndDay >= 1101 && $this->monthAndDay <= 1111;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsSaintPatricksDay(): bool
    {
        return $this->monthAndDay >= 315 && $this->monthAndDay <= 317;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsPiDayCrafting(): bool
    {
        return
            ($this->monthAndDay >= 313 && $this->monthAndDay <= 315) ||
            ($this->monthAndDay >= 721 && $this->monthAndDay <= 723)
        ;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsHalloween(): bool
    {
        return $this->monthAndDay >= 1029 && $this->monthAndDay <= 1031;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsHalloweenDay(): bool
    {
        return $this->monthAndDay === 1031;
    }

    public static function isHalloweenDay(\DateTimeInterface $dt)
    {
        return $dt->format('nd') === '1031';
    }

    /**
     * @deprecated
     */
    public function deprecatedIsPiDay(): bool
    {
        return $this->monthAndDay === 314 || $this->monthAndDay === 722;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsPsyPetsBirthday(): bool
    {
        return $this->monthAndDay === 321;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsAprilFools(): bool
    {
        return $this->monthAndDay === 401;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsBastilleDay(): bool
    {
        return $this->monthAndDay >= 713 && $this->monthAndDay <= 715;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsCincoDeMayo(): bool
    {
        return $this->monthAndDay >= 504 && $this->monthAndDay <= 506;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsWhiteDay(): bool
    {
        return $this->monthAndDay === 314;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsMayThe4th(): bool
    {
        return $this->monthAndDay === 504;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsAwaOdori(): bool
    {
        return $this->monthAndDay >= 812 && $this->monthAndDay <= 815;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsTalkLikeAPirateDay(): bool
    {
        return $this->monthAndDay === 919;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsJuly4th(): bool
    {
        return $this->monthAndDay >= 703 && $this->monthAndDay <= 705;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsLeapDay(): bool
    {
        return $this->monthAndDay === 229;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsEarthDay(): bool
    {
        return $this->monthAndDay >= 420 && $this->monthAndDay <= 422;
    }

    public function getChineseCalendarInfo(): ChineseCalendarInfo
    {
        $info = (new \Overtrue\ChineseCalendar\Calendar())
            ->solar($this->today->format('Y'), $this->today->format('m'), $this->today->format('d'))
        ;

        $result = new ChineseCalendarInfo();

        $result->year = (int)$info['lunar_year'];
        $result->month = (int)$info['lunar_month'];
        $result->day = (int)$info['lunar_day'];
        $result->animal = $info['animal'];
        $result->isLeapYear = $info['is_leap'];

        return $result;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsHanukkah(): bool
    {
        $jdCurrent = gregoriantojd($this->today->format('m'), $this->today->format('d'), $this->today->format('Y'));
        [$jewishYear, $jewishMonth, $jewishDay] = JewishCalendarFunctions::getJewishDate($this->today);

        $hanukkahStart = jewishtojd(JewishCalendarFunctions::KISLEV, 25, $jewishYear);
        $hanukkahNo = (int)($jdCurrent - $hanukkahStart + 1);

        return $hanukkahNo >= 1 && $hanukkahNo <= 8;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsValentinesOrAdjacent(): bool
    {
        return $this->monthAndDay >= 213 && $this->monthAndDay <= 215;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsEaster(): bool
    {
        $easter = \DateTimeImmutable::createFromFormat('U', easter_date((int)$this->today->format('Y')));
        $easter = $easter->setTime(0, 0, 0);

        if($this->today > $easter)
            return false;

        $diff = $this->today->diff($easter)->days;

        return $diff < 3;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsHoli(): bool
    {
        // :(
        return $this->monthAndDay == self::HOLI_MONTH_DAYS[(int)$this->today->format('Y')];
    }

    /**
     * @deprecated
     */
    public function deprecatedIsNewYearsHoliday(): bool
    {
        return $this->monthAndDay === 1231 || $this->monthAndDay <= 102;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsStockingStuffingSeason(): bool
    {
        return $this->monthAndDay >= 1200 && $this->monthAndDay <= 1231;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsLeonidPeakOrAdjacent(): bool
    {
        $year = (int)$this->today->format('Y');

        $leonidPeakDay = array_key_exists($year, self::LEONID_PEAK_DAYS)
            ? self::LEONID_PEAK_DAYS[$year]
            : self::LEONID_PEAK_DAY_DEFAULT
        ;

        return abs($this->monthAndDay - $leonidPeakDay) <= 1;
    }

    /**
     * @deprecated
     */
    public function deprecatedIsChineseNewYear(): bool
    {
        $chineseCalendarInfo = $this->getChineseCalendarInfo();

        return $chineseCalendarInfo->month === 1 && $chineseCalendarInfo->day <= 6;
    }

    /**
     * @return string[]
     */
    public function getEventData(\DateTimeImmutable $dt): array
    {
        $oldToday = $this->today;
        $this->setToday($dt);

        $events = [];

        $fullMoonName = DateFunctions::getFullMoonName($this->today);

        if($fullMoonName)
            $events[] = $fullMoonName . ' Moon';

        if($this->deprecatedIsStockingStuffingSeason())
            $events[] = HolidayEnum::STOCKING_STUFFING_SEASON;

        if($this->deprecatedIsHalloween())
            $events[] = HolidayEnum::HALLOWEEN;

        if($this->deprecatedIsEaster())
            $events[] = HolidayEnum::EASTER;

        if($this->deprecatedIsSaintPatricksDay())
            $events[] = HolidayEnum::SAINT_PATRICKS;

        if($this->deprecatedIsValentinesOrAdjacent())
            $events[] = HolidayEnum::VALENTINES;

        if($this->deprecatedIsCyberMonday())
            $events[] = HolidayEnum::CYBER_MONDAY;

        if($this->deprecatedIsBlackFriday())
            $events[] = HolidayEnum::BLACK_FRIDAY;

        if($this->deprecatedIsPiDay())
            $events[] = HolidayEnum::PI_DAY;

        if($this->deprecatedIsPSPBirthday())
            $events[] = HolidayEnum::PSP_BIRTHDAY;

        if($this->deprecatedIsSummerSolstice())
            $events[] = HolidayEnum::SUMMER_SOLSTICE;

        if($this->deprecatedIsWinterSolstice())
            $events[] = HolidayEnum::WINTER_SOLSTICE;

        if($this->deprecatedIsPsyPetsBirthday())
            $events[] = HolidayEnum::PSYPETS_BIRTHDAY;

        if($this->deprecatedIsAprilFools())
            $events[] = HolidayEnum::APRIL_FOOLS;

        if($this->deprecatedIsHanukkah())
            $events[] = HolidayEnum::HANUKKAH;

        if($this->deprecatedIsWhiteDay())
            $events[] = HolidayEnum::WHITE_DAY;

        if($this->deprecatedIsTalkLikeAPirateDay())
            $events[] = HolidayEnum::TALK_LIKE_A_PIRATE_DAY;

        if($this->deprecatedIsThanksgiving())
            $events[] = HolidayEnum::THANKSGIVING;

        if($this->deprecatedIsNewYearsHoliday())
            $events[] = HolidayEnum::NEW_YEARS_DAY;

        if($this->deprecatedIsJuly4th())
            $events[] = HolidayEnum::FOURTH_OF_JULY;

        if($this->deprecatedIsBastilleDay())
            $events[] = HolidayEnum::BASTILLE_DAY;

        if($this->deprecatedIsAwaOdori())
            $events[] = HolidayEnum::AWA_ODORI;

        if($this->deprecatedIsEarthDay())
            $events[] = HolidayEnum::EARTH_DAY;

        if($this->deprecatedIsCincoDeMayo())
            $events[] = HolidayEnum::CINCO_DE_MAYO;

        if($this->deprecatedIsNoombatDay())
            $events[] = HolidayEnum::NOOMBAT_DAY;

        if($this->deprecatedIsJelephantDay())
            $events[] = HolidayEnum::JELEPHANT_DAY;

        if($this->deprecatedIsChineseNewYear())
            $events[] = HolidayEnum::CHINESE_NEW_YEAR;

        if($this->deprecatedIsHoli())
            $events[] = HolidayEnum::HOLI;

        if($this->deprecatedIsLeonidPeakOrAdjacent())
            $events[] = HolidayEnum::LEONIDS;

        $this->setToday($oldToday);

        return $events;
    }

    public const HOLI_MONTH_DAYS = [
        2021 => 328,
        2022 => 317,
        2023 => 307,
        2024 => 324,
        2025 => 313,
        2026 => 303,
        2027 => 322,
        2028 => 310,
        2029 => 228,
        2030 => 319,
        2031 => 309,
        2032 => 327,
        2033 => 316,
    ];

    public const LEONID_PEAK_DAY_DEFAULT = 1117;

    public const LEONID_PEAK_DAYS = [
        // for years which are omitted, LEONID_PEAK_DAY_DEFAULT is assumed
        2022 => 1119,
    ];

}
