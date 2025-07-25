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
use App\Enum\PetLocationEnum;
use App\Enum\UserStat;
use App\Service\HouseService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/magicHourglass")]
class MagicHourglassController
{
    #[Route("/{inventory}/shatter", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function shatter(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        HouseService $houseService, UserStatsService $userStatsRepository, EntityManagerInterface $em,
        IRandom $rng, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'magicHourglass/#/shatter');

        if($inventory->getLocation() !== LocationEnum::Home)
        {
            return $responseService->success('Somehow you get the feeling that your pets would like to watch this happen.');
        }

        $inventoryService->receiveItem('Aging Powder', $user, $user, $user->getName() . ' smashed a ' . $inventory->getItem()->getName() . ', spilling what was once Silica Grounds on the floor.', $inventory->getLocation());

        $message = 'Crazy-magic energies flow through the house, swirling and dancing with chaotic shapes that you\'re pretty sure are fractal in nature.' . "\n\n" . 'Also, the Silica Grounds inside - now reduced to Aging Powder - spill all over the ground.';

        if($rng->rngNextInt(1, 8) === 1)
            $message .= ' Way to go.';

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStat::MagicHourglassesSmashed);

        $query = $em->createQuery('
            UPDATE App\Entity\PetHouseTime AS ht
            SET ht.activityTime = ht.activityTime + 600
            WHERE
                ht.activityTime < 4320
                AND ht.pet IN (
                    SELECT p.id FROM App\Entity\Pet AS p
                    WHERE p.owner=:ownerId
                    AND p.location=:home
                )
        ');

        $query->execute([
            'ownerId' => $user->getId(),
            'home' => PetLocationEnum::HOME
        ]);

        $em->flush();

        $houseService->run($user);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
