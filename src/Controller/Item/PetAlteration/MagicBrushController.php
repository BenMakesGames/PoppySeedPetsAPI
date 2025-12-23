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

namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\MeritRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/magicBrush")]
class MagicBrushController
{
    #[Route("/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function brush(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        InventoryService $inventoryService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'magicBrush');

        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if($pet->hasMerit(MeritEnum::SHEDS))
            throw new PSPInvalidOperationException($pet->getName() . ' already sheds!');

        $pet->addMerit(MeritRepository::findOneByName($em, MeritEnum::SHEDS));

        $item = $pet->getSpecies()->getSheds();

        $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this by brushing ' . $pet->getName() . ' with a Magic Brush.', LocationEnum::Home, false);

        $em->remove($inventory);
        $em->flush();

        $plural = strtolower(mb_substr($item->getName(), -1, 1)) === 's';

        $responseService->addFlashMessage('You brush ' . $pet->getName() . ', and some ' . $item->getName() . ' ' . ($plural ? 'come' : 'comes') . ' off! (They now Shed!) Also, the magic brush breaks in half and disappears! (It wasn\'t your fault; Magic Brushes just be like that.)');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true, 'reloadInventory' => true ]);
    }
}
