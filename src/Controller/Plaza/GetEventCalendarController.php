<?php
declare(strict_types=1);

namespace App\Controller\Plaza;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Functions\CalendarFunctions;
use App\Service\CacheHelper;
use App\Service\Clock;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/plaza")]
class GetEventCalendarController extends AbstractController
{
    #[Route("/eventCalendar", methods: ["GET"])]
    public function getCalendar(
        ResponseService $responseService, Clock $clock, CacheHelper $cacheHelper
    )
    {
        $cacheKey = 'EventCalendar-' . $clock->now->format('Y-m');

        $eventCalendar = $cacheHelper->getOrCompute($cacheKey, \DateInterval::createFromDateString('1 month'), function() use ($clock) {
            $endMonth = (int)(((int)$clock->now->format('Y') + 1) . $clock->now->format('m'));
            $today = \DateTimeImmutable::createFromFormat('Y-m-d', $clock->now->format('Y-m') . '-01');

            $currentYear = 0;
            $currentMonth = 0;
            $years = [];
            $oneDay = \DateInterval::createFromDateString('1 day');

            while($today->format('Ym') < $endMonth)
            {
                if($today->format('Y') !== $currentYear)
                {
                    $currentYear = $today->format('Y');
                    $currentMonth = 0;

                    $years[] = [ 'year' => $currentYear, 'months' => [] ];
                }

                if($today->format('n') !== $currentMonth)
                {
                    $currentMonth = $today->format('n');

                    $years[count($years) - 1]['months'][] = [
                        'month' => $today->format('F'),
                        'days' => []
                    ];
                }

                $years[count($years) - 1]['months'][count($years[count($years) - 1]['months']) - 1]['days'][] = [
                    'dayOfWeek' => $today->format('N'),
                    'date' => $today->format('Y-m-d'),
                    'holidays' => CalendarFunctions::getEventData($today),
                ];

                $today = $today->add($oneDay);
            }

            return $years;
        });

        return $responseService->success([
            'today' => $clock->now->format('Y-m-d'),
            'years' => $eventCalendar,
        ]);
    }
}
