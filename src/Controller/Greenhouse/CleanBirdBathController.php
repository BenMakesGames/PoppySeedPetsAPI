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

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ArrayFunctions;
use App\Functions\PlayerLogFactory;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/greenhouse")]
class CleanBirdBathController
{
    #[Route("/cleanBirdBath", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function cleanBirdBath(
        ResponseService $responseService, EntityManagerInterface $em, InventoryService $inventoryService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->getGreenhouse())
            throw new PSPNotUnlockedException('Greenhouse');

        if(!$user->getGreenhouse()->getHasBirdBath())
            throw new PSPNotUnlockedException('Bird Bath');

        /** @var Inventory[] $itemsInBirdBath */
        $itemsInBirdBath = $em->getRepository(Inventory::class)->createQueryBuilder('i')
            ->andWhere('i.owner=:owner')
            ->andWhere('i.location=:location')
            ->setParameter('owner', $user->getId())
            ->setParameter('location', LocationEnum::BirdBath)
            ->getQuery()
            ->getResult();

        if(count($itemsInBirdBath) === 0)
            throw new PSPInvalidOperationException('There\'s nothing to clean!');

        $itemsAtHome = $inventoryService->countItemsInLocation($user, LocationEnum::Home);

        if($itemsAtHome >= User::MaxHouseInventory)
            throw new PSPInvalidOperationException('You don\'t have enough room in your house for all these items!');

        // +1 item, because the items come in pairs (oil or bubblegum + a shed item), and we don't want to leave a lone shed item
        if($itemsAtHome + count($itemsInBirdBath) > User::MaxHouseInventory + 1)
            $itemsToTake = array_slice($itemsInBirdBath, 0, User::MaxHouseInventory - $itemsAtHome);
        else
            $itemsToTake = $itemsInBirdBath;

        $itemsRemaining = count($itemsInBirdBath) - count($itemsToTake);

        foreach($itemsToTake as $item)
            $item->setLocation(LocationEnum::Home);

        $itemNames = array_map(fn(Inventory $i) => $i->getItem()->getName(), $itemsToTake);

        $message = 'You cleaned the bird bath, and found ' . ArrayFunctions::list_nice($itemNames) . '!';

        if($itemsRemaining > 0)
            $message .= ' (There\'s still ' . $itemsRemaining . ' more ' . ($itemsRemaining == 1 ? 'thing' : 'things') . ' on/in there, but your house is already full!)';

        PlayerLogFactory::create($em, $user, $message, [ 'Greenhouse', 'Birdbath' ]);

        $em->flush();

        $responseService->addFlashMessage($message);

        return $responseService->success();
    }
}
