<?php
declare(strict_types=1);

namespace App\Controller\Weather;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Model\AvailableHolidayBox;
use App\Service\PlazaService;
use App\Service\ResponseService;
use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/weather")]
class GetForecastController extends AbstractController
{
    #[Route("/forecast", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getForecast(
        ResponseService $responseService, WeatherService $weatherService, PlazaService $plazaService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $data = [
            'forecast' => $weatherService->get6DayForecast(),
            'holidayBoxes' => array_map(
                fn(AvailableHolidayBox $box) => $box->nameWithQuantity,
                $plazaService->getAvailableHolidayBoxes($user)
            )
        ];

        return $responseService->success($data, [ SerializationGroupEnum::WEATHER ]);
    }
}