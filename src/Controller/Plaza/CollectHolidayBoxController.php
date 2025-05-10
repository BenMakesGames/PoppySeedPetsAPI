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


namespace App\Controller\Plaza;

use App\Enum\LocationEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Functions\ArrayFunctions;
use App\Model\AvailableHolidayBox;
use App\Service\InventoryService;
use App\Service\MuseumService;
use App\Service\PlazaService;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\UserAccessor;

#[Route("/plaza")]
class CollectHolidayBoxController
{
    #[Route("/collectHolidayBox", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function collectHolidayBox(
        Request $request, PlazaService $plazaService, MuseumService $museumService,
        InventoryService $inventoryService, EntityManagerInterface $em, ResponseService $responseService,
        UserStatsService $userStatsRepository,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $availableBoxes = $plazaService->getAvailableHolidayBoxes($user);
        $requestedBox = $request->request->get('box');

        /** @var AvailableHolidayBox|null $box */
        $box = ArrayFunctions::find_one($availableBoxes, fn(AvailableHolidayBox $box) => $box->nameWithQuantity === $requestedBox);

        if(!$box)
            throw new PSPInvalidOperationException('No holiday box is available right now...');

        if($box->userQuestEntity)
            $box->userQuestEntity->setValue(true);

        if(str_contains($box->itemName, 'Box') || str_contains($box->itemName, 'Bag'))
            $userStatsRepository->incrementStat($user, UserStatEnum::PLAZA_BOXES_RECEIVED, $box->quantity);

        for($i = 0; $i < $box->quantity; $i++)
            $inventoryService->receiveItem($box->itemName, $user, $user, $box->comment, LocationEnum::HOME, true);

        $museumService->forceDonateItem($user, $box->itemName, 'Tess donated this to the Museum on your behalf.', null);

        $em->flush();

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Museum))
            $responseService->addFlashMessage('Here you go: your ' . $box->tradeDescription . '! (I\'ve also donated one to the Museum on your behalf!)');
        else
            $responseService->addFlashMessage('Here you go: your ' . $box->tradeDescription . '!');

        return $responseService->success(array_map(
            fn(AvailableHolidayBox $box) => $box->tradeDescription,
            $plazaService->getAvailableHolidayBoxes($user)
        ));
    }
}
