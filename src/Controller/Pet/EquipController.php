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


namespace App\Controller\Pet;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\StatusEffectEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\EquipmentFunctions;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/pet")]
class EquipController extends AbstractController
{
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/{pet}/equip/{inventory}", methods: ["POST"], requirements: ["pet" => "\d+", "inventory" => "\d+"])]
    public function equipPet(
        Pet $pet, Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($inventory->getOwner()->getId() !== $user->getId())
            throw new PSPNotFoundException('That item does not exist.');

        if(!$inventory->getItem()->getTool())
            throw new PSPInvalidOperationException('That item\'s not an equipment!');

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if(!$pet->isAtHome())
            throw new PSPInvalidOperationException('Pets that aren\'t home cannot be interacted with.');

        if(
            ($pet->hasStatusEffect(StatusEffectEnum::WEREFORM)) &&
            $inventory->getItem()->getTreasure() &&
            $inventory->getItem()->getTreasure()->getSilver() > 0
        )
        {
            throw new PSPInvalidOperationException($pet->getName() . ' recoils at the sight of the silvery ' . $inventory->getFullItemName() . '!');
        }

        if($pet->getTool())
        {
            if($inventory->getId() === $pet->getTool()->getId())
                throw new PSPInvalidOperationException($pet->getName() . ' is already equipped with that ' . $pet->getTool()->getFullItemName() . '!');

            EquipmentFunctions::unequipPet($pet);
        }

        if($inventory->getHolder())
        {
            $inventory->getHolder()->setTool(null);
            $em->flush();
        }

        if($inventory->getWearer())
        {
            $inventory->getWearer()->setHat(null);
            $em->flush();
        }

        // equip the tool
        $pet->setTool($inventory);

        // move it to the wardrobe
        $inventory->setLocation(LocationEnum::WARDROBE);

        if($inventory->getForSale())
            $em->remove($inventory->getForSale());

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }

    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/{pet}/hat/{inventory}", methods: ["POST"], requirements: ["pet" => "\d+", "inventory" => "\d+"])]
    public function hatPet(
        Pet $pet, Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($inventory->getOwner()->getId() !== $user->getId())
            throw new PSPNotFoundException('That item does not exist.');

        if(!$inventory->getItem()->getHat())
            throw new PSPInvalidOperationException('That item\'s not a hat!');

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if(!$pet->isAtHome())
            throw new PSPInvalidOperationException('Pets that aren\'t home cannot be interacted with.');

        if(!$pet->hasMerit(MeritEnum::BEHATTED))
            throw new PSPInvalidOperationException($pet->getName() . ' does not have the Merit required to wear hats.');

        if(
            $pet->hasStatusEffect(StatusEffectEnum::WEREFORM) &&
            $inventory->getItem()->getTreasure() &&
            $inventory->getItem()->getTreasure()->getSilver() > 0
        )
        {
            throw new PSPInvalidOperationException($pet->getName() . ' recoils at the sight of the silvery ' . $inventory->getFullItemName() . '!');
        }

        if($pet->getHat())
        {
            if($inventory->getId() === $pet->getHat()->getId())
                throw new PSPInvalidOperationException($pet->getName() . ' is already wearing that ' . $pet->getHat()->getFullItemName() . '!');

            EquipmentFunctions::unhatPet($pet);
        }

        if($inventory->getHolder())
        {
            $inventory->getHolder()->setTool(null);
            $em->flush();
        }

        if($inventory->getWearer())
        {
            $inventory->getWearer()->setHat(null);
            $em->flush();
        }

        // equip the hat
        $pet->setHat($inventory);

        // move it to the wardrobe
        $inventory->setLocation(LocationEnum::WARDROBE);

        if($inventory->getForSale())
            $em->remove($inventory->getForSale());

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }

    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/{pet}/unequip", methods: ["POST"], requirements: ["pet" => "\d+"])]
    public function unequipPet(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if(!$pet->isAtHome()) throw new PSPInvalidOperationException('Pets that aren\'t home cannot be interacted with.');

        if(!$pet->getTool())
            throw new PSPInvalidOperationException($pet->getName() . ' is not currently equipped.');

        EquipmentFunctions::unequipPet($pet);

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }

    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/{pet}/unhat", methods: ["POST"], requirements: ["pet" => "\d+"])]
    public function unhatPet(Pet $pet, ResponseService $responseService, EntityManagerInterface $em)
    {
        /** @var User $user */
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if(!$pet->isAtHome())
            throw new PSPInvalidOperationException('Pets that aren\'t home cannot be interacted with.');

        if(!$pet->getHat())
            throw new PSPInvalidOperationException($pet->getName() . ' is not currently wearing a hat.');

        $pet->getHat()
            ->setLocation(LocationEnum::HOME)
        ;

        $pet->setHat(null);

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }
}
