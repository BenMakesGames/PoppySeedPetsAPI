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


namespace App\Controller\Florist;

use App\Entity\UserStats;
use App\Enum\UnlockableFeatureEnum;
use App\Enum\UserStat;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\FloristService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/florist")]
class GetShopInventoryController
{
    #[Route("", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getInventory(
        FloristService $floristService, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Florist))
            throw new PSPNotUnlockedException('Florist');

        $hasRolledSatyrDice = $em->getRepository(UserStats::class)->findOneBy([
            'user' => $user,
            'stat' => UserStat::RolledSatyrDice
        ]);

        return $responseService->success([
            'inventory' => $floristService->getInventory($user),
            'canTradeForGiftPackage' => $hasRolledSatyrDice !== null && $hasRolledSatyrDice->getValue() > 0
        ]);
    }
}
