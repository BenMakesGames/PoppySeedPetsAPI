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
use App\Service\TraderService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/note")]
class CreepyMaskDayController
{
    #[Route("/creepyMaskDay/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function readCreepyMaskDayNote(Inventory $inventory, ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventory($userAccessor->getUserOrThrow(), $inventory, 'note/creepyMaskDay/#/read');

        $lines = [];

        for($i = 1; $i <= 12; $i++)
        {
            $monthName = strtolower(date("M", mktime(0, 0, 0, $i, 10)));
            $payment = TraderService::getCreepyMaskDayPayment($i);
            $item = strtolower($payment[0]);

            if($item == 'petrichor') $item .= ' (ugh!)';
            else if($item == 'wed bawwoon') $item .= ' ?';
            else if($item == 'little strongbox') $item = '~~stron~~ LITTLE strongbox';

            $quantity = $payment[1];
            if($quantity == 1)
                $lines[] = "$monthName = $item";
            else
                $lines[] = "$monthName = $item &nbsp; **x$quantity**";
        }

        $lines[] = '';
        $lines[] = 'oct-mar = ash, crystal, gold';
        $lines[] = '~~may-~~**o**thers = others';

        return $responseService->itemActionSuccess(join('<br>', $lines));
    }
}
