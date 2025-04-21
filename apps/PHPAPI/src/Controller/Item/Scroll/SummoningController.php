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
use App\Entity\User;
use App\Enum\PetLocationEnum;
use App\Enum\UserStatEnum;
use App\Model\SummoningScrollMonster;
use App\Service\IRandom;
use App\Service\PetActivity\HouseMonsterService;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/item/summoningScroll')]
class SummoningController extends AbstractController
{
    #[Route("/{inventory}/unfriendly", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function summonSomethingUnfriendly(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        HouseMonsterService $houseMonsterService, IRandom $squirrel3, UserStatsService $userStatsRepository
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'summoningScroll/#/unfriendly');

        $em->remove($inventory);

        $petsAtHome = $em->getRepository(Pet::class)->findBy([
            'owner' => $user,
            'location' => PetLocationEnum::HOME
        ]);

        if(count($petsAtHome) === 0)
        {
            return $responseService->itemActionSuccess('Summoning something so terrifying on your own would be... _unwise_. (You need some pets at home to help!)');
        }

        /** @var SummoningScrollMonster $monster */
        $monster = $squirrel3->rngNextFromArray([
            SummoningScrollMonster::CreateDragon(),
            SummoningScrollMonster::CreateBalrog(),
            SummoningScrollMonster::CreateBasabasa(),
            SummoningScrollMonster::CreateIfrit(),
            SummoningScrollMonster::CreateCherufe(),
        ]);

        $result = $houseMonsterService->doFight('You read the scroll', $petsAtHome, $monster);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $em->flush();

        return $responseService->itemActionSuccess($result, [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/unfriendly2", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function summonSomethingFromDeepSpace(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        HouseMonsterService $houseMonsterService, IRandom $squirrel3
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'summoningScroll/#/unfriendly2');

        $em->remove($inventory);

        $petsAtHome = $em->getRepository(Pet::class)->findBy([
            'owner' => $user,
            'location' => PetLocationEnum::HOME
        ]);

        if(count($petsAtHome) === 0)
        {
            return $responseService->itemActionSuccess('Summoning something so terrifying on your own would be... _unwise_. (You need some pets at home to help!)');
        }

        /** @var SummoningScrollMonster $monster */
        $monster = $squirrel3->rngNextFromArray([
            SummoningScrollMonster::CreateCrystallineEntity(),
            SummoningScrollMonster::CreateBivusRelease(),
            SummoningScrollMonster::CreateSpaceJelly(),
        ]);

        $result = $houseMonsterService->doFight('You cast your voice into the mirror', $petsAtHome, $monster);

        $em->flush();

        return $responseService->itemActionSuccess($result, [ 'itemDeleted' => true ]);
    }
}
