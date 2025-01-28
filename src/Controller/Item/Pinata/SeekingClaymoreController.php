<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Exceptions\PSPInvalidOperationException;
use App\Functions\ArrayFunctions;
use App\Functions\ItemRepository;
use App\Model\ItemQuantity;
use App\Model\Music;
use App\Repository\InventoryRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/seekingClaymore")]
class SeekingClaymoreController extends AbstractController
{
    #[Route("/{inventory}/tune", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function tune(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'seekingClaymore/#/tune');

        $previousTunings = self::countTunings($inventory);

        if($previousTunings >= 3)
            throw new PSPInvalidOperationException('Try as you might, the claymore refuses to be tuned again. It seems three tunings is all you can get out of the thing.');

        $possibleItems = array_filter(
            [
                'Meat-seeking Claymore',
                'Sweet-seeking Claymore',
                'Wheat-seeking Claymore',
                'Beat-seeking Claymore',
                'Sheet-seeking Claymore',
                'Peat-seeking Claymore'
            ],
            fn($itemName) => $inventory->getItem()->getName() !== $itemName
        );

        $newItem = $rng->rngNextFromArray($possibleItems);

        $inventory
            ->addComment($user->getName() . ' retuned a ' . $inventory->getItem()->getName() . ' into a ' . $newItem . '.')
            ->changeItem(ItemRepository::findOneByName($em, $newItem))
        ;

