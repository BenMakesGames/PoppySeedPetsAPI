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
use App\Enum\UnlockableFeatureEnum;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/evilFeatherDuster")]
class EvilFeatherDuster
{
    #[Route("/{inventory}/dust", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function dust(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor, IRandom $rng, InventoryService $inventoryService,
        UserStatsService $userStatsService
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventory($userAccessor->getUserOrThrow(), $inventory, 'evilFeatherDuster/#/dust');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $user = $userAccessor->getUserOrThrow();

        /**
         * @var DustingLocation[] $possibleLocations
         */
        $possibleLocations = [];

        // around the house
        $possibleLocations[] = new DustingLocation(
            'their house',
            'Dusted the House',
            'You start to dust every nook and cranny of your house, but barely get through 1/13th of it before the Evil Feather Duster is reduced to bits.',
            [
                'Fluff', 'Fluff', 'Fluff', 'String',
            ]
        );

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Basement))
        {
            $possibleLocations[] = new DustingLocation(
                'Dusted the Basement',
                'their Basement',
                'You dust the Basement until the Evil Feather Duster is completely destroyed, dusted down to a useless nib.',
                [
                    'Fluff', 'Fluff', 'Cobweb', 'Paper',
                ]
            );
        }

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Fireplace))
        {
            $possibleLocations[] = new DustingLocation(
                'Dusted the Fireplace',
                'their Fireplace',
                'You dust the Fireplace until you and the Evil Feather Duster are so completely soot-ridden, you have no choice but to take a shower and throw the duster out.',
                [
                    'Charcoal', 'Cobweb', 'Fluff', 'Brick',
                ]
            );
        }

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Beehive))
        {
            $possibleLocations[] = new DustingLocation(
                'Dusted the Beehive',
                'their Beehive',
                'You dust the Beehive, causing the Evil Feather Duster\'s feathers to get sticky with honey. At first this seems maybe-useful (more sticky == more dust?), but then it very quickly isn\'t.',
                [
                    'Honeycomb', 'Honeycomb', 'Fluff', 'Antenna',
                ]
            );
        }

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Mailbox))
        {
            $possibleLocations[] = new DustingLocation(
                'their Mailbox',
                'Dusted the Mailbox',
                'You dust the Mailbox, but the Evil Feather Duster becomes so enraged by all the junk mail that it eventually gives up on life. (It turns out feather dusters are more like people than might be guessed.)',
                [
                    'Paper', 'Dust', 'Cobweb', 'Missing Mail',
                ]
            );
        }

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Greenhouse))
        {
            $possibleLocations[] = new DustingLocation(
                'their Greenhouse',
                'Dusted the Greenhouse',
                'You dust the Greenhouse until the Evil Feather Duster is so covered in ants that you refuse to touch it ever again, or even acknowledge that it ever existed in the first place.',
                [
                    'Line of Ants', 'Line of Ants', 'Line of Ants', 'Dandelion',
                ]
            );
        }

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::DragonDen))
        {
            $possibleLocations[] = new DustingLocation(
                'their Dragon Den',
                'Dusted the Dragon Den',
                'You dust the Dragon Den until all the dust causes the dragon to sneeze, reducing your duster to, well, dust. (Ironic.)',
                [
                    'Scales', 'Gold Bar', 'Fluff', 'Charcoal',
                ]
            );
        }

        // public places
        $possibleLocations[] = new DustingLocation(
            'the Plaza',
            'Dusted the Plaza',
            'You dust the Plaza so recklessly that you try dusting the Evil Feather Duster. Apparently these things are, I dunno, hydromisic or hydronemetic or something, because the moment it touches water, the duster crumples up so powerfully that it turns into a Tiny Blackhole!',
            [
                'Tiny Blackhole'
            ]
        );

        $possibleLocations[] = new DustingLocation(
            'the Grocer',
            'Dusted the Grocer',
            'You dust the Grocer for a while before other customers start to complain about how weird you\'re being to the point that an employee takes the duster from you and kicks you out. This feels ridiculously unfair and unfun to you, but upon reflection you admit that might be the Evil Feather Duster talking.',
            [
                'Sugar', 'Rice', 'Paper', 'Fluff',
            ]
        );

        $possibleLocations[] = new DustingLocation(
            'the Painter',
            'Dusted the Painter',
            'You never really thought about what the Painter\'s place actually looks like before, and you still haven\'t due to the single-minded dusting frenzy the Evil Feather Duster has inflicted on you. At first, Robin is amused, but when you start to dust her paints, she sternly asks you to leave. Not that you could do much more dusting once paint got in the duster...',
            [
                'Green Dye', 'Quinacridone Magenta Dye', 'Yellow Dye', 'Fluff'
            ]
        );

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Trader))
        {
            $possibleLocations[] = new DustingLocation(
                'the Trader',
                'Dusted the Trader',
                'You dust around at the Trader\'s so fervently that you catch the fish merchant\'s attention. You eventually receive an offer so tempting, not even the Evil Feather Duster\'s influence can stop you from accepting it.',
                [
                    'Major Scroll of Riches',
                ]
            );
        }

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Bookstore))
        {
            $possibleLocations[] = new DustingLocation(
                'the Bookstore',
                'Dusted the Bookstore',
                'You dust as many bookshelves of the Bookstore as you can before accumulating so many papercuts that the pain breaks the spell... and the dusting end of the duster. (And now that you think about it, you\'re not actually sure which event _actually_ broke the spell - a worrying thought!)',
                [
                    'Paper', 'Paper', 'Dust', 'Dust',
                ]
            );
        }

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Museum))
        {
            $possibleLocations[] = new DustingLocation(
                'the Museum',
                'Dusted the Museum',
                'You dust several rooms of the Museum before accidentally dropped the duster in an exhibit. You very nearly jump in after it, but perhaps because you\'re no longer under the duster\'s influence, you think better of it, and simply return home.',
                [
                    'Fluff', 'Cobweb', 'Fish Bones', 'Aging Powder',
                ]
            );
        }

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Park))
        {
            $possibleLocations[] = new DustingLocation(
                'the Park',
                'Dusted the Park',
                'Once you get to dusting, you realize how unbelievably gross the Park is to the point that it\'s hard to tell where the Evil Feather Duster\'s Evil magic and your own motivation to clean up the world end and begin. You dust until the duster is ravaged beyond use by all the san-- er, Silica Grounds.',
                [
                    'Silica Grounds', 'Silica Grounds', 'Dark Matter', 'Dark Matter',
                ]
            );
        }

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Hattier))
        {
            $possibleLocations[] = new DustingLocation(
                'the Hattier',
                'Dusted the Hattier',
                'You dust the Hattier\'s until the duster is completely bent out of shape. The Hattier thanks you for your services, and even gives you some cookies as thanks.',
                [
                    'Fluff', 'White Cloth', 'World\'s Best Sugar Cookie', 'World\'s Best Sugar Cookie',
                ]
            );
        }

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Zoologist))
        {
            $possibleLocations[] = new DustingLocation(
                'the Zoologist',
                'Dusted the Zoologist',
                'You begin dusting the Zoologist\'s, but it seems Rue is familiar with the Evil Feather Duster\'s powers: before you can react she snatches it from you and snaps it in two. You thank her, and share a pot of tea before heading home.',
                [
                    'Tremendous Tea', 'Totally Tea', 'Tiny Tea',
                ]
            );
        }

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Florist))
        {
            $possibleLocations[] = new DustingLocation(
                'the Florist',
                'Dusted the Florist',
                'Kat seems surprised, but ultimately unconcerned by your fervent dusting. Eventually so many feathers have been rustled free of the duster that there\'s simply no further dusting you can do. Exhausted, you leave.',
                [
                    'Agrimony', 'Iris', 'Red Clover', 'Viscaria', 'Wolf\'s Bane'
                ]
            );
        }

        $hasAllPossibleLocations = count($possibleLocations) >= 17;

        $location = $rng->rngNextFromArray($possibleLocations);

        $message =
            'You begin dusting, as if possessed (which, of course, you are - that\'s the evil!) ' .
            $location->description .
            $rng->rngNextFromArray([ 'Well, at least you got ', 'So hey: you got ', 'All that for ' ]) .
            ArrayFunctions::list_nice($location->loot) . '!'
        ;

        foreach($location->loot as $itemName)
            $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' got this from dusting ' . $location->locationName . ' with an Evil Feather Duster.', $inventory->getLocation(), !$hasAllPossibleLocations);

        $em->remove($inventory);

        $userStatsService->incrementStat($user, $location->statName);

        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}

class DustingLocation
{
    public function __construct(
        public readonly string $locationName,
        public readonly string $statName,
        public readonly string $description,
        public readonly array $loot,
    )
    {
    }
}