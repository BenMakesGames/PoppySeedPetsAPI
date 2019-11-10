<?php
namespace App\Service;

use App\Entity\User;
use App\Service\Holidays\HalloweenService;

class CalendarService
{
    private $today;
    private $monthAndDay;
    private $halloweenService;

    public function __construct(HalloweenService $halloweenService)
    {
        $this->today = new \DateTimeImmutable();
        $this->monthAndDay = (int)$this->today->format('md');
        $this->halloweenService = $halloweenService;
    }

    public function isThanksgiving(): bool
    {
        // if it's not November, just get outta' here
        if($this->monthAndDay < 1100 || $this->monthAndDay >= 1200)
            return false;

        $fourthThursdayOfNovember = new \DateTimeImmutable('fourth Thursday of this month');

        // within 1 day of thanksgiving
        return abs((int)$fourthThursdayOfNovember->format('md') - $this->monthAndDay) <= 1;
    }

    public function isHalloween(): bool
    {
        return $this->monthAndDay >= 1029 && $this->monthAndDay <= 1031;
    }

    public function isPiDay(): bool
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

        return null;
    }
}