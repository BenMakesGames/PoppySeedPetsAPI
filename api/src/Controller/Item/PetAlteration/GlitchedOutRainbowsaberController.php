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
use App\Functions\ActivityHelpers;
use App\Functions\ItemRepository;
use App\Functions\MeritRepository;
use App\Functions\PetActivityLogFactory;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/extractHyperchromaticPrism")]
class GlitchedOutRainbowsaberController
{
    #[Route("/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function use(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        UserAccessor $userAccessor, InventoryService $inventoryService
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'extractHyperchromaticPrism');

        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId);

        if (!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if (!$pet->hasMerit(MeritEnum::HYPERCHROMATIC))
            throw new PSPInvalidOperationException($pet->getName() . ' is not Hyperchromatic!');

        // 1. remove merit
        $pet->removeMerit(MeritRepository::findOneByName($em, MeritEnum::HYPERCHROMATIC));
        $responseService->setReloadPets();

        // 2. give Hyperchromatic Prism
        $inventoryService->receiveItem(
            'Hyperchromatic Prism',
            $user,
            $user,
            'This was cut out of ' . $pet->getName() . ' with a Glitched-out Rainbowsaber!',
            LocationEnum::Home,
            $inventory->getLockedToOwner()
        );

        PetActivityLogFactory::createReadLog($em, $pet, ActivityHelpers::PetName($pet) . '\'s Hyperchromaticism was cut out using a Glitched-out Rainbowsaber!');

        // 3. reduce down into an Iron Bar
        $inventory
            ->changeItem(ItemRepository::findOneByName($em, 'Iron Bar'))
            ->addComment('This was once a Glitched-out Rainbowsaber.')
        ;

        $responseService->setReloadInventory();

        $em->flush();

        $responseService->addFlashMessage(
            'The Glitched-out Rainbowsaber hums with a strange frequency, and cuts the chromatic instability out of ' . $pet->getName() . ' in the form of a prism! (Yikes!)' . "\n\n" .
            'The procedure must have required some serious overclocking - the saber gets so hot, you have to drop it. Not long later, the whole thing has melted; little more than an Iron Bar is all that remains...'
        );

        return $responseService->itemActionSuccess(null, ['itemDeleted' => true]);
    }
}
