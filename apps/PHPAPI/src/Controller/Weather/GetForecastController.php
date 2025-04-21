<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


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
use Symfony\Component\Routing\Attribute\Route;
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