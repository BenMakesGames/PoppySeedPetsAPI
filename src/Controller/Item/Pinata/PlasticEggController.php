<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/plasticEgg")
 */
class PlasticEggController extends PoppySeedPetsItemController
{
    private const CANDY = [
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
    ];

    /**
     * @Route("/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function open(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        $this->validateInventory($inventory, 'plasticEgg/#/open');

        $user = $this->getUser();

        switch($inventory->getItem()->getName())
        {
            case 'Blue Plastic Egg':
                // common

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
                        'description' => 'revealing Beans!' . (mt_rand(1, 4) === 1 ? ' BEAAAAAAANS!' : ''),
                    ],
                    [
                        'items' => [
                            ArrayFunctions::pick_one(self::CANDY),
                            ArrayFunctions::pick_one(self::CANDY),
                            ArrayFunctions::pick_one(self::CANDY)
                        ],
                        'description' => 'and there\'s candy inside! (Yay! Candy!)',
                    ],
                    [
                        'items' => [
                            ArrayFunctions::pick_one(self::CANDY),
                            ArrayFunctions::pick_one(self::CANDY),
                            ArrayFunctions::pick_one(self::CANDY)
                        ],
                        'description' => 'and there\'s candy inside! :D',
                    ],
                    [
                        'items' => [
                            ArrayFunctions::pick_one([
                                'Mini Chocolate Chip Cookies',
                                'Shortbread Cookies',
                                'Thicc Mints',
                            ]),
                            ArrayFunctions::pick_one([
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
                break;

            case 'Yellow Plastic Egg':
                // uncommon

                $possibleLoot = [
                    [
                        'items' => [
                            ArrayFunctions::pick_one(self::CANDY),
                            ArrayFunctions::pick_one(self::CANDY),
                            ArrayFunctions::pick_one(self::CANDY),
                            ArrayFunctions::pick_one(self::CANDY),
                            ArrayFunctions::pick_one(self::CANDY)
                        ],
                        'description' => 'and there\'s candy inside! SO MUCH CANDY!',
                    ],
                    [
                        'items' => [ 'Century Egg' ],
                        'description' => 'and there\'s a Century Egg inside?!' . (mt_rand(1, 4) === 1 ? ' WHO WOULD DO SUCH A THING?!?' : '')
                    ],
                    [
                        'items' => [
                            ArrayFunctions::pick_one([ 'Black Animal Ears', 'White Animal Ears' ])
                        ],
                        'description' => 'and there\'s animal ears inside! (Oh, but don\'t worry: they\'re not real! See? Just Plastic and polyester!)'
                    ],
                    [
                        'items' => [
                            ArrayFunctions::pick_one([
                                'Gold Idol',
                                'Glowing Six-sided Die',
                                'Gold Triangle',
                                'Maraca',
                                'Toy Alien Gun'
                            ])
                        ],
                        'description' => 'and there\'s a toy inside! (Which toy? Check your house and see! It\'s a surprise, and/or this part of the code doesn\'t have access to the item name, for weird reasons I won\'t get into here!)'
                    ]
                ];
                break;

            case 'Pink Plastic Egg':
                // rare

                $possibleLoot = [
                    [
                        'items' => [ 'Renaming Scroll' ],
                        'description' => 'and there\'s a scroll inside! A... Renaming Scroll!',
                    ],
                    [
                        'items' => [ 'Behatting Scroll' ],
                        'description' => 'and there\'s a scroll inside! A... Behatting Scroll!'
                    ],
                    [
                        'items' => [ 'Hyperchromatic Prism' ],
                        'description' => 'and there\'s... a prism inside? Weird. It\'s very shiny...'
                    ],
                    [
                        'items' => [ 'Ruby Feather' ],
                        'description' => 'and there\'s... a Ruby Feather inside! (Oh! Pretty!)'
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

        $loot = ArrayFunctions::pick_one($possibleLoot);

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

        return $responseService->itemActionSuccess($message, [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}
