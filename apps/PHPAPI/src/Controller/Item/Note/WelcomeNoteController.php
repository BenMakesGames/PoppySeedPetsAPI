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


namespace App\Controller\Item\Note;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/note")]
class WelcomeNoteController extends AbstractController
{
    #[Route("/welcome/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function readWelcomeNote(Inventory $inventory, ResponseService $responseService)
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'note/welcome/#/read');

        return $responseService->itemActionSuccess('# Welcome!

Thanks for adopting a pet! So many of these creatures have been appearing from the portal, we\'re having trouble taking care of them all!

Here\'s some basic notes on how to take care of your new pet:

1. Visit the Plaza every week to get a free box of food stuffs!
1. Roughly every hour, your pet will go out and do stuff, all on its own! These little guys like to fish, hunt, gather, and even make their own tools!
1. Touch your pet to interact with it, and see a log of what it\'s been up to.
1. Have fun!
');
    }
}
