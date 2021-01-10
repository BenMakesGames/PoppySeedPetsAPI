<?php
namespace App\Service;

use App\Entity\User;
use App\Model\AvailableHolidayBox;
use App\Repository\UserQuestRepository;

class PlazaService
{
    private $calendarService;
    private $chineseCalendarInfo;
    private $userQuestRepository;

    public function __construct(CalendarService $calendarService, UserQuestRepository $userQuestRepository)
    {
        $this->calendarService = $calendarService;
        $this->userQuestRepository = $userQuestRepository;

        $this->chineseCalendarInfo = $calendarService->getChineseCalendarInfo();
    }

    public function getAvailableHolidayBoxes(User $user)
    {
        $boxes = [];

        $now = new \DateTimeImmutable();

        $month = (int)$now->format('m');
        $day = (int)$now->format('d');

        if($this->chineseCalendarInfo->month === 1 && $this->chineseCalendarInfo->day <= 6)
        {
            $gotBox = $this->userQuestRepository->findOrCreate($user, 'Chinese New Year, ' . $this->chineseCalendarInfo->year, false);

            if(!$gotBox->getValue())
            {
                $boxes[] = new AvailableHolidayBox(
                    'Chinese New Year Box',
                    'Received for the ' . $this->chineseCalendarInfo->year . ' Chinese New Year.',
                    $gotBox
                );
            }
        }

        if($month === 7 && $day >= 3 && $day <= 5)
        {
            $gotBox = $this->userQuestRepository->findOrCreate($user, '4th of July, ' . $now->format('Y'), false);

            if(!$gotBox->getValue())
            {
                $boxes[] = new AvailableHolidayBox(
                    '4th of July Box',
                    'Received on the ' . $now->format('jS') . ' of July, ' . $now->format('Y') . '.',
                    $gotBox
                );
            }
        }
        else if(($month === 12 && $day === 31) || ($month === 1 && $day <= 2))
        {
            $year = $month === 12 ? ((int)$now->format('Y') + 1) : (int)$now->format('Y');

            $gotBox = $this->userQuestRepository->findOrCreate($user, 'New Year, ' . $year, false);

            if(!$gotBox->getValue())
            {
                $boxes[] = new AvailableHolidayBox(
                    'New Year Box',
                    'Received on the ' . $now->format('jS') . ' of ' . $now->format('F') . ', ' . $now->format('Y') . '.',
                    $gotBox
                );
            }
        }

        return $boxes;
    }
}
