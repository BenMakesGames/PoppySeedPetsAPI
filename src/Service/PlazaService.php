<?php
namespace App\Service;

use App\Entity\User;
use App\Model\AvailableHolidayBox;
use App\Repository\ItemRepository;
use App\Repository\UserQuestRepository;

class PlazaService
{
    private $calendarService;
    private $chineseCalendarInfo;
    private $userQuestRepository;
    private $itemRepository;

    public function __construct(
        CalendarService $calendarService, UserQuestRepository $userQuestRepository, ItemRepository $itemRepository
    )
    {
        $this->calendarService = $calendarService;
        $this->userQuestRepository = $userQuestRepository;
        $this->itemRepository = $itemRepository;

        $this->chineseCalendarInfo = $calendarService->getChineseCalendarInfo();
    }

    public function getAvailableHolidayBoxes(User $user)
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
                    'Chinese New Year Box',
                    'Chinese New Year Box',
                    'Received for the ' . $this->chineseCalendarInfo->year . ' Chinese New Year.',
                    $gotBox,
                    null
                );
            }
        }

        if($this->calendarService->isJuly4th())
        {
            $gotBox = $this->userQuestRepository->findOrCreate($user, '4th of July, ' . $year, false);

            if(!$gotBox->getValue())
            {
                $boxes[] = new AvailableHolidayBox(
                    '4th of July Box',
                    '4th of July Box',
                    'Received on the ' . $now->format('jS') . ' of July, ' . $year . '.',
                    $gotBox,
                    null
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
                    'New Year Box',
                    'New Year Box',
                    'Received on the ' . $now->format('jS') . ' of ' . $now->format('F') . ', ' . $year . '.',
                    $gotBox,
                    null
                );
            }
        }

        return $boxes;
    }
}
