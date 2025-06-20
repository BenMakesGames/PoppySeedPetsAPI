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
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ItemRepository;
use App\Functions\UserQuestRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/ceremonyOfSandAndSea")]
class CeremonyOfSandAndSeaController
{
    #[Route("/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function useItem(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'ceremonyOfSandAndSea/#');

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Greenhouse))
            throw new PSPNotUnlockedException('Greenhouse');

        $expandedGreenhouseWithShovel = UserQuestRepository::findOrCreate($em, $user, 'Expanded Greenhouse with Ceremony of Sand and Sea', false);

        if($expandedGreenhouseWithShovel->getValue())
            throw new PSPInvalidOperationException('The sea has already been summoned to your Greenhouse!');

        $expandedGreenhouseWithShovel->setValue(true);

        $user->getGreenhouse()->increaseMaxWaterPlants(2);

        $reloadPets = $inventory->getHolder() || $inventory->getWearer();

        $inventory
            ->changeItem(ItemRepository::findOneByName($em, 'Ceremonial Trident'))
            ->setModifiedOn()
        ;

        $em->flush();
        $responseService->setReloadPets($reloadPets);

        return $responseService->itemActionSuccess('Water pours from the trident, adding two new water plots to your Greenhouse! The trident, having spent its magic, is reduced to a regular ol\' Ceremonial Trident.', [ 'itemDeleted' => true ]);
    }
}
