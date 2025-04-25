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

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/plaza")]
class CollectWeeklyCarePackageController
{
    #[Route("/collectWeeklyCarePackage", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function collectWeeklyBox(
        Request $request, EntityManagerInterface $em, ResponseService $responseService,
        InventoryService $inventoryService, UserStatsService $userStatsRepository,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $type = $request->request->getInt('type');

        $days = (new \DateTimeImmutable())->diff($user->getLastAllowanceCollected())->days;

        if($days < 7)
            throw new PSPInvalidOperationException('It\'s too early to collect your weekly Care Package.');

        $itemsDonated = $userStatsRepository->getStatValue($user, UserStatEnum::ITEMS_DONATED_TO_MUSEUM);

        $canGetHandicraftsBox = $itemsDonated >= 100;
        $canGetFishBag = $itemsDonated >= 450;
        $canGetGamingBox = $user->hasUnlockedFeature(UnlockableFeatureEnum::HollowEarth);

        if($type === 1)
        {
            $newInventory = $inventoryService->receiveItem('Fruits & Veggies Box', $user, $user, $user->getName() . ' got this as a weekly Care Package.', LocationEnum::HOME, true);
        }
        else if($type === 2)
        {
            $newInventory = $inventoryService->receiveItem('Baker\'s Box', $user, $user, $user->getName() . ' got this as a weekly Care Package.', LocationEnum::HOME, true);
        }
        else if($type === 3 && $canGetHandicraftsBox)
        {
            $newInventory = $inventoryService->receiveItem('Handicrafts Supply Box', $user, $user, $user->getName() . ' got this as a weekly Care Package.', LocationEnum::HOME, true);
        }
        else if($type === 4 && $canGetGamingBox)
        {
            $newInventory = $inventoryService->receiveItem('Gaming Box', $user, $user, $user->getName() . ' got this as a weekly Care Package.', LocationEnum::HOME, true);
        }
        else if($type === 5 && $canGetFishBag)
        {
            $newInventory = $inventoryService->receiveItem('Fish Bag', $user, $user, $user->getName() . ' got this as a weekly Care... Bag??', LocationEnum::HOME, true);
        }
        else
            throw new PSPFormValidationException('Must specify a Care Package "type".');

        $user->setLastAllowanceCollected($user->getLastAllowanceCollected()->modify('+' . (floor($days / 7) * 7) . ' days'));

        $userStatsRepository->incrementStat($user, UserStatEnum::PLAZA_BOXES_RECEIVED, 1);

        $em->flush();

        return $responseService->success($newInventory, [ SerializationGroupEnum::MY_INVENTORY ]);
    }
}
