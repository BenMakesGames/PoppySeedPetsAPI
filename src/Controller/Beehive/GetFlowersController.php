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

namespace App\Controller\Beehive;

use App\Entity\Inventory;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\BeehiveService;
use App\Service\ResponseService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/beehive")]
class GetFlowersController
{
    #[Route("/flowers", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getBeehiveFlowers(
        BeehiveService $beehiveService, ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Beehive) || !$user->getBeehive())
            throw new PSPNotUnlockedException('Beehive');

        $flowers = $beehiveService->findFlowers($user);

        $response = array_map(self::mapFlower(...), $flowers);

        return $responseService->success($response);
    }

    /**
     * @return array<string, mixed>
     */
    private static function mapFlower(Inventory $i): array
    {
        return [
            'id' => $i->getId(),
            'item' => [
                'name' => $i->getItem()->getName(),
                'image' => $i->getItem()->getImage(),
            ],
            'spice' => !$i->getSpice() ? null : [
                'name' => $i->getSpice()->getName(),
                'isSuffix' => $i->getSpice()->getIsSuffix()
            ],
            'illusion' => !$i->getIllusion() ? null : [
                'name' => $i->getIllusion()->getName(),
                'image' => $i->getIllusion()->getImage()
            ],
            'flowerPower' => BeehiveService::computeFlowerPower($i)
        ];
    }
}
