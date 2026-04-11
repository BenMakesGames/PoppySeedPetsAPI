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

namespace App\Controller\Museum;

use App\Entity\Inventory;
use App\Entity\MuseumItem;
use App\Enum\LocationEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Enum\UserStat;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ArrayFunctions;
use App\Service\ResponseService;
use App\Service\TransactionService;
use App\Service\UserAccessor;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/museum")]
class DonateController
{
    #[Route("/donate", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function handle(
        ResponseService $responseService, Request $request,
        EntityManagerInterface $em, UserStatsService $userStatsRepository,
        TransactionService $transactionService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Museum))
            throw new PSPNotUnlockedException('Museum');

        $inventoryIds = $request->request->all('inventory');

        if(count($inventoryIds) > 20)
            throw new PSPFormValidationException('You may only donate up to 20 items at a time.');

        $inventory = $em->getRepository(Inventory::class)->findBy([
            'id' => $inventoryIds,
            'owner' => $user,
            'location' => [ LocationEnum::Home, LocationEnum::Basement ]
        ]);

        if(count($inventory) === 0)
            throw new PSPFormValidationException('No items were selected.');

        $existingMuseumItems = [];

        for($i = count($inventory) - 1; $i >= 0; $i--)
        {
            if($inventory[$i]->getOwner()->getId() !== $user->getId())
            {
                unset($inventory[$i]);
                continue;
            }

            $existingItem = $em->getRepository(MuseumItem::class)->findOneBy([
                'user' => $user,
                'item' => $inventory[$i]->getItem()
            ]);

            if($existingItem)
            {
                $isUpgrade = $inventory[$i]->getCreatedBy()?->getId() === $user->getId()
                    && $existingItem->getCreatedBy()?->getId() !== $user->getId();

                if(!$isUpgrade)
                {
                    unset($inventory[$i]);
                    continue;
                }

                $existingMuseumItems[$inventory[$i]->getId()] = $existingItem;
            }
        }

        if(count($inventory) === 0)
            throw new PSPNotFoundException('Some of the selected items could not be found or donated? That\'s weird. Try reloading and trying again.');

        $totalMuseumPoints = 0;
        $donatedItemNames = [];
        $newDonationCount = 0;

        foreach($inventory as $i)
        {
            if(isset($existingMuseumItems[$i->getId()]))
            {
                // Upgrade existing museum entry
                $existingMuseumItems[$i->getId()]
                    ->setCreatedBy($i->getCreatedBy())
                    ->setComments($i->getComments());
            }
            else
            {
                // New donation
                $museumItem = new MuseumItem(user: $user, item: $i->getItem())
                    ->setCreatedBy($i->getCreatedBy())
                    ->setComments($i->getComments())
                ;

                $totalMuseumPoints += $i->getItem()->getMuseumPoints();
                $newDonationCount++;

                $em->persist($museumItem);
            }

            $donatedItemNames[] = $i->getItem()->getNameWithArticle();
            $em->remove($i);
        }

        $donationSummary = count($inventory) > 5 ? (count($inventory) . ' items') : ArrayFunctions::list_nice($donatedItemNames);

        if($totalMuseumPoints > 0)
            $transactionService->getMuseumFavor($user, $totalMuseumPoints, 'You donated ' . $donationSummary . ' to the Museum.');

        if($newDonationCount > 0)
            $userStatsRepository->incrementStat($user, UserStat::ItemsDonatedToMuseum, $newDonationCount);

        $em->flush();

        return $responseService->success();
    }
}
