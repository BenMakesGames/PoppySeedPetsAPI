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


namespace App\Controller\Item\Book;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Service\ResponseService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/onVampires")]
class VampiresController
{
    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventory($userAccessor->getUserOrThrow(), $inventory, 'onVampires/#/read');

        return $responseService->itemActionSuccess(<<<EOBOOK
### The Monster

Vampires sustain themselves on the blood of mortals, which not only nourishes them but also gives them potent supernatural abilities. However, with this power comes the curse of "the Monster", an inner force that drives a vampire's hunger and rage. The struggle to maintain one's humanity against the pull of the Monster is central to a vampire's existence, and of vampire society at large.

### The Veil

Most vampires believe that if humanity gained widespread awareness of vampires, it would make feeding more difficult. In the face of the Monster, humanity would take measures to protect themselves, and possibly develop effective ways of fighting and killing vampires. For this reason, a complex system of deceptions called "the Veil" is maintained, to ensure that humanity remains ignorant. These deceptions take many forms, such as "the Blush of Life," a technique that allows vampires to more easily pass as human.

Though most vampires consider the Veil essential for their survival, there are some that believe vampires should rule over humans openly, and that accepting the Veil is a sign of weakness.

### The Umbra

The Umbra, a reflection of the spiritual and conceptual universe parallel to the material world, is known to many vampires. While primarily the domain of other supernaturals, such as werewolves and the fae, vampires may venture into the Umbra to negotiate, battle, or uncover secrets. Some vampires establish a permanent residence in the Umbra, however acquiring blood there can be more difficult.
EOBOOK);
    }
}