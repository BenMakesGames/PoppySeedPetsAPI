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


namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/plasticEgg")]
class PlasticEggController
{
    private const array Candy = [
        'Blue Hard Candy',
        'Orange Hard Candy',
        'Purple Hard Candy',
        'Red Hard Candy',
        'Yellow Hard Candy',
        'Blue Gummies',
        'Green Gummies',
        'Orange Gummies',
        'Purple Gummies',
        'Red Gummies',
        'Yellow Gummies',
        'Chocolate Bar',
        'Chocolate Bunny',
        'Dark Chocolate Bunny',
        'Marshmallow Bulbun',
    ];

    #[Route("/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function open(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'plasticEgg/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        switch($inventory->getItem()->getName())
        {
            case 'Blue Plastic Egg':
                // common

                if($rng->rngNextInt(1, 30) === 1)
                {
                    $possibleLoot = [
                        [
                            'items' => [ 'Yellow Plastic Egg' ],
                            'description' => 'and-- hey! There\'s a Yellow Plastic Egg inside!'
                        ]
                    ];
                }
                else
                {
                    $possibleLoot = [
                        [
                            'items' => [ 'Plastic' ],
                            'description' => 'but it\'s Plastic all the way through! (Dang trick eggs!)'
                        ],
                        [
                            'items' => [ 'Egg' ],
                            'description' => 'revealing... an actual Egg? Huh. Okay. Weird.'
                        ],
                        [
                            'items' => [ 'Beans', 'Beans' ],
                            'description' => 'revealing Beans!' . ($rng->rngNextInt(1, 4) === 1 ? ' BEAAAAAAANS!' : ''),
                        ],
                        [
                            'items' => [
                                $rng->rngNextFromArray(self::Candy),
                                $rng->rngNextFromArray(self::Candy),
                                $rng->rngNextFromArray(self::Candy)
                            ],
                            'description' => 'and there\'s candy inside! (Yay! Candy!)',
                        ],
                        [
                            'items' => [
                                $rng->rngNextFromArray(self::Candy),
                                $rng->rngNextFromArray(self::Candy),
                                $rng->rngNextFromArray(self::Candy)
                            ],
                            'description' => 'and there\'s candy inside! :D',
                        ],
                        [
                            'items' => [
                                $rng->rngNextFromArray([
                                    'Mini Chocolate Chip Cookies',
                                    'Shortbread Cookies',
                                    'Thicc Mints',
                                ]),
                                $rng->rngNextFromArray([
                                    'Browser Cookie',
                                    'Fortune Cookie',
                                    'World\'s Best Sugar Cookie'
                                ])
                            ],
                            'description' => 'and - ooh! - there\'s cookies inside!',
                        ],
                        [
                            'items' => [ 'Fluff', 'String' ],
                            'description' => 'but there\'s just some Fluff and a bit of String inside. Hrm.',
                        ]
                    ];
                }

                break;

            case 'Yellow Plastic Egg':
                // uncommon

                if($rng->rngNextInt(1, 15) === 1)
                {
                    $possibleLoot = [
                        [
                            'items' => [ 'Pink Plastic Egg' ],
                            'description' => 'and-- hey! There\'s a Pink Plastic Egg inside!'
                        ]
                    ];
                }
                else
                {
                    $possibleLoot = [
                        [
                            'items' => [
                                $rng->rngNextFromArray(self::Candy),
                                $rng->rngNextFromArray(self::Candy),
                                $rng->rngNextFromArray(self::Candy),
                                $rng->rngNextFromArray(self::Candy),
                                $rng->rngNextFromArray(self::Candy)
                            ],
                            'description' => 'and there\'s candy inside! SO MUCH CANDY!',
                        ],
                        [
                            'items' => [ 'Century Egg' ],
                            'description' => 'and there\'s a Century Egg inside?!' . ($rng->rngNextInt(1, 4) === 1 ? ' WHO WOULD DO SUCH A THING?!?' : '')
                        ],
                        [
                            'items' => [
                                $rng->rngNextFromArray([ 'Black Animal Ears', 'White Animal Ears' ])
                            ],
                            'description' => 'and there\'s animal ears inside! (Oh, but don\'t worry: they\'re not real! See? Just Plastic and polyester!)'
                        ],
                        [
                            'items' => [
                                $rng->rngNextFromArray([
                                    '"Gold" Idol',
                                    'Glowing Six-sided Die',
                                    'Gold Triangle',
                                    'Maraca',
                                    'Toy Alien Gun'
                                ])
                            ],
                            'description' => 'and there\'s a toy inside! (Which toy? Check your house and see! It\'s a surprise, and/or this part of the code doesn\'t have access to the item name, for weird reasons I won\'t get into here!)'
                        ]
                    ];
                }
                break;

            case 'Pink Plastic Egg':
                // rare

                $possibleLoot = [
                    [
                        'items' => [ 'Behatting Scroll' ],
                        'description' => 'and there\'s a scroll inside! A... Behatting Scroll!'
                    ],
                    [
                        'items' => [ 'Hyperchromatic Prism' ],
                        'description' => 'and there\'s... a prism inside? Weird. It\'s very shiny...'
                    ],
                    [
                        'items' => [ 'Feathers', 'Ruby Feather' ],
                        'description' => 'and there\'s... some Feathers inside, including a Ruby Feather! (Oh! Pretty!)'
                    ],
                    [
                        'items' => [ 'Species Transmigration Serum' ],
                        'description' => 'and there\'s a _syringe_ inside!?! Now I\'m not claiming to be an expert, or anything, but that seems _exceptionally_ unsafe to me!!'
                    ]
                ];
                break;

            default:
                throw new \Exception('Ben screwed up! There\'s no code to handle a ' . $inventory->getItem()->getName() . '!');
        }

        $loot = $rng->rngNextFromArray($possibleLoot);

        $message = 'You open up the plastic egg, ' . $loot['description'];

        foreach($loot['items'] as $itemName)
        {
            $inventoryService->receiveItem(
                $itemName,
                $user,
                $inventory->getCreatedBy(),
                $user->getName() . ' found this inside a ' . $inventory->getItem()->getName() . '!',
                $inventory->getLocation(),
                $inventory->getLockedToOwner()
            );
        }

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
