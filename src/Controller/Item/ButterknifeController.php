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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/butterknife")]
class ButterknifeController extends AbstractController
{
    #[Route("/{inventory}/mold", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function moldButterknife(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'butterknife/#/mold');

        $butter = ItemRepository::findOneByName($em, 'Butter');

        $reloadPets = $inventory->getHolder() || $inventory->getWearer();

        $inventory
            ->changeItem($butter)
            ->setModifiedOn()
        ;

        $em->flush();

        $responseService->addFlashMessage('There we go. Back to normal.');
        $responseService->setReloadPets($reloadPets);

        return $responseService->itemActionSuccess(null);
    }
}
