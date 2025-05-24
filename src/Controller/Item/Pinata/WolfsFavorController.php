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


namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Enum\StatusEffectEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Functions\PetActivityLogFactory;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item")]
class WolfsFavorController
{
    private const string UserStatName = 'Redeemed a Wolf\'s Favor';

    #[Route("/changeWereform/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function changePetWereform(
        Inventory $inventory, ResponseService $responseService, Request $request,
        EntityManagerInterface $em, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'changeWereform');

        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if(!$pet->hasStatusEffect(StatusEffectEnum::Wereform))
            throw new PSPInvalidOperationException('This pet is not in its wereform.');

        $possibleWereforms = [];

        for($i = 0; $i < 6; $i++)
        {
            if($i != $pet->getWereform())
                $possibleWereforms[] = $i;
        }

        $pet->setWereform($rng->rngNextFromArray($possibleWereforms));

        PetActivityLogFactory::createUnreadLog($em, $pet, ActivityHelpers::PetName($pet) . '\'s wereform has changed!');

        $em->remove($inventory);
        $em->flush();

        return $responseService->success();
    }

    #[Route("/wolfsFavor/{inventory}/furAndClaw", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getFluffAndTalons(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng, UserStatsService $userStatsRepository,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'wolfsFavor/#/furAndClaw');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        $loot = [
            'Fluff', 'Fluff', 'Fluff', 'Fluff', 'Fluff', 'Fluff', 'Fluff', 'Fluff',
            'Talon', 'Talon', 'Talon', 'Quintessence',
            $rng->rngNextFromArray([
                'Rib', 'Stereotypical Bone',
            ]),
            $rng->rngNextFromArray([
                'Hot Dog', 'Bulbun Plushy'
            ])
        ];

        foreach($loot as $item)
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from a Wolf\'s Favor.', $location);

        $userStatsRepository->incrementStat($user, self::UserStatName);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('A fluffy pupper drops ' . ArrayFunctions::list_nice($loot) . ' off just outside your door, and bounds off into the distance.', [ 'itemDeleted' => true ]);
    }

    #[Route("/wolfsFavor/{inventory}/theMoon", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getMoonStuff(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, UserStatsService $userStatsRepository,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'wolfsFavor/#/theMoon');

        $location = $inventory->getLocation();

        $loot = [
            'Moon Pearl', 'Moon Pearl', 'Moon Pearl', 'Moon Pearl',
            'Moon Dust', 'Moon Dust',
            'Moth',
            'Meteorite',
        ];

        foreach($loot as $item)
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from a Wolf\'s Favor.', $location);

        $userStatsRepository->incrementStat($user, self::UserStatName);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('A fluffy pupper drops ' . ArrayFunctions::list_nice($loot) . ' off just outside your door, and bounds off into the distance.', [ 'itemDeleted' => true ]);
    }
}
