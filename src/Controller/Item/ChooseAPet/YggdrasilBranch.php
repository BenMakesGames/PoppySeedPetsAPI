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


namespace App\Controller\Item\ChooseAPet;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Merit;
use App\Entity\User;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Functions\PetActivityLogFactory;
use App\Model\PetChanges;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/yggdrasilBranch")]
class YggdrasilBranch
{
    #[Route("/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function useItem(
        Inventory $inventory,
        Request $request,
        ResponseService $responseService,
        EntityManagerInterface $em,
        IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'yggdrasilBranch');

        $pet = ChooseAPetHelpers::getPet($request, $user, $em);
        $petChanges = new PetChanges($pet);

        $randomMerit = $rng->rngNextFromArray([
            MeritEnum::WONDROUS_STRENGTH,
            MeritEnum::WONDROUS_STAMINA,
            MeritEnum::WONDROUS_DEXTERITY,
            MeritEnum::WONDROUS_PERCEPTION,
            MeritEnum::WONDROUS_INTELLIGENCE,
        ]);

        $merit = $em->getRepository(Merit::class)->findOneBy([ 'name' => $randomMerit ]);

        if(!$merit)
            throw new \Exception("Merit not found: {$randomMerit}");

        if($pet->hasMerit($randomMerit))
        {
            $leaves = $rng->rngNextFromArray([
                'melts away',
                'evaporates',
                'dissipates',
                'vanishes'
            ]);

            $itemActionDescription = "ate the fruit of the Yggdrasil Branch, and their {$randomMerit} {$leaves}!";
            $pet->removeMerit($merit);
        }
        else
        {
            $pet->addMerit($merit);
            $itemActionDescription = "ate the fruit of the Yggdrasil Branch, and was blessed with {$randomMerit}!";
        }

        PetActivityLogFactory::createReadLog($em, $pet, "%pet:{$pet->getId()}.name% {$itemActionDescription}")
            ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE)
            ->setChanges($petChanges->compare($pet))
        ;

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess(
            "{$pet->getName()} {$itemActionDescription}!",
            [ 'itemDeleted' => true ]
        );
    }
}