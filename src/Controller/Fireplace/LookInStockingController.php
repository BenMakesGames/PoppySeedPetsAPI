<?php
namespace App\Controller\Fireplace;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Functions\ItemRepository;
use App\Functions\JewishCalendarFunctions;
use App\Functions\PlayerLogFactory;
use App\Functions\SpiceRepository;
use App\Functions\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/fireplace")]
class LookInStockingController extends AbstractController
{
    #[Route("/lookInStocking", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function lookInStocking(
        InventoryService $inventoryService, ResponseService $responseService, EntityManagerInterface $em,
        IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();
        $now = new \DateTimeImmutable();
        $monthAndDay = $now->format('md');

        if($monthAndDay < 1201)
            throw new PSPInvalidOperationException('It\'s not December!');

        $gotStockingPresent = UserQuestRepository::findOrCreate($em, $user, 'Got a Stocking Present', null);

        if($gotStockingPresent->getValue() === $now->format('Y-m-d'))
            throw new PSPInvalidOperationException('There\'s nothing else in the stocking. Maybe tomorrow?');

        $randomRewards = [
            [ 'Mint', true ],
            [ 'Chocolate Bar', true ],
            [ 'Charcoal', false ],
            [ 'Cheese', true ],
            [ 'Crystal Ball', false ],
            [ 'Fruit Basket', true ],
            [ 'Glowing Protojelly', false ],
            [ 'Wings', false ],
            [ 'Fluff', false ],
            [ 'Paper Bag', true ],
            [ 'Plastic Idol', false ],
            [ 'Quintessence', false ],
            [ 'Secret Seashell', false ],
            [ 'Rock', false ],
            [ 'Castella Cake', true ],
        ];

        $rewards = [
            null, // 1st
            [ 'Gold Key', false ], // 2nd - International Day for the Abolition of Slavery
            null, // 3rd
            [ 'World\'s Best Sugar Cookie', true ], // 4th - National Cookie Day
            [ 'Mysterious Seed', false ], // 5th - World Soil Day
            [ 'Blue Firework', false ], // 6th - Independence Day (Finland)
            [ 'Candle', false ], // 7th - Day of the Little Candles
            [ 'Fig', true ], // 8th - Bodhi Day
            [ 'Lutefisk', true ], // 9th - Anna's Day
            null, // 10th
            [ 'Liquid-hot Magma', false ], // 11th - International Mountain Day
            [ 'Bungee Cord', false ], // 12th - Jamhuri Day
            null, // 13th
            [ 'Bunch of Naners', true ], // 14th - Monkey Day
            [ 'Tea Leaves', true ], // 15th - International Tea Day
            [ 'Red Firework', false ], // 16th - Day of Reconciliation
            [ 'Red Umbrella', false ], // 17th - International Day to End Violence Against Sex Workers
            null, // 18th
            [ 'Behatting Scroll', false ], // 19th - no particular holiday; just want to give one of these out
            [ 'Scarf Bag', false ], // 20th - National Ugly Sweater Day (it's stupid, but sure)
            null, // 21st
            [ 'Compass (the Math Kind)', false ], // 22nd - National Mathematics Day
            [ 'Large Radish', true ], // 23rd - Night of the Radishes
            [ 'Fish', true ], // 24th - Feast of the Seven Fishes
            [ 'Santa Hat', false ], // 25th - Christmas
            [ 'Candle', false ], // 26th - 1st day of Kwanzaa (candle-lighting is listed among ceremonies)
            null, // 27th
            [ 'Corn', true ], // 28th - 3rd day of Kwanzaa (corn is listed among symbols)
            null, // 29th
            [ 'Apricot', true ], // 30th - 4th day of Kwanzaa (fresh fruit is listed among symbols)
            [ 'Music Note', false ], // 31st - New Year's Eve/Hogmanay
        ];

        $item = $rewards[$monthAndDay - 1201];

        if(!$item)
        {
            if(JewishCalendarFunctions::isHanukkah($now))
                $item = [ 'Dreidel', false ];
            else
                $item = $squirrel3->rngNextFromArray($randomRewards);
        }

        $itemObject = ItemRepository::findOneByName($em, $item[0]);

        $newItem = $inventoryService->receiveItem($item[0], $user, $user, $user->getName() . ' found this in a stocking over their Fireplace on ' . $now->format('M j, Y') . '.', LocationEnum::HOME, true);

        if($item[1])
        {
            $newItem->setSpice(
                SpiceRepository::findOneByName($em, $squirrel3->rngNextFromArray([
                    '5-Spice\'d',
                    'Autumnal',
                    'Buttery',
                    'Chocolate-covered',
                    'Ducky',
                    'Juniper',
                    'Nutmeg-laden',
                    'Starry',
                    'with Ponzu',
                ])),
            );
        }

        $messages = [
            'You reach into the stocking and feel around... eventually your fingers find something. You pull it out...',
            'You reach into the stocking, and in one, swift motion extract the gift inside...',
            'You up-end the stocking; something falls out, but you\'re ready...',
            'You squeeze the stocking like a tube of toothpaste, forcing its contents up, and out of the stocking\'s opening...',
            'You peer into the stocking, but all you see darkness. Carefully, you reach inside... and find something! You pull it out as quickly as possible!',
        ];

        $responseService->addFlashMessage(
            $squirrel3->rngNextFromArray($messages) . "\n\n" . ucfirst($itemObject->getNameWithArticle()) . '!'
        );

        $gotStockingPresent->setValue($now->format('Y-m-d'));

        PlayerLogFactory::create($em, $user, 'You pulled ' . $itemObject->getNameWithArticle() . ' from your stocking!', [ 'Fireplace', 'Special Event', 'Stocking Stuffing Season' ]);

        $em->flush();

        return $responseService->success();
    }
}
