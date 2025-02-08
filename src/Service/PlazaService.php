<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Functions\CalendarFunctions;
use App\Functions\UserQuestRepository;
use App\Model\AvailableHolidayBox;
use App\Model\ChineseCalendarInfo;
use Doctrine\ORM\EntityManagerInterface;

class PlazaService
{
    private ChineseCalendarInfo $chineseCalendarInfo;

    public function __construct(
        private readonly Clock $clock,
        private readonly EntityManagerInterface $em
    )
    {
        $this->chineseCalendarInfo = CalendarFunctions::getChineseCalendarInfo($clock->now);
    }

    /**
     * @return AvailableHolidayBox[]
     */
    public function getAvailableHolidayBoxes(User $user): array
    {
        $boxes = [];

        $year = (int)$this->clock->now->format('Y');
        $month = (int)$this->clock->now->format('m');
        $day = (int)$this->clock->now->format('d');

        if($this->chineseCalendarInfo->month === 1 && $this->chineseCalendarInfo->day <= 6)
        {
            $gotBox = UserQuestRepository::findOrCreate($this->em, $user, 'Lunar New Year, ' . $this->chineseCalendarInfo->year, false);

            if(!$gotBox->getValue())
            {
                $boxes[] = new AvailableHolidayBox(
                    'a Lunar New Year Box',
                    'Lunar New Year Box',
                    'Lunar New Year Box', 1,
                    'Received for the ' . $this->chineseCalendarInfo->year . ' Lunar New Year.',
                    $gotBox
                );
            }
        }

        if(CalendarFunctions::isEarthDay($this->clock->now))
        {
            $gotEarthDaySeed = UserQuestRepository::findOrCreate($this->em, $user, 'Earth Day, ' . $year, false);

            if(!$gotEarthDaySeed->getValue())
            {
                $boxes[] = new AvailableHolidayBox(
                    'two Earth Tree Seeds',
                    'two Earth Tree Seeds',
                    'Earth Tree Seed', 2,
                    'Received for Earth Day, ' . $year . '.',
                    $gotEarthDaySeed
                );
            }
        }

        if(CalendarFunctions::isSummerSolstice($this->clock->now))
        {
            $gotGoodieBagsThisYear = UserQuestRepository::findOrCreate($this->em, $user, 'Summer Solstice, ' . $year, false);

            if(!$gotGoodieBagsThisYear->getValue())
            {
                $boxes[] = new AvailableHolidayBox(
                    'two Summer Goodie Bags',
                    'two Summer Goodie Bags',
                    'Summer Goodie Bag', 2,
                    'Received for Summer Solstice, ' . $year . '.',
                    $gotGoodieBagsThisYear
                );
            }
        }
        else if(CalendarFunctions::isWinterSolstice($this->clock->now))
        {
            $gotGoodieBagsThisYear = UserQuestRepository::findOrCreate($this->em, $user, 'Winter Solstice, ' . $year, false);

            if(!$gotGoodieBagsThisYear->getValue())
            {
                $boxes[] = new AvailableHolidayBox(
                    'two Winter Goodie Bags',
                    'two Winter Goodie Bags',
                    'Winter Goodie Bag', 2,
                    'Received for Winter Solstice, ' . $year . '.',
                    $gotGoodieBagsThisYear
                );
            }
        }

        if(CalendarFunctions::isJuly4th($this->clock->now))
        {
            $gotBox = UserQuestRepository::findOrCreate($this->em, $user, '4th of July, ' . $year, false);

            if(!$gotBox->getValue())
            {
                $boxes[] = new AvailableHolidayBox(
                    'a 4th of July Box',
                    '4th of July Box',
                    '4th of July Box', 1,
                    'Received on the ' . $this->clock->now->format('jS') . ' of July, ' . $year . '.',
                    $gotBox
                );
            }
        }

        if(CalendarFunctions::isEight($this->clock->now))
        {
            $got8 = UserQuestRepository::findOrCreate($this->em, $user, 'EIGHT, ' . $year, false);

            if(!$got8->getValue())
            {
                $boxes[] = new AvailableHolidayBox(
                    'an 8',
                    '8',
                    '8', 1,
                    'Received on the ' . $this->clock->now->format('jS') . ' of December, ' . $year . '.',
                    $got8
                );
            }
        }

        if(CalendarFunctions::isBastilleDay($this->clock->now))
        {
            $gotBox = UserQuestRepository::findOrCreate($this->em, $user, 'Bastille Day, ' . $year, false);

            if(!$gotBox->getValue())
            {
                $boxes[] = new AvailableHolidayBox(
                    'a Bastille Day Box',
                    'Bastille Day Box',
                    'Bastille Day Box', 1,
                    'Received on the ' . $this->clock->now->format('jS') . ' of July, ' . $year . '.',
                    $gotBox
                );
            }
        }

        if(CalendarFunctions::isCincoDeMayo($this->clock->now))
        {
            $gotBox = UserQuestRepository::findOrCreate($this->em, $user, 'Cinco de Mayo, ' . $year, false);

            if(!$gotBox->getValue())
            {
                $boxes[] = new AvailableHolidayBox(
                    'a Cinco de Mayo Box',
                    'Cinco de Mayo Box',
                    'Cinco de Mayo Box', 1,
                    'Received on the ' . $this->clock->now->format('jS') . ' of May, ' . $year . '.',
                    $gotBox
                );
            }
        }

        if(CalendarFunctions::isNewYearsHoliday($this->clock->now))
        {
            $newYearYear = $month === 12 ? ($year + 1) : $year;

            $gotBox = UserQuestRepository::findOrCreate($this->em, $user, 'New Year, ' . $newYearYear, false);

            if(!$gotBox->getValue())
            {
                $boxes[] = new AvailableHolidayBox(
                    'a New Year Box',
                    'New Year Box',
                    'New Year Box', 1,
                    'Received on the ' . $this->clock->now->format('jS') . ' of ' . $this->clock->now->format('F') . ', ' . $year . '.',
                    $gotBox
                );
            }
        }

        if(CalendarFunctions::isAwaOdori($this->clock->now))
        {
            $gotBox = UserQuestRepository::findOrCreate($this->em, $user, 'Awa Odori, ' . $year, false);

            if(!$gotBox->getValue())
            {
                $boxes[] = new AvailableHolidayBox(
                    'an Awa Odori Box',
                    'Awa Odori Box',
                    'Awa Odori Box', 1,
                    'Received on the ' . $this->clock->now->format('jS') . ' of August, ' . $year . '.',
                    $gotBox
                );
            }
        }

        return $boxes;
    }
}
