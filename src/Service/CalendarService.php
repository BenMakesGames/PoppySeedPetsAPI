<?php
namespace App\Service;

use App\Entity\User;
use App\Enum\HolidayEnum;
use App\Functions\DateFunctions;
use App\Functions\JewishCalendarFunctions;
use App\Model\ChineseCalendarInfo;
use App\Service\Holidays\HalloweenService;

class CalendarService
{
    private \DateTimeImmutable $today;
    private int $monthAndDay;

    public function __construct()
    {
        $this->setToday(new \DateTimeImmutable());
    }

    public function setToday(\DateTimeImmutable $date)
    {
        $this->today = $date->setTime(0, 0, 0);
        $this->monthAndDay = (int)$this->today->format('nd');
    }

    public function isJelephantDay(): bool
    {
        return $this->monthAndDay === 812;
    }

    public function isNoombatDay(): bool
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

    public function isPSPBirthday(): bool
    {
        return $this->monthAndDay >= 621 && $this->monthAndDay <= 623;
    }

    public function isSummerSolstice(): bool
    {
        return $this->monthAndDay >= 619 && $this->monthAndDay <= 622;
    }

    public function isWinterSolstice(): bool
    {
        return $this->monthAndDay >= 1220 && $this->monthAndDay <= 1223;
    }

    public function isThanksgivingMonsters(): bool
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

    public function isThanksgiving(): bool
    {
        // if it's not November, just get outta' here
        if($this->monthAndDay < 1122 || $this->monthAndDay >= 1200)
            return false;

        $fourthThursdayOfNovember = new \DateTimeImmutable('fourth Thursday of this month');

        // within 1 day of thanksgiving
        return abs((int)$fourthThursdayOfNovember->format('md') - $this->monthAndDay) <= 1;
    }

    public function isBlackFriday(): bool
    {
        // if it's not November, just get outta' here
        if($this->monthAndDay < 1122 || $this->monthAndDay >= 1200)
            return false;

        $blackFriday = (new \DateTimeImmutable('fourth Thursday of this month'))->modify('+1 day');

        return (int)$blackFriday->format('md') == $this->monthAndDay;
    }

    public function isCyberMonday(): bool
    {
        if($this->monthAndDay < 1125 || $this->monthAndDay >= 1203)
            return false;

        $cyberMonday = (new \DateTimeImmutable('fourth Thursday of this month'))->modify('+4 day');

        return (int)$cyberMonday->format('md') == $this->monthAndDay;
    }

    public function isHalloweenCrafting(): bool
    {
        return $this->monthAndDay >= 1017 && $this->monthAndDay <= 1031;
    }

    public function isSaintMartinsDayCrafting(): bool
    {
        return $this->monthAndDay >= 1101 && $this->monthAndDay <= 1111;
    }

    public function isSaintPatricksDay(): bool
    {
        return $this->monthAndDay >= 315 && $this->monthAndDay <= 317;
    }

    public function isPiDayCrafting(): bool
    {
        return
            ($this->monthAndDay >= 313 && $this->monthAndDay <= 315) ||
            ($this->monthAndDay >= 721 && $this->monthAndDay <= 723)
        ;
    }

    public function isHalloween(): bool
    {
        return $this->monthAndDay >= 1029 && $this->monthAndDay <= 1031;
    }

    public function isHalloweenDay(): bool
    {
        return $this->monthAndDay === 1031;
    }

    public function isPiDay(): bool
    {
        return $this->monthAndDay === 314 || $this->monthAndDay === 722;
    }

    public function isPsyPetsBirthday(): bool
    {
        return $this->monthAndDay === 321;
    }

    public function isAprilFools(): bool
    {
        return $this->monthAndDay === 401;
    }

    public function isBastilleDay(): bool
    {
        return $this->monthAndDay >= 713 && $this->monthAndDay <= 715;
    }

    public function isCincoDeMayo(): bool
    {
        return $this->monthAndDay >= 504 && $this->monthAndDay <= 506;
    }

    public function isWhiteDay(): bool
    {
        return $this->monthAndDay === 314;
    }

    public function isMayThe4th(): bool
    {
        return $this->monthAndDay === 504;
    }

    public function isAwaOdori(): bool
    {
        return $this->monthAndDay >= 812 && $this->monthAndDay <= 815;
    }

    public function isTalkLikeAPirateDay(): bool
    {
        return $this->monthAndDay === 919;
    }

    public function isJuly4th(): bool
    {
        return $this->monthAndDay >= 703 && $this->monthAndDay <= 705;
    }

    public function isLeapDay(): bool
    {
        return $this->monthAndDay === 229;
    }

    public function isEarthDay(): bool
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

