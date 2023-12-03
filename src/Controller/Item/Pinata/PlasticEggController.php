<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/plasticEgg")
 */
class PlasticEggController extends AbstractController
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
        'Chocolate Bunny',
        'Dark Chocolate Bunny',
        'Marshmallow Bulbun',
    ];

    #[Route("/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function open(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'plasticEgg/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

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
                        'description' => 'revealing Beans!' . ($squirrel3->rngNextInt(1, 4) === 1 ? ' BEAAAAAAANS!' : ''),
                    ],
                    [
                        'items' => [
                            $squirrel3->rngNextFromArray(self::CANDY),
                            $squirrel3->rngNextFromArray(self::CANDY),
                            $squirrel3->rngNextFromArray(self::CANDY)
                        ],
                        'description' => 'and there\'s candy inside! (Yay! Candy!)',
                    ],
                    [
                        'items' => [
                            $squirrel3->rngNextFromArray(self::CANDY),
                            $squirrel3->rngNextFromArray(self::CANDY),
                            $squirrel3->rngNextFromArray(self::CANDY)
                        ],
                        'description' => 'and there\'s candy inside! :D',
                    ],
                    [
                        'items' => [
                            $squirrel3->rngNextFromArray([
                                'Mini Chocolate Chip Cookies',
                                'Shortbread Cookies',
                                'Thicc Mints',
                            ]),
                            $squirrel3->rngNextFromArray([
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
                            $squirrel3->rngNextFromArray(self::CANDY),
                            $squirrel3->rngNextFromArray(self::CANDY),
                            $squirrel3->rngNextFromArray(self::CANDY),
                            $squirrel3->rngNextFromArray(self::CANDY),
                            $squirrel3->rngNextFromArray(self::CANDY)
                        ],
                        'description' => 'and there\'s candy inside! SO MUCH CANDY!',
                    ],
                    [
                        'items' => [ 'Century Egg' ],
                        'description' => 'and there\'s a Century Egg inside?!' . ($squirrel3->rngNextInt(1, 4) === 1 ? ' WHO WOULD DO SUCH A THING?!?' : '')
                    ],
                    [
                        'items' => [
                            $squirrel3->rngNextFromArray([ 'Black Animal Ears', 'White Animal Ears' ])
                        ],
                        'description' => 'and there\'s animal ears inside! (Oh, but don\'t worry: they\'re not real! See? Just Plastic and polyester!)'
                    ],
                    [
                        'items' => [
                            $squirrel3->rngNextFromArray([
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

        $loot = $squirrel3->rngNextFromArray($possibleLoot);

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
