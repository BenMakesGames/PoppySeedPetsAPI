<?php
namespace App\Service;

use App\Entity\User;
use App\Functions\JewishCalendarFunctions;
use App\Model\ChineseCalendarInfo;
use App\Service\Holidays\HalloweenService;

class CalendarService
{
    /** @var \DateTimeImmutable */ private $today;
    private $monthAndDay;
    private $halloweenService;

    public function __construct(HalloweenService $halloweenService)
    {
        $this->halloweenService = $halloweenService;

        $this->setToday(new \DateTimeImmutable());
    }

    public function setToday(\DateTimeImmutable $date)
    {
        $this->today = $date;
        $this->monthAndDay = (int)$this->today->format('nd');
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

    public function isWhiteDay(): bool
    {
        return $this->monthAndDay === 314;
    }

    public function isTalkLikeAPirateDay(): bool
    {
        return $this->monthAndDay === 919;
    }

    public function isLeapDay(): bool
    {
        return $this->monthAndDay === 229;
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

    public function isHannukah(): bool
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

    public function isValentines(): bool
    {
        return $this->monthAndDay === 214;
    }

    public function isEaster(): bool
    {
        $easter = \DateTimeImmutable::createFromFormat('U', easter_date((int)$this->today->format('Y')));
        $easter = \DateTimeImmutable::createFromFormat('Y-m-d', $easter->format('Y-m-d'));

        if($this->today > $easter)
            return false;

        $diff = $this->today->diff($easter)->days;

        return $diff < 3;
    }

    /**
     * @return string[]
     */
    public function getEventData(\DateTimeImmutable $dt = null): array
    {
        // uh oh: this is a bit gross :|
        if($dt)
        {
            $oldToday = $this->today;
            $this->setToday($dt);
        }

        $events = [];

        if($this->isHalloween())
            $events[] = 'Halloween';

        if($this->isEaster())
            $events[] = 'Easter';

        if($this->isSaintPatricksDay())
            $events[] = 'Saint Patrick\'s';

        if($this->isValentinesOrAdjacent())
            $events[] = 'Valentine\'s';

        if($this->isBlackFriday())
            $events[] = 'Black Friday';

        if($this->isPiDay())
            $events[] = 'Pi Day';

        if($this->isPSPBirthday())
            $events[] = 'PSP Birthday';

        if($this->isHannukah())
            $events[] ='Hannukah';

        if($this->isWhiteDay())
            $events[] = 'White Day';

        if($this->isCyberMonday())
            $events[] = 'Cyber Monday';

        if($this->isTalkLikeAPirateDay())
            $events[] = 'Talk Like a Pirate Day';

        if($this->isThanksgiving())
            $events[] = 'Thanksgiving';

        if($this->monthAndDay === 101)
            $events[] = 'New Year\'s Day';

        if($this->monthAndDay === 704)
            $events[] = '4th of July';

        $chineseCalendarInfo = $this->getChineseCalendarInfo();

        if($chineseCalendarInfo->month === 1 && $chineseCalendarInfo->day <= 6)
            $events[] = 'Chinese New Year';

        if($dt)
            $this->setToday($oldToday);

        return $events;
    }
}
