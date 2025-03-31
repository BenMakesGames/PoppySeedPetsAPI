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
use App\Entity\User;
use App\Enum\StatusEffectEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/werebane")]
class WerebaneController extends AbstractController
{
    #[Route("/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function drinkWerebane(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'werebane');

        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        $removedSomething = false;

        if($pet->hasStatusEffect(StatusEffectEnum::BITTEN_BY_A_VAMPIRE))
        {
            $pet->removeStatusEffect($pet->getStatusEffect(StatusEffectEnum::BITTEN_BY_A_VAMPIRE));
            $removedSomething = true;
        }

        if($pet->hasStatusEffect(StatusEffectEnum::BITTEN_BY_A_WERECREATURE))
        {
            $pet->removeStatusEffect($pet->getStatusEffect(StatusEffectEnum::BITTEN_BY_A_WERECREATURE));
            $removedSomething = true;
        }

        if($pet->hasStatusEffect(StatusEffectEnum::WEREFORM))
        {
            $pet->removeStatusEffect($pet->getStatusEffect(StatusEffectEnum::WEREFORM));
            $removedSomething = true;
        }

        if(!$removedSomething)
            throw new PSPInvalidOperationException('But it tastes, like, REALLY gross, and ' . $pet->getName() . ' hasn\'t been bitten by anything supernatural, anyway, so... not worth!');

        $em->remove($inventory);
        $em->flush();

        $responseService->addFlashMessage($pet->getName() . '\'s blood has been cleansed! (No more werecreature saliva, or whatever was going on in there!)');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
