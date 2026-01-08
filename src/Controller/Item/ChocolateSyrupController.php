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

namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Functions\EnchantmentRepository;
use App\Service\HattierService;
use App\Service\HollowEarthService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/chocolateSyrup")]
class ChocolateSyrupController
{
    #[Route("/{inventory}/squeeze", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function squeeze(
        Inventory $inventory, ResponseService $responseService, UserAccessor $userAccessor,
        HattierService $hattierService, EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'chocolateSyrup/#/squeeze');

        $cocoa = EnchantmentRepository::findOneByName($em, 'Cocoa');

        if($hattierService->userHasUnlocked($user, $cocoa))
            return $responseService->itemActionSuccess('You already unlocked the Cocoa style - covering yourself in Chocolate Syrup _again_ would just be a waste.');

        $hattierService->playerUnlockAura($user, $cocoa, 'You unlocked this by squeezing a bottle of Chocolate Syrup!');

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('Curious about what might happen, you empty the bottle of Chocolate Syrup all over yourself, and lo: in so doing you\'ve unlocked the Cocoa style at the Hattiers!', [ 'itemDeleted' => true ]);
    }
}