        return $responseService
            ->setReloadInventory()
            ->itemActionSuccess(
                'You tweak the claymore\'s various knobs and dials, and it begins to hum with a new purpose. It is now a ' . $newItem . '!',
                [ 'itemDeleted' => true ]
            );

    }

    private static function countTunings(Inventory $inventory): int
    {
        $numberOfTunings = 0;

        foreach($inventory->getComments() as $comment)
        {
            if(str_contains($comment, ' retuned a ') && str_contains($comment, ' into a '))
                $numberOfTunings++;
        }

        return $numberOfTunings;
    }

    #[Route("/{inventory}/seekMeat", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function seekMeat(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'seekingClaymore/#/seekMeat');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        $bucket = self::getBucketOrThrow($em, $user, $location);

        $items = array_merge(
            [ 'Fish', 'Fish', 'Toad Legs' ],
            $rng->rngNextSubsetFromArray([
                'Fish', 'Fish', 'Fish', 'Fish', 'Fish', 'Fish',
                'Toad Legs', 'Toad Legs',
                'Hot Dog',
                'Giant Turkey Leg',
                'Tentacle', 'Tentacle', 'Tentacle',
                'Worms', 'Worms',
                'Hot Wings',
            ], 5)
        );

        sort($items);

        self::receiveItems($inventoryService, $inventory, $items);

        $em->remove($inventory);
        $em->flush();

        return self::generateResponse($responseService, $bucket, $items);
    }

    #[Route("/{inventory}/seekSweet", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function seekSweet(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'seekingClaymore/#/seekSweet');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        $bucket = self::getBucketOrThrow($em, $user, $location);

        $items = array_merge(
            $rng->rngNextFromArray([
                [ 'Blue Hard Candy', 'Orange Hard Candy', 'Purple Hard Candy', 'Red Hard Candy', 'Yellow Hard Candy' ],
                [ 'Blue Gummies', 'Green Gummies', 'Orange Gummies', 'Purple Gummies', 'Red Gummies', 'Yellow Gummies' ]
            ]),
            $rng->rngNextSubsetFromArray([
                'Blue Hard Candy', 'Blue Hard Candy',
                'Orange Hard Candy', 'Orange Hard Candy',
                'Purple Hard Candy', 'Purple Hard Candy',
                'Red Hard Candy', 'Red Hard Candy',
                'Yellow Hard Candy', 'Yellow Hard Candy',
                'Blue Gummies', 'Blue Gummies',
                'Green Gummies', 'Green Gummies',
                'Orange Gummies', 'Orange Gummies',
                'Purple Gummies', 'Purple Gummies',
                'Red Gummies', 'Red Gummies',
                'Yellow Gummies', 'Yellow Gummies',
                'Konpeitō', 'Konpeitō',
                'Gummy Worms', 'Gummy Worms',
                'Rock Candy', 'Rock Candy',
                'A Single Candy Cane',
            ], 8)
        );

        sort($items);

        self::receiveItems($inventoryService, $inventory, $items);

        $em->remove($inventory);
        $em->flush();

        return self::generateResponse($responseService, $bucket, $items);
    }

    #[Route("/{inventory}/seekWheat", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function seekWheat(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'seekingClaymore/#/seekWheat');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        $bucket = self::getBucketOrThrow($em, $user, $location);

        $items = array_merge(
            [ 'Wheat', 'Wheat', 'Wheat', 'Wheat Flour' ],
            $rng->rngNextSubsetFromArray([
                'Wheat', 'Wheat', 'Wheat', 'Wheat', 'Wheat', 'Wheat', 'Wheat', 'Wheat',
                'Wheat Flour', 'Wheat Flour',
                'Wheat Flower', 'Wheat Flower',
                'Slice of Bread', 'Slice of Bread',
                'Tile: Bakery Bites', 'Pie Crust'
            ], 7)
        );

        sort($items);

        self::receiveItems($inventoryService, $inventory, $items);

        $em->remove($inventory);
        $em->flush();

        return self::generateResponse($responseService, $bucket, $items);
    }

    #[Route("/{inventory}/seekBeat", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function seekBeat(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'seekingClaymore/#/seekBeat');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        $bucket = self::getBucketOrThrow($em, $user, $location);

        $items = array_merge(
            [ 'Music Note', 'Music Note', 'Music Note', 'Music Note', 'Music Note', 'Music Note' ],
            $rng->rngNextSubsetFromArray([
                'Music Note', 'Music Note',
                'Sweet Beat',
                'Gold Triangle',
                'Drumpkin',
                'Maraca',
                'Potion of Music',
            ], 3)
        );

        sort($items);

        self::receiveItems($inventoryService, $inventory, $items);

        $em->remove($inventory);
        $em->flush();

        return self::generateResponse($responseService, $bucket, $items);
    }

    #[Route("/{inventory}/seekSheet", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function seekSheet(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'seekingClaymore/#/seekSheet');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        $bucket = self::getBucketOrThrow($em, $user, $location);

        $items = array_merge(
            [ 'Paper', 'Paper', 'Paper', 'White Cloth' ],
            $rng->rngNextSubsetFromArray([
                'Bananananers Foster Recipe', 'Cobbler Recipe', 'Gochujang Recipe',
                'Carrot Wine Recipe',
                'Spirit Polymorph Potion Recipe', 'Stroganoff Recipe', 'Welcome Note',

                'Paper',

                'Tiny Scroll of Resources',
                $rng->rngNextFromArray([ 'Tiny Scroll of Resources', 'Scroll of Resources' ]),

                $rng->rngNextFromArray([
                    'Scroll of Fruit',
                    'Minor Scroll of Riches',
                    'Major Scroll of Riches',
                    'Scroll of Chocolate',
                    'Scroll of Dice',
                    'Scroll of the Sea',
                    'Farmer\'s Scroll',
                ]),

                'Chocolate-stained Cloth', 'Filthy Cloth',
                'White Cloth', 'White Cloth', 'White Cloth', 'White Cloth',
            ], 4)
        );

        sort($items);

        self::receiveItems($inventoryService, $inventory, $items);

        $em->remove($inventory);
        $em->flush();

        return self::generateResponse($responseService, $bucket, $items);
    }

    #[Route("/{inventory}/seekPeat", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function seekPeat(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'seekingClaymore/#/seekPeat');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        $bucket = self::getBucketOrThrow($em, $user, $location);

        $items = [ 'Whole Bucket-worth of Peat' ];

        self::receiveItems($inventoryService, $inventory, $items);

        $em->remove($inventory);
        $em->flush();

        return self::generateResponse($responseService, $bucket, $items);
    }

    private static function getBucketOrThrow(EntityManagerInterface $em, User $user, string $location): Inventory
    {
        $bucket = InventoryRepository::findAnyOneFromItemGroup($em, $user, 'Bucket', [ $location ]);

        if(!$bucket)
            throw new PSPInvalidOperationException('The claymore seems confused. You get the impression it\'s looking for some kind of _bucket_ to help it carry back whatever it finds...');

        return $bucket;
    }

    private static function receiveItems(InventoryService $inventoryService, Inventory $inventory, array $items): void
    {
        $user = $inventory->getOwner();
        $location = $inventory->getLocation();
        $itemSpice = $inventory->getSpice();

        $inventoryService->receiveItem('Wings', $user, $user, 'This is all that remains of ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());

        foreach($items as $item)
        {
            $inventoryService->receiveItem($item, $user, $user, 'This was found by ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner())
                ->setSpice($itemSpice);
        }
    }

    private static function generateResponse(ResponseService $responseService, Inventory $bucket, array $items): JsonResponse
    {
        return $responseService->itemActionSuccess(
            'The claymore flies off with ' . $bucket->getItem()->getNameWithArticle() . '. A little while later, the wings return, carrying ' . ArrayFunctions::list_nice($items) . '! (It seems the blade itself was lost in the adventure!)',
            [ 'itemDeleted' => true ]
        );
    }
}
