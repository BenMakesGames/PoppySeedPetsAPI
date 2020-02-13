<?php
namespace App\Service;

use App\Entity\User;
use App\Functions\JewishCalendarFunctions;
use App\Service\Holidays\HalloweenService;

class CalendarService
{
    private $today;
    private $monthAndDay;
    private $halloweenService;
    private $jewishCalendarService;

    public function __construct(HalloweenService $halloweenService)
    {
        $this->today = new \DateTimeImmutable();
        $this->monthAndDay = (int)$this->today->format('md');
        $this->halloweenService = $halloweenService;
    }

    public function getMonthAndDay(): int
    {
        return $this->monthAndDay;
    }

    public function isThanksgiving(): bool
    {
        // if it's not November, just get outta' here
        if($this->monthAndDay < 1100 || $this->monthAndDay >= 1200)
            return false;

        $fourthThursdayOfNovember = new \DateTimeImmutable('fourth Thursday of this month');

        // within 1 day of thanksgiving
        return abs((int)$fourthThursdayOfNovember->format('md') - $this->monthAndDay) <= 2;
    }

    public function isHalloween(): bool
    {
        return $this->monthAndDay >= 1029 && $this->monthAndDay <= 1031;
    }

    public function isPiDayOrWhiteDay(): bool
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

    public function isHannukah(): bool
    {
        $jdCurrent = gregoriantojd($this->today->format('m'), $this->today->format('d'), $this->today->format('Y'));
        list($jewishYear, $jewishMonth, $jewishDay) = JewishCalendarFunctions::getJewishDate($this->today);

        $hanukkahStart = jewishtojd(JewishCalendarFunctions::KISLEV, 25, $jewishYear);
        $hanukkahNo = (int)($jdCurrent - $hanukkahStart + 1);

        return $hanukkahNo >= 1 && $hanukkahNo <= 8;
    }

    public function isValentines(): bool
    {
        return $this->monthAndDay === 214;
    }

    public function isEaster(): bool
    {
        $easter = \DateTimeImmutable::createFromFormat('U', easter_date((int)$this->today->format('Y')));
        $diff = $easter->diff($this->today)->days;

        return $diff >= 0 && $diff < 3;
    }

    public function getEventData(?User $user): ?array
    {
        if(!$user)
            return null;

        if($this->isHalloween())
        {
            return [
                'title' => 'Halloween',
                'nextTrickOrTreater' => $this->halloweenService->getNextTrickOrTreater($user)->getValue()
            ];
        }

        if($this->isEaster())
        {
            return [
                'title' => 'Easter'
            ];
        }

        return null;
    }
}
