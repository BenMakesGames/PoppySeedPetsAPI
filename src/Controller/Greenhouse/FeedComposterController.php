<?php
namespace App\Controller\Greenhouse;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ArrayFunctions;
use App\Functions\PlayerLogHelpers;
use App\Functions\RequestFunctions;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\SpiceRepository;
use App\Repository\UserStatsRepository;
use App\Service\GreenhouseService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/greenhouse")
 */
class FeedComposterController extends AbstractController
{
    public const FORBIDDEN_COMPOST = [
        'Small Bag of Fertilizer',
        'Bag of Fertilizer',
        'Large Bag of Fertilizer',
        'Twilight Fertilizer'
    ];

    /**
     * @Route("/composter/feed", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function feedComposter(
        ResponseService $responseService, Request $request, InventoryRepository $inventoryRepository,
        InventoryService $inventoryService, EntityManagerInterface $em, UserStatsRepository $userStatsRepository,
        SpiceRepository $spiceRepository, Squirrel3 $squirrel3, GreenhouseService $greenhouseService
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->getGreenhouse())
            throw new PSPNotUnlockedException('Greenhouse');

        if(!$user->getGreenhouse()->getHasComposter())
            throw new PSPNotUnlockedException('Composter');

        $itemIds = RequestFunctions::getUniqueIdsOrThrow($request, 'food', 'No items were selected as fuel???');

        $items = $inventoryRepository->findFertilizers($user, $itemIds);

        $items = array_filter($items, function(Inventory $i)  {
            return !in_array($i->getItem()->getName(), self::FORBIDDEN_COMPOST);
        });

        if(count($items) < count($itemIds))
            throw new PSPNotFoundException('Some of the compost items selected could not be found. That shouldn\'t happen. Reload and try again, maybe?');

        $totalFertilizer = $user->getGreenhouse()->getComposterFood();

        $tossedItemNames = [];

        foreach($items as $item)
        {
            $totalFertilizer += $item->getItem()->getFertilizer();
            $tossedItemNames[] = $item->getFullItemName();
        }

        $remainingFertilizer = $totalFertilizer;

        $largeBags = (int)($remainingFertilizer / 20);

        $remainingFertilizer -= $largeBags * 20;

        $mediumBags = (int)($remainingFertilizer / 15);

        $remainingFertilizer -= $mediumBags * 15;

        $smallBags = (int)($remainingFertilizer / 10);

        $remainingFertilizer -= $smallBags * 10;

        $itemDelta = $largeBags + $mediumBags + $smallBags - count($items);

        if($itemDelta > 0)
        {
            $itemsAtHome = $inventoryService->countTotalInventory($user, LocationEnum::HOME);

            if($itemsAtHome > 100)
                throw new PSPInvalidOperationException('That would leave you with more items at home than you started with, and you\'re already over 100!');

            if($itemsAtHome + $itemDelta > 100)
                throw new PSPInvalidOperationException('That would leave you with ' . ($itemsAtHome + $itemDelta) . ' items at home. (100 is the usual limit.)');
        }

        foreach($items as $item)
            $em->remove($item);

        $userStatsRepository->incrementStat($user, UserStatEnum::ITEMS_COMPOSTED, count($items));

        $user->getGreenhouse()
            ->setComposterFood($remainingFertilizer)
            ->decreaseComposterBonusCountdown($totalFertilizer)
        ;

        $bonusItemNames = [];

        while($user->getGreenhouse()->getComposterBonusCountdown() <= 0)
        {
            $user->getGreenhouse()->setComposterBonusCountdown();

            $bugs = [ 'Centipede', 'Stink Bug' ];

            if($user->getBeehive())
                $bugs[] = 'Bee Larva';

            $bonusItem = ItemRepository::findOneByName($em, $squirrel3->rngNextFromArray([
                $squirrel3->rngNextFromArray([ 'Talon', 'Silica Grounds', 'Secret Seashell', 'Brown Bow' ]),
                $squirrel3->rngNextFromArray($bugs),
                'Grandparoot',
                'Toadstool',
                'String', // let it get rancid
                $squirrel3->rngNextFromArray([ 'Iron Ore', 'Iron Ore', 'Silver Ore', 'Gold Ore', 'Worms' ]),
                'Paper Bag',
            ]));

            $bonusItemNames[] = $bonusItem->getNameWithArticle();

            if($bonusItem->getName() === 'Paper Bag')
                $theBonusItem = $inventoryService->receiveItem($bonusItem, $user, $user, $user->getName() . ' found this in their composter. (Its contents are PROBABLY safe to eat?)', LocationEnum::HOME, false);
            else
                $theBonusItem = $inventoryService->receiveItem($bonusItem, $user, $user, $user->getName() . ' found this in their composter.', LocationEnum::HOME, false);

            if($bonusItem->getName() === 'String' || $bonusItem->getName() === 'Grandparoot' || $bonusItem->getName() === 'Paper Bag')
                $theBonusItem->setSpice($spiceRepository->findOneByName('Rancid'));
        }

        for($i = 0; $i < $largeBags; $i++)
            $inventoryService->receiveItem('Large Bag of Fertilizer', $user, $user, $user->getName() . ' made this using their composter.', LocationEnum::HOME, false);

        for($i = 0; $i < $mediumBags; $i++)
            $inventoryService->receiveItem('Bag of Fertilizer', $user, $user, $user->getName() . ' made this using their composter.', LocationEnum::HOME, false);

        for($i = 0; $i < $smallBags; $i++)
            $inventoryService->receiveItem('Small Bag of Fertilizer', $user, $user, $user->getName() . ' made this using their composter.', LocationEnum::HOME, false);

        $got = [];

        if($largeBags > 0)
            $got[] = $largeBags === 1 ? 'one Large Bag of Fertilizer' : ($largeBags . ' Large Bags of Fertilizer');

        if($mediumBags > 0)
            $got[] = $mediumBags === 1 ? 'one Bag of Fertilizer' : ($mediumBags . ' Bags of Fertilizer');

        if($smallBags > 0)
            $got[] = $smallBags === 1 ? 'one Small Bag of Fertilizer' : ($smallBags . ' Small Bags of Fertilizer');

        if(count($got) > 0)
        {
            $gotDescription = ' ' . ArrayFunctions::list_nice($got);
            if(count($bonusItemNames) > 0)
                $gotDescription .= ', _and also_ ' . ArrayFunctions::list_nice($bonusItemNames);
            $gotDescription .= '!';
        }
        else if(count($bonusItemNames) > 0)
            $gotDescription = ' ' . ArrayFunctions::list_nice($bonusItemNames) . '!';
        else
            $gotDescription = '... nothing, yet (but you\'re making progress!)';

        if(count($tossedItemNames) > 5)
        {
            PlayerLogHelpers::Create(
                $em,
                $user,
                'You chucked ' . count($tossedItemNames) . ' into the Composter, and got' . $gotDescription,
                [ 'Greenhouse' ]
            );
        }
        else
        {
            $objectOrObjects = count($tossedItemNames) == 1 ? 'object' : 'objects';
            PlayerLogHelpers::Create(
                $em,
                $user,
                'You chucked ' . ArrayFunctions::list_nice($tossedItemNames) . ' into the Composter, and got' . $gotDescription,
                [ 'Greenhouse' ]
            );
        }

        $em->flush();

        $thoseOrThat = count($bonusItemNames) === 1 ? 'that' : 'those';

        if(count($got) > 0)
        {
            if(count($bonusItemNames) > 0)
                $responseService->addFlashMessage('You got ' . ArrayFunctions::list_nice($got) . '! Also, ' . ArrayFunctions::list_nice($bonusItemNames) . ' fell out! (Where\'d ' . $thoseOrThat . ' come from?)');
            else
                $responseService->addFlashMessage('You got ' . ArrayFunctions::list_nice($got) . '!');
        }
        else
        {
            if(count($bonusItemNames) > 0)
                $responseService->addFlashMessage('That wasn\'t quite enough to make a bag of fertilizer... but it\'s progress! Oh, and wait, what? ' . ucfirst(ArrayFunctions::list_nice($bonusItemNames)) . ' fell out! (Where\'d ' . $thoseOrThat . ' come from ?)');
            else
                $responseService->addFlashMessage('That wasn\'t quite enough to make a bag of fertilizer... but it\'s progress!');
        }

        return $responseService->success(
            $greenhouseService->getGreenhouseResponseData($user),
            [ SerializationGroupEnum::GREENHOUSE_PLANT, SerializationGroupEnum::MY_GREENHOUSE, SerializationGroupEnum::HELPER_PET ]
        );
    }
}
