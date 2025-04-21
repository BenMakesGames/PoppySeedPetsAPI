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
use App\Enum\StatusEffectEnum;
use App\Functions\StatusEffectHelpers;
use App\Service\HotPotatoService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/glitterBomb")]
class GlitterBombController extends AbstractController
{
    #[Route("/{inventory}/toss", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function toss(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        IRandom $squirrel3, HotPotatoService $hotPotatoService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'glitterBomb/#/toss');

        if($squirrel3->rngNextInt(1, 5) === 1)
        {
            $pets = $em->getRepository(Pet::class)->findBy([
                'owner' => $user,
                'location' => PetLocationEnum::HOME
            ]);

            foreach($pets as $pet)
                StatusEffectHelpers::applyStatusEffect($em, $pet, StatusEffectEnum::GLITTER_BOMBED, 12 * 60);

            $em->remove($inventory);
            $em->flush();

            if(count($pets) === 0)
                return $responseService->itemActionSuccess('You get ready to toss the Glitter Bomb, but it explodes, getting glitter all over you. (Your pets would have presumably also been affected, but they\'re not here, so...)', [ 'itemDeleted' => true ]);
            else
            {
                $responseService->setReloadPets();

                if(count($pets) === 1)
                    return $responseService->itemActionSuccess('You get ready to toss the Glitter Bomb, but it explodes, getting glitter all over you, and ' . $pets[0]->getName() . '.', [ 'itemDeleted' => true ]);
                else
                    return $responseService->itemActionSuccess('You get ready to toss the Glitter Bomb, but it explodes, getting glitter all over you, and, more importantly, your pets.', [ 'itemDeleted' => true ]);
            }
        }
        else
        {
            return $hotPotatoService->tossItem($inventory);
        }
    }
}
