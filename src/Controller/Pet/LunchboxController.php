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
use App\Entity\LunchboxItem;
use App\Entity\Pet;
use App\Enum\LocationEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPPetNotFoundException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/pet")]
class LunchboxController
{
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/{pet}/putInLunchbox/{inventory}", methods: ["POST"], requirements: ["pet" => "\d+", "inventory" => "\d+"])]
    public function putFoodInLunchbox(
        Pet $pet, Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if($inventory->getOwner()->getId() !== $user->getId())
            throw new PSPNotFoundException('That item does not exist.');

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if(!$inventory->getItem()->getFood())
            throw new PSPInvalidOperationException('Only foods can be placed into lunchboxes.');

        if(count($pet->getLunchboxItems()) >= $pet->getLunchboxSize())
            throw new PSPInvalidOperationException($pet->getName() . '\'s lunchbox cannot contain more than ' . $pet->getLunchboxSize() . ' items.');

        if($inventory->getHolder())
            throw new PSPInvalidOperationException($inventory->getHolder()->getName() . ' is currently holding that item!');

        if($inventory->getWearer())
            throw new PSPInvalidOperationException($inventory->getWearer()->getName() . ' is currently wearing that item!');

        if($inventory->getLunchboxItem())
            throw new PSPInvalidOperationException('That item is in ' . $inventory->getLunchboxItem()->getPet()->getName() . '\'s lunchbox!');

        $inventory->setLocation(LocationEnum::LUNCHBOX);

        if($inventory->getForSale())
            $em->remove($inventory->getForSale());

        $lunchboxItem = (new LunchboxItem())
            ->setPet($pet)
            ->setInventoryItem($inventory)
        ;

        $em->persist($lunchboxItem);

        $em->flush();

        return $responseService->success();
    }

    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/{pet}/takeOutOfLunchbox/{inventory}", methods: ["POST"], requirements: ["pet" => "\d+", "inventory" => "\d+"])]
    public function takeFoodOutOfLunchbox(
        Pet $pet, Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if($inventory->getOwner()->getId() !== $user->getId())
            throw new PSPNotFoundException('That item does not exist.');

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if(!$inventory->getLunchboxItem())
            throw new PSPInvalidOperationException('That item is not in a lunchbox! (Reload and try again?)');

        $inventory
            ->setLocation(LocationEnum::HOME)
        ;

        $em->remove($inventory->getLunchboxItem());

        $em->flush();

        return $responseService->success();
    }
}
