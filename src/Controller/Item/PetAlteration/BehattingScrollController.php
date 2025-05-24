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
use App\Enum\MeritEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\MeritRepository;
use App\Functions\PetActivityLogFactory;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/behattingScroll")]
class BehattingScrollController
{
    #[Route("/{inventory}/read", methods: ["PATCH"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function readBehattingScroll(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        IRandom $rng, UserStatsService $userStatsRepository,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'behattingScroll');

        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if($pet->hasMerit(MeritEnum::BEHATTED))
            throw new PSPInvalidOperationException($pet->getName() . ' already has the Behatted Merit!');

        $merit = MeritRepository::findOneByName($em, MeritEnum::BEHATTED);

        $userStatsRepository->incrementStat($user, UserStatEnum::ReadAScroll);

        $em->remove($inventory);

        $pet->addMerit($merit);

        $adjective = $rng->rngNextFromArray([
            'awe-inspiring', 'incredible', 'breathtaking', 'amazing', 'fabulous'
        ]);

        PetActivityLogFactory::createUnreadLog($em, $pet, '%pet:' . $pet->getId() . '.name% was granted the ' . $adjective . ' power to wear <i class="fa-solid fa-hat-beach"></i>s!')
            ->setIcon('items/scroll/behatting');

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
