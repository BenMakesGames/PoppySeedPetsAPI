<?php
namespace App\Controller\Fireplace;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Functions\ItemRepository;
use App\Functions\JewishCalendarFunctions;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route("/fireplace")]
class LookInStockingController extends AbstractController
{
    #[Route("/lookInStocking", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function lookInStocking(
        InventoryService $inventoryService, ResponseService $responseService, EntityManagerInterface $em,
        UserQuestRepository $userQuestRepository, IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();
        $now = new \DateTimeImmutable();
        $monthAndDay = $now->format('md');

        if($monthAndDay < 1201)
            throw new PSPInvalidOperationException('It\'s not December!');

        $gotStockingPresent = $userQuestRepository->findOrCreate($user, 'Got a Stocking Present', null);

        if($gotStockingPresent->getValue() === $now->format('Y-m-d'))
            throw new PSPInvalidOperationException('There\'s nothing else in the stocking. Maybe tomorrow?');

        $randomRewards = [
            'Mint', 'Chocolate Bar', 'Charcoal', 'Cheese', 'Crystal Ball', 'Fruit Basket',
            'Glowing Four-sided Die', 'Glowing Six-sided Die', 'Glowing Eight-sided Die', 'Wings',
            'Fluff', 'Paper Bag', 'Plastic Idol', 'Quintessence', 'Secret Seashell', 'Rock',
            'Castella Cake'
        ];

        $rewards = [
            null, // 1st
            'Gold Key', // 2nd - International Day for the Abolition of Slavery
            null, // 3rd
            'World\'s Best Sugar Cookie', // 4th - National Cookie Day
            'Mysterious Seed', // 5th - World Soil Day
            'Blue Firework', // 6th - Independence Day (Finland)
            'Candle', // 7th - Day of the Little Candles
            'Fig', // 8th - Bodhi Day
            'Lutefisk', // 9th - Anna's Day
            null, // 10th
            'Liquid-hot Magma', // 11th - International Mountain Day
            'Bungee Cord', // 12th - Jamhuri Day
            null, // 13th
            'Bunch of Naners', // 14th - Monkey Day
            'Tea Leaves', // 15th - International Tea Day
            'Red Firework', // 16th - Day of Reconciliation
            'Red Umbrella', // 17th - International Day to End Violence Against Sex Workers
            null, // 18th
            'Behatting Scroll', // 19th - no particular holiday; just want to give one of these out
            'String', // 20th - National Ugly Sweater Day (it's stupid, but sure)
            null, // 21st
            'Compass (the Math Kind)', // 22nd - National Mathematics Day
            'Large Radish', // 23rd - Night of the Radishes
            'Fish', // 24th - Feast of the Seven Fishes
            'Santa Hat', // 25th - Christmas
            'Candle', // 26th - 1st day of Kwanzaa (candle-lighting is listed among ceremonies)
            null, // 27th
            'Corn', // 28th - 3rd day of Kwanzaa (corn is listed among symbols)
            null, // 29th
            'Apricot', // 30th - 4th day of Kwanzaa (fresh fruit is listed among symbols)
            'Music Note', // 31st - New Year's Eve/Hogmanay
        ];

        $item = $rewards[$monthAndDay - 1201];

        if(!$item)
        {
            if(JewishCalendarFunctions::isHanukkah($now))
                $item = 'Dreidel';
            else
                $item = $squirrel3->rngNextFromArray($randomRewards);
        }

        $itemObject = ItemRepository::findOneByName($em, $item);

        $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' found this in a stocking over their Fireplace on ' . $now->format('M j, Y') . '.', LocationEnum::HOME, true);

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

        $em->flush();

        return $responseService->success();
    }
}
