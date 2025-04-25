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


namespace App\Controller\Fireplace;

use App\Entity\Dragon;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ArrayFunctions;
use App\Functions\DragonRepository;
use App\Functions\RequestFunctions;
use App\Functions\UserUnlockedFeatureHelpers;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/fireplace")]
class FeedWhelpController
{
    #[Route("/feedWhelp", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function feedWhelp(
        Request $request, ResponseService $responseService,
        InventoryService $inventoryService, EntityManagerInterface $em, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $whelp = DragonRepository::findWhelp($em, $user);

        if(!$whelp)
            throw new PSPNotUnlockedException('Dragon Whelp');

        $itemIds = RequestFunctions::getUniqueIdsOrThrow($request, 'food', 'No items were selected as food???');

        $items = $em->getRepository(Inventory::class)->findBy([
            'id' => $itemIds,
            'owner' => $user->getId(),
            'location' => LocationEnum::HOME
        ]);

        $items = array_filter($items, function(Inventory $i) {
            return $i->getItem()->getFood() && (
                $i->getItem()->getFood()->getFishy() > 0 || // most foods you feed the whelp are probably fishy
                $i->getItem()->getFood()->getMeaty() > 0 ||
                $i->getItem()->getFood()->getSpicy() > 0
            );
        });

        if(count($items) < count($itemIds))
            throw new PSPNotFoundException('Some of the food items selected could not be found. That shouldn\'t happen. Reload and try again, maybe?');

        $loot = [];

        foreach($items as $item)
        {
            $em->remove($item);

            $whelp->increaseFood($item->getItem()->getFood()->getFood() + $item->getItem()->getFood()->getSpicy() * 2);

            while($whelp->getFood() >= Dragon::FOOD_REQUIRED_FOR_A_MEAL)
            {
                $whelp->decreaseFood();

                $r = $rng->rngNextInt(1, 100);

                if($r === 1)
                    $loot[] = 'Firestone';          // 1%
                else if($r === 2 || $r === 3)
                    $loot[] = 'Dark Matter';        // 2%
                else if($r <= 8)
                    $loot[] = 'Charcoal';           // 5%
                else if($r <= 28)
                    $loot[] = 'Quintessence';       // 20%
                else
                    $loot[] = 'Liquid-hot Magma';   // 72%
            }
        }

        if(count($loot) > 0)
        {
            sort($loot);

            foreach($loot as $item)
                $inventoryService->receiveItem($item, $user, $user, $whelp->getName() . ' spit this up.', LocationEnum::HOME);

            $responseService->addFlashMessage($whelp->getName() . ' spit up ' . ArrayFunctions::list_nice($loot) . '.');
        }
        else
        {
            $adverb = $rng->rngNextFromArray([
                'happily', 'happily', 'happily', 'excitedly', 'blithely', 'eagerly'
            ]);

            $responseService->addFlashMessage($whelp->getName() . ' ' . $adverb . ' devoured your offering.');
        }

        if($whelp->getGrowth() >= 35 * 20)
        {
            $greetingsAndThanks = $rng->rngNextSubsetFromArray(Dragon::GREETINGS_AND_THANKS, 2);

            $whelp
                ->setIsAdult(true)
                ->setGreetings([ $greetingsAndThanks[0]['greeting'], $greetingsAndThanks[1]['greeting'] ])
                ->setThanks([ $greetingsAndThanks[0]['thanks'], $greetingsAndThanks[1]['thanks'] ])
            ;

            UserUnlockedFeatureHelpers::create($em, $user, UnlockableFeatureEnum::DragonDen);

            $responseService->addFlashMessage($whelp->getName() . ' is a whelp no longer! They leave your fireplace and establish a den nearby! (The Dragon Den is now available! Check it out in the menu!)');
        }

        $em->flush();

        if($whelp->getIsAdult())
            return $responseService->success();
        else
            return $responseService->success($whelp, [ SerializationGroupEnum::MY_FIREPLACE ]);
    }
}
