<?php
namespace App\Service;

use App\Entity\User;
use App\Model\AvailableHolidayBox;
use App\Model\ChineseCalendarInfo;
use App\Repository\UserQuestRepository;

class PlazaService
{
    private CalendarService $calendarService;
    private ChineseCalendarInfo $chineseCalendarInfo;
    private UserQuestRepository $userQuestRepository;

    public function __construct(CalendarService $calendarService, UserQuestRepository $userQuestRepository)
    {
        $this->calendarService = $calendarService;
        $this->userQuestRepository = $userQuestRepository;

        $this->chineseCalendarInfo = $calendarService->getChineseCalendarInfo();
    }

    /**
     * @param User $user
     * @return AvailableHolidayBox[]
     */
    public function getAvailableHolidayBoxes(User $user): array
    {
        $boxes = [];

        $now = new \DateTimeImmutable();

        $year = (int)$now->format('Y');
        $month = (int)$now->format('m');
        $day = (int)$now->format('d');

        if($this->chineseCalendarInfo->month === 1 && $this->chineseCalendarInfo->day <= 6)
        {
            $gotBox = $this->userQuestRepository->findOrCreate($user, 'Chinese New Year, ' . $this->chineseCalendarInfo->year, false);

            if(!$gotBox->getValue())
            {
                $boxes[] = new AvailableHolidayBox(
                    'a Chinese New Year Box',
                    'Chinese New Year Box',
                    'Chinese New Year Box', 1,
                    'Received for the ' . $this->chineseCalendarInfo->year . ' Chinese New Year.',
                    $gotBox
                );
            }
        }

        if($this->calendarService->isEarthDay())
        {
            $gotEarthDaySeed = $this->userQuestRepository->findOrCreate($user, 'Earth Day, ' . $year, false);

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

        if($this->calendarService->isSummerSolstice())
        {
            $gotGoodieBagsThisYear = $this->userQuestRepository->findOrCreate($user, 'Summer Solstice, ' . $year, false);

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
        else if($this->calendarService->isWinterSolstice())
        {
            $gotGoodieBagsThisYear = $this->userQuestRepository->findOrCreate($user, 'Winter Solstice, ' . $year, false);

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


        if($this->calendarService->isJuly4th())
        {
            $gotBox = $this->userQuestRepository->findOrCreate($user, '4th of July, ' . $year, false);

            if(!$gotBox->getValue())
            {
                $boxes[] = new AvailableHolidayBox(
                    'a 4th of July Box',
                    '4th of July Box',
                    '4th of July Box', 1,
                    'Received on the ' . $now->format('jS') . ' of July, ' . $year . '.',
                    $gotBox
                );
            }
        }
        else if($this->calendarService->isBastilleDay())
        {
            $gotBox = $this->userQuestRepository->findOrCreate($user, 'Bastille Day, ' . $year, false);

            if(!$gotBox->getValue())
            {
                $boxes[] = new AvailableHolidayBox(
                    'a Bastille Day Box',
                    'Bastille Day Box',
                    'Bastille Day Box', 1,
                    'Received on the ' . $now->format('jS') . ' of July, ' . $year . '.',
                    $gotBox
                );
            }
        }
        else if($this->calendarService->isCincoDeMayo())
        {
            $gotBox = $this->userQuestRepository->findOrCreate($user, 'Cinco de Mayo, ' . $year, false);

            if(!$gotBox->getValue())
            {
                $boxes[] = new AvailableHolidayBox(
                    'a Cinco de Mayo Box',
                    'Cinco de Mayo Box',
                    'Cinco de Mayo Box', 1,
                    'Received on the ' . $now->format('jS') . ' of May, ' . $year . '.',
                    $gotBox
                );
            }
        }
        else if($this->calendarService->isNewYearsHoliday())
        {
            $newYearYear = $month === 12 ? ($year + 1) : $year;

            $gotBox = $this->userQuestRepository->findOrCreate($user, 'New Year, ' . $newYearYear, false);

            if(!$gotBox->getValue())
            {
                $boxes[] = new AvailableHolidayBox(
                    'a New Year Box',
                    'New Year Box',
                    'New Year Box', 1,
                    'Received on the ' . $now->format('jS') . ' of ' . $now->format('F') . ', ' . $year . '.',
                    $gotBox
                );
            }
        }

        return $boxes;
    }
}
