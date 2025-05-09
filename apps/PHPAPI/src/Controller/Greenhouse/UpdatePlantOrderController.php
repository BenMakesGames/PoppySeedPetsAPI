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


namespace App\Controller\Greenhouse;

use App\Entity\GreenhousePlant;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ArrayFunctions;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/greenhouse")]
class UpdatePlantOrderController
{
    #[Route("/updatePlantOrder", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function updatePlantOrder(
        Request $request, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();
        $greenhouse = $user->getGreenhouse();

        if($greenhouse === null)
            throw new PSPNotUnlockedException('Greenhouse');

        $plantIds = $request->request->all('order');

        $allPlants = $user->getGreenhousePlants();

        $plantIds = array_filter($plantIds, fn(int $i) =>
            ArrayFunctions::any($allPlants, fn(GreenhousePlant $p) => $p->getId() === $i)
        );

        if(count($allPlants) !== count($plantIds))
            throw new PSPFormValidationException('The list of plants must include the full list of your plants; no more; no less!');

        foreach($allPlants as $plant)
        {
            $ordinal = array_search($plant->getId(), $plantIds) + 1;
            $plant->setOrdinal($ordinal);
        }

        $em->flush();

        return $responseService->success();
    }
}
