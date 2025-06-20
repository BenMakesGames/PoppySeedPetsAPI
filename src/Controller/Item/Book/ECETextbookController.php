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

#[Route("/item/eceTextbook")]
class ECETextbookController
{
    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventory($userAccessor->getUserOrThrow(), $inventory, 'eceTextbook/#/read');

        return $responseService->itemActionSuccess('# Electrical Engineering 212

## Table of Contents

* Laser Pointer
* Metal Detector
* <i>(it looks like someone put some whiteout over an entry here...)</i>
* Seashell Detector
* Appendix

<hr>

#### Laser Pointer

Use a 3D Printer to print the casing. (URLs to all resources can be found in the appendix.)

* Plastic
* Glass
* Silver or Gold (a good conducting wire is crucial!)

<i>(a wiring diagram follows. you recognize a lot of the symbols, but can\'t quite make sense of it.)</i>

<hr>

#### Metal Detector

You\'ll definitely want a 3D Printer to print some of the components for this build! (URLs to all resources can be found in the appendix.)

* Plastic
* Silver (Iron will also work, if that\'s all you have)
* Magic Smoke

<i>(a strangely-complicated wiring diagram follows.)</i>

If you want to go the extra mile, build a knob that adjusts the distance between the plates. This will allow you to pick up different metals.

<hr>

<i>(it looks like someone tore a page out of the book, here. some of the page still remains... looks like maybe... an "r" at the start of one line? and a "y" on another? hm...)</i>

<hr>

#### Seashell Detector

Start with a basic Metal Detector. The detecting surface needs to be inert, but conductive; gold is a good choice. Latency will be a huge issue, as well, so upgrading the wiring is a must!

* Metal Detector
* Gold
* Fiberglass

<i>(a super-bizarre diagram follows. (is this even still within the realm of science??))</i>

If you built a tuning knob on your Metal Detector, don\'t remove it! Seashells come in many shapes and sizes, and you may find that adjusting the distance between the plates can help you pick up Seashells that might otherwise have been missed!

<hr>

#### Appendix

<i>(there\'s just a bunch of references and URLs and things-- oh, but there\'s also some writing in one of the margins here. it says: "use Compiler to build AI for beetle". huh. what does </i>that<i> mean??)</i>
');
    }
}
