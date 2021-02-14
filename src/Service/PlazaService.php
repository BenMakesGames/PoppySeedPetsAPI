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

        if($year === 2021 && $month === 2 && $day <= 19)
        {
            $boxes[] = new AvailableHolidayBox(
                'Twu Wuv (in exchange for a Wed Bawwoon, of course)',
                'Twu Wuv',
                'Received from Tess, in exchange for a Wed Bawwoon.',
                null,
                $this->itemRepository->findOneByName('Wed Bawwoon')
            );
        }

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

        if($month === 7 && $day >= 3 && $day <= 5)
        {
            $gotBox = $this->userQuestRepository->findOrCreate($user, '4th of July, ' . $now->format('Y'), false);

            if(!$gotBox->getValue())
            {
                $boxes[] = new AvailableHolidayBox(
                    '4th of July Box',
                    '4th of July Box',
                    'Received on the ' . $now->format('jS') . ' of July, ' . $now->format('Y') . '.',
                    $gotBox,
                    null
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
                    'New Year Box',
                    'Received on the ' . $now->format('jS') . ' of ' . $now->format('F') . ', ' . $now->format('Y') . '.',
                    $gotBox,
                    null
                );
            }
        }

        return $boxes;
    }
}
