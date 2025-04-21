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
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\PetLocationEnum;
use App\Model\SummoningScrollMonster;
use App\Service\PetActivity\HouseMonsterService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/evilBeetle")]
class EvilSentientBeetleController extends AbstractController
{
    #[Route("/{inventory}/defeat", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function summonSomethingUnfriendly(
        Inventory $inventory, ResponseService $responseService,
        EntityManagerInterface $em, HouseMonsterService $houseMonsterService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'evilBeetle/#/defeat');

        $em->remove($inventory);

        $petsAtHome = $em->getRepository(Pet::class)->findBy([
            'owner' => $user,
            'location' => PetLocationEnum::HOME
        ]);

        if(count($petsAtHome) === 0)
        {
            return $responseService->itemActionSuccess('You have no pets at home! You can\'t defeat evil all on your own!');
        }

        $monster = SummoningScrollMonster::CreateDiscipleOfHunCame();

        $result = $houseMonsterService->doFight('You challenge the beetle', $petsAtHome, $monster);

        $em->flush();

        return $responseService->itemActionSuccess($result, [ 'itemDeleted' => true ]);
    }
}
