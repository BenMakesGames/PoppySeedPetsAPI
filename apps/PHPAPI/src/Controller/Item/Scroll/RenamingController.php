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


namespace App\Controller\Item\Scroll;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\PetRenamingHelpers;
use App\Functions\ProfanityFilterFunctions;
use App\Service\ResponseService;
use App\Service\TransactionService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/renamingScroll")]
class RenamingController
{
    #[Route("/{inventory}/read", methods: ["PATCH"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function readRenamingScroll(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        UserStatsService $userStatsRepository,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'renamingScroll');

        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        PetRenamingHelpers::renamePet($em, $pet, $request->request->getString('name'));

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/readToSpiritCompanion", methods: ["PATCH"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function renameSpiritCompanion(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        UserStatsService $userStatsRepository,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'renamingScroll');

        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if(!$pet->getSpiritCompanion())
            throw new PSPNotFoundException('That pet does not have a spirit companion.');

        PetRenamingHelpers::renameSpiritCompanion($em, $pet->getSpiritCompanion(), $request->request->getString('name'));

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/readToSelf", methods: ["PATCH"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function renameYourself(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        TransactionService $transactionService, UserStatsService $userStatsRepository,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $pointsRemaining = $user->getMuseumPoints() - $user->getMuseumPointsSpent();

        if($pointsRemaining < 500)
            throw new PSPNotEnoughCurrencyException('500 Favor', $pointsRemaining);

        ItemControllerHelpers::validateInventory($user, $inventory, 'renamingScroll');

        $newName = ProfanityFilterFunctions::filter(trim($request->request->getString('name')));

        if($newName === $user->getName())
            throw new PSPInvalidOperationException('That\'s already your name! (What a waste of the scroll that would be...)');

        if(\mb_strlen($newName) < 2 || \mb_strlen($newName) > 30)
            throw new PSPFormValidationException('Name must be between 2 and 30 characters long.');

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $em->remove($inventory);

        $oldName = $user->getName();

        $user->setName($newName);

        $transactionService->spendMuseumFavor($user, 500, 'You renamed yourself, from ' . $oldName . ' to ' . $newName . '!');

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
