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
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/featheredHat")]
class FeatheredHatController extends AbstractController
{
    private const array TWEAKS = [
        'Afternoon Hat' => 'Evening Hat',
    ];

    #[Route("/{inventory}/tweak", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function tweakHat(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'featheredHat/#/tweak');

        $oldItemName = $inventory->getItem()->getName();

        if(array_key_exists($oldItemName, self::TWEAKS))
            $newItemName = self::TWEAKS[$oldItemName];
        else
        {
            $newItemName = array_search($oldItemName, self::TWEAKS);

            if(!$newItemName)
                throw new \Exception($oldItemName . ' cannot be tweaked?? This is a result of programmer oversight. Please let Ben know.');
        }

        $newItem = ItemRepository::findOneByName($em, $newItemName);

        $reloadPets = $inventory->getHolder() || $inventory->getWearer();

        $inventory
            ->changeItem($newItem)
            ->setModifiedOn()
        ;

        $em->flush();

        $responseService
            ->addFlashMessage('The hat shifts in color!')
            ->setReloadPets($reloadPets)
            ->setReloadInventory()
        ;

        return $responseService->itemActionSuccess(null);
    }
}