    public function isHanukkah(): bool
    {
        $jdCurrent = gregoriantojd($this->today->format('m'), $this->today->format('d'), $this->today->format('Y'));
        [$jewishYear, $jewishMonth, $jewishDay] = JewishCalendarFunctions::getJewishDate($this->today);

        $hanukkahStart = jewishtojd(JewishCalendarFunctions::KISLEV, 25, $jewishYear);
        $hanukkahNo = (int)($jdCurrent - $hanukkahStart + 1);

        return $hanukkahNo >= 1 && $hanukkahNo <= 8;
    }

    public function isValentinesOrAdjacent(): bool
    {
        return $this->monthAndDay >= 213 && $this->monthAndDay <= 215;
    }

    public function isEaster(): bool
    {
        $easter = \DateTimeImmutable::createFromFormat('U', easter_date((int)$this->today->format('Y')));
        $easter = $easter->setTime(0, 0, 0);

        if($this->today > $easter)
            return false;

        $diff = $this->today->diff($easter)->days;

        return $diff < 3;
    }

    public function isHoli(): bool
    {
        // :(
        return $this->monthAndDay == self::HOLI_MONTH_DAYS[(int)$this->today->format('Y')];
    }

    public function isNewYearsHoliday(): bool
    {
        return $this->monthAndDay === 1231 || $this->monthAndDay <= 102;
    }

    public function isStockingStuffingSeason(): bool
    {
        return $this->monthAndDay >= 1200 && $this->monthAndDay <= 1231;
    }

    public function isLeonidPeakOrAdjacent(): bool
    {
        $year = (int)$this->today->format('Y');

        $leonidPeakDay = array_key_exists($year, self::LEONID_PEAK_DAYS)
            ? self::LEONID_PEAK_DAYS[$year]
            : self::LEONID_PEAK_DAY_DEFAULT
        ;

        return abs($this->monthAndDay - $leonidPeakDay) <= 1;
    }

    public function isChineseNewYear(): bool
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

        if($this->isStockingStuffingSeason())
            $events[] = HolidayEnum::STOCKING_STUFFING_SEASON;

        if($this->isHalloween())
            $events[] = HolidayEnum::HALLOWEEN;

        if($this->isEaster())
            $events[] = HolidayEnum::EASTER;

        if($this->isSaintPatricksDay())
            $events[] = HolidayEnum::SAINT_PATRICKS;

        if($this->isValentinesOrAdjacent())
            $events[] = HolidayEnum::VALENTINES;

        if($this->isCyberMonday())
            $events[] = HolidayEnum::CYBER_MONDAY;

        if($this->isBlackFriday())
            $events[] = HolidayEnum::BLACK_FRIDAY;

        if($this->isPiDay())
            $events[] = HolidayEnum::PI_DAY;

        if($this->isPSPBirthday())
            $events[] = HolidayEnum::PSP_BIRTHDAY;

        if($this->isSummerSolstice())
            $events[] = HolidayEnum::SUMMER_SOLSTICE;

        if($this->isWinterSolstice())
            $events[] = HolidayEnum::WINTER_SOLSTICE;

        if($this->isPsyPetsBirthday())
            $events[] = HolidayEnum::PSYPETS_BIRTHDAY;

        if($this->isAprilFools())
            $events[] = HolidayEnum::APRIL_FOOLS;

        if($this->isHanukkah())
            $events[] = HolidayEnum::HANUKKAH;

        if($this->isWhiteDay())
            $events[] = HolidayEnum::WHITE_DAY;

        if($this->isTalkLikeAPirateDay())
            $events[] = HolidayEnum::TALK_LIKE_A_PIRATE_DAY;

        if($this->isThanksgiving())
            $events[] = HolidayEnum::THANKSGIVING;

        if($this->isNewYearsHoliday())
            $events[] = HolidayEnum::NEW_YEARS_DAY;

        if($this->isJuly4th())
            $events[] = HolidayEnum::FOURTH_OF_JULY;

        if($this->isBastilleDay())
            $events[] = HolidayEnum::BASTILLE_DAY;

        if($this->isAwaOdori())
            $events[] = HolidayEnum::AWA_ODORI;

        if($this->isEarthDay())
            $events[] = HolidayEnum::EARTH_DAY;

        if($this->isCincoDeMayo())
            $events[] = HolidayEnum::CINCO_DE_MAYO;

        if($this->isNoombatDay())
            $events[] = HolidayEnum::NOOMBAT_DAY;

        if($this->isJelephantDay())
            $events[] = HolidayEnum::JELEPHANT_DAY;

        if($this->isChineseNewYear())
            $events[] = HolidayEnum::CHINESE_NEW_YEAR;

        if($this->isHoli())
            $events[] = HolidayEnum::HOLI;

        if($this->isLeonidPeakOrAdjacent())
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
