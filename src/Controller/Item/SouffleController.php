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
use App\Entity\User;
use App\Enum\UnlockableFeatureEnum;
use App\Functions\EnchantmentRepository;
use App\Functions\ItemRepository;
use App\Functions\UserQuestRepository;
use App\Service\HattierService;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/souffle")]
class SouffleController extends AbstractController
{
    #[Route("/{inventory}/startle", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function startle(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        UserStatsService $userStatsService
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'souffle/#/startle');

        $inventory->changeItem(ItemRepository::findOneByName($em, 'Startled Soufflé'));
        $userStatsService->incrementStat($user, 'Soufflés Startled');

        $em->flush();

        $responseService->setReloadInventory(true);

        return $responseService->itemActionSuccess('It\'s _your_ hot Soufflé - you can do what you want.', ['itemDeleted' => true]);
    }
}
