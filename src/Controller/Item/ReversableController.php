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
use App\Functions\ItemRepository;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/reversable")]
class ReversableController
{
    private const array Flips = [
        'Small Plastic Bucket' => 'Upside-down Plastic Bucket',
        'Shiny Pail' => 'Upside-down Shiny Pail',
        'Small, Yellow Plastic Bucket' => 'Upside-down, Yellow Plastic Bucket',
        'Saucepan' => 'Upside-down Saucepan',
        'Silver Colander' => 'Upside-down Silver Colander',
        'Pie Crust' => 'Upside-down Pie Crust',
    ];

    #[Route("/{inventory}/flip", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function flipIt(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventory($userAccessor->getUserOrThrow(), $inventory, 'reversable/#/flip');

        $oldItemName = $inventory->getItem()->getName();

        if(array_key_exists($oldItemName, self::Flips))
            $newItemName = self::Flips[$oldItemName];
        else
        {
            $newItemName = array_search($oldItemName, self::Flips);

            if(!$newItemName)
                throw new \Exception($oldItemName . ' cannot be flipped?? This is a result of programmer oversight. Please let Ben know.');
        }

        $newItem = ItemRepository::findOneByName($em, $newItemName);

        $inventory
            ->changeItem($newItem)
            ->setModifiedOn()
        ;

        $em->flush();

        $message = $rng->rngNextFromArray([
            'The ' . $oldItemName . ' has been completely transformed, becoming ' . $newItem->getNameWithArticle() . '!' . "\n\n" . 'Incredible.',
            'You rotate the ' . $oldItemName . ' approximately 3.14 radians about its x-axis, et voilà: ' . $newItem->getNameWithArticle() . '!',
            'You deftly flip the ' . $oldItemName . ' into ' . $newItem->getNameWithArticle() . '!',
            'You caaaaaarefully turn the ' . $oldItemName . ' over, then caaaaaarefully put it down...' . "\n\n" . 'Okay... okay, yeah! It worked!' . "\n\n" . 'You successfully made ' . $newItem->getNameWithArticle() . '!',
            'You confidently toss the ' . $oldItemName . ' into the air with a twist, close your eyes, turn around, and catch it behind you.' . "\n\n" . 'A bit ostentatious, but effective nonetheless: you now have ' . $newItem->getNameWithArticle() . '!'
        ]);

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
