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
use App\Entity\User;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/twilightFertilizer")]
class TwilightFertilizerController extends AbstractController
{
    #[Route("/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function useItem(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'twilightFertilizer/#');

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Greenhouse))
            throw new PSPNotUnlockedException('Greenhouse');

        if($user->getGreenhouse()->getMaxDarkPlants() >= 2)
            throw new PSPInvalidOperationException('There\'s nowhere else to put the fertilizer!');

        $user->getGreenhouse()->increaseMaxDarkPlants(1);

        $em->remove($inventory);
        $em->flush();

        if($user->getGreenhouse()->getMaxDarkPlants() === 1)
            return $responseService->itemActionSuccess('You lay down the fertilizer in a dark corner of the Greenhouse. (Is that "summoning the night"? Sure. Why not.)', [ 'itemDeleted' => true ]);
        else
            return $responseService->itemActionSuccess('You lay down the fertilizer in a dark corner of the Greenhouse.', [ 'itemDeleted' => true ]);
    }
}
