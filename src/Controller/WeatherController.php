<?php
namespace App\Controller;

use App\Enum\SerializationGroupEnum;
use App\Model\AvailableHolidayBox;
use App\Service\PlazaService;
use App\Service\ResponseService;
use App\Service\WeatherService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/weather")
 */
class WeatherController extends PoppySeedPetsController
{
    /**
     * @Route("/forecast", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getForecast(
        ResponseService $responseService, WeatherService $weatherService, PlazaService $plazaService
    )
    {
        $user = $this->getUser();

        $data = [
            'forecast' => $weatherService->get7DayForecast(),
            'holidayBoxes' => array_map(
                function(AvailableHolidayBox $box) { return $box->tradeDescription; },
                $plazaService->getAvailableHolidayBoxes($user)
            )
        ];

        return $responseService->success($data, [ SerializationGroupEnum::WEATHER ]);
    }
}