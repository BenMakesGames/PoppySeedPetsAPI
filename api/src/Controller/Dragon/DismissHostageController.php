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

namespace App\Controller\Dragon;

use App\Enum\LocationEnum;
use App\Exceptions\PSPNotFoundException;
use App\Functions\DragonHelpers;
use App\Functions\PlayerLogFactory;
use App\Service\DragonHostageService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use App\Service\UserAccessor;

#[Route("/dragon")]
class DismissHostageController
{
    #[Route("/dismissHostage", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function dismissHostage(
        ResponseService $responseService, EntityManagerInterface $em, InventoryService $inventoryService,
        DragonHostageService $dragonHostageService, NormalizerInterface $normalizer,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $dragon = DragonHelpers::getAdultDragon($em, $user);

        if(!$dragon || !$dragon->getHostage())
            throw new PSPNotFoundException('You don\'t have a dragon hostage...');

        $hostage = $dragon->getHostage();

        $loot = $dragonHostageService->generateLoot($hostage->getType());

        $em->remove($hostage);
        $dragon->setHostage(null);

        $responseService->addFlashMessage($loot->flashMessage);

        $inventoryService->receiveItem($loot->item, $dragon->getOwner(), $dragon->getOwner(), $loot->comment, LocationEnum::Home, false);

        PlayerLogFactory::create(
            $em,
            $user,
            'You ushered a "hostage" out of your Dragon Den. ' . $loot->flashMessage,
            [ 'Dragon Den' ]
        );

        $em->flush();

        return $responseService->success(DragonHelpers::createDragonResponse($em, $normalizer, $user, $dragon));
    }
}
