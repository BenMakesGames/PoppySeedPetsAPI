<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Model\ItemQuantity;
use App\Repository\ItemRepository;
use App\Service\InventoryService;
use App\Service\PetRelationshipService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/album")
 */
class AlbumController extends PoppySeedPetsItemController
{
    public const GENRES = [
        'Salsa',
        'Meringue',
        'Rock',
        'Bubblegum'
    ];

    /**
     * @Route("/single/{inventory}/listen", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function listenToSingle(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, ItemRepository $itemRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'album/single/#/listen');

        $location = $inventory->getLocation();

        $musicNotes = new ItemQuantity();
        $musicNotes->item = $itemRepository->findOneByName('Music Note');
        $musicNotes->quantity = mt_rand(3, 4);

        $extraItem = ArrayFunctions::pick_one([ 'Pointer', 'Quintessence' ]);

        $inventoryService->giveInventory($musicNotes, $user, $user, $user->getName() . ' got this by listening to a Single.', $location);
        $inventoryService->receiveItem($extraItem, $user, $user, $user->getName() . ' got this by listening to a Single.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You received ' . $musicNotes->quantity . ' music notes, and a ' . $extraItem . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/EP/{inventory}/listen", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function listenToEP(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, ItemRepository $itemRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'album/EP/#/listen');

        $location = $inventory->getLocation();

        $musicNotes = new ItemQuantity();
        $musicNotes->item = $itemRepository->findOneByName('Music Note');
        $musicNotes->quantity = mt_rand(4, 6);

        $genre = ArrayFunctions::pick_one(self::GENRES);
        $extraItem = ArrayFunctions::pick_one([ 'NUL', 'Quintessence' ]);

        $inventoryService->giveInventory($musicNotes, $user, $user, $user->getName() . ' got this by listening to an EP.', $location);
        $inventoryService->receiveItem($genre, $user, $user, $user->getName() . ' got this by listening to a Single.', $location);
        $inventoryService->receiveItem($extraItem, $user, $user, $user->getName() . ' got this by listening to a Single.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('Ah yes: your favorite genre, ' . $genre . '.' . "\n\n" . 'You also received ' . $musicNotes->quantity . ' Music Notes, and a ' . $extraItem . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/LP/{inventory}/listen", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function listenToLP(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, ItemRepository $itemRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'album/LP/#/listen');

        $location = $inventory->getLocation();

        $musicNotes = new ItemQuantity();
        $musicNotes->item = $itemRepository->findOneByName('Music Note');
        $musicNotes->quantity = mt_rand(4, 6);

        $genre = ArrayFunctions::pick_one([ 'Salsa', 'Meringue', 'Rock', 'Rock', 'Bubblegum' ]);

        $extraItems = [
            ArrayFunctions::pick_one([ 'Pointer', 'Quintessence' ]),
            'Pointer',
            'Quintessence'
        ];

        sort($extraItems);

        $inventoryService->giveInventory($musicNotes, $user, $user, $user->getName() . ' got this by listening to an LP.', $location);
        $inventoryService->receiveItem($genre, $user, $user, $user->getName() . ' got this by listening to a Single.', $location);

        foreach($extraItems as $extraItem)
            $inventoryService->receiveItem($extraItem, $user, $user, $user->getName() . ' got this by listening to a Single.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('Ah yes: your favorite genre, ' . $genre . '.' . "\n\n" . 'You also received ' . $musicNotes->quantity . ' Music Notes, ' . ArrayFunctions::list_nice($extraItems) . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}