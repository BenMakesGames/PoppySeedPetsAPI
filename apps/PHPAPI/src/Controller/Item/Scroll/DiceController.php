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


namespace App\Controller\Item\Scroll;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/scroll")]
class DiceController extends AbstractController
{
    #[Route("/dice/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function readScrollOfDice(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        EntityManagerInterface $em, UserStatsService $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'scroll/dice/#/read');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $n = $squirrel3->rngNextInt(1, 100);
        $howRead = '';

        if($n <= 40)
        {
            $dice = 4;
            $howRead = ', stumbling over some of the words';
        }
        else if($n <= 80)
            $dice = 5;
        else if($n < 90)
        {
            $dice = 6;
            $howRead = ' loud and clear';
        }
        else
        {
            $dice = 8;
            $howRead = ' with ' . $squirrel3->rngNextFromArray([
                'a booming voice',
                'a voice as vibrant as a rainbow',
                'a voice as smooth as Chocolate Syrup',
                'dramatic flair and perfectly-rolled "r"s'
            ]);
        }

        for($i = 0; $i < $dice; $i++)
        {
            $die = $squirrel3->rngNextFromArray([
                'Glowing Four-sided Die', 'Glowing Four-sided Die',
                'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die',
                'Glowing Eight-sided Die', 'Glowing Eight-sided Die',
            ]);

            $inventoryService->receiveItem($die, $user, $user, $user->getName() . ' found this in ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $lockedToOwner);
        }

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $em->remove($inventory);

        $em->flush();

        $message = 'You read the scroll' . $howRead . ', and the shapes of ' . $dice . ' dice form on its surface before suddenly popping out';

        if($squirrel3->rngNextInt(1, 5) === 1)
        {
            $message .= '! The scroll\'s magic is consumed in the process, reducing it to mundane Paper.';
            $inventoryService->receiveItem('Paper', $user, $user, 'The mundane remains of ' . $inventory->getItem()->getNameWithArticle() . ' read by ' . $user->getName() . '.', $location, $lockedToOwner);
        }
        else
            $message .= ', reducing the scroll to shreds!';

        $responseService->addFlashMessage($message);

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}