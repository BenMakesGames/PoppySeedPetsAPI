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

use App\Entity\Dragon;
use App\Entity\Inventory;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\PetColorFunctions;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/spicyKonpeito")]
class SpicyKonpeitoController
{
    #[Route("/{inventory}/give", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function feedToDragon(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        PetColorFunctions $petColorChangingService, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'spicyKonpeito/#/give');

        $dragon = $em->getRepository(Dragon::class)->findOneBy([ 'owner' => $user ]);

        if(!$dragon || !$dragon->getIsAdult())
            throw new PSPNotUnlockedException('Dragon Den');

        if(!$inventory->getSpice() || $inventory->getSpice()->getEffects()->getSpicy() === 0)
            throw new PSPInvalidOperationException($dragon->getName() . ' is excited at first, but takes a sniff, and realizes the Konpeitō hasn\'t been properly spiced! (That Konpeitō\'s gotta\'s be SPICY!)');

        $em->remove($inventory);

        $newColorA = $petColorChangingService->randomizeColorDistinctFromPreviousColor($rng, $dragon->getColorA());
        $newColorB = $petColorChangingService->randomizeColorDistinctFromPreviousColor($rng, $dragon->getColorB());

        $dragon
            ->setColorA($newColorA)
            ->setColorB($newColorB)
        ;

        $em->flush();

        $responseService->addFlashMessage($dragon->getName() . '\'s colors have been altered!');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
