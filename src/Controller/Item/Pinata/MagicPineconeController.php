<?php
declare(strict_types=1);

namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Functions\ArrayFunctions;
use App\Functions\SpiceRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/magicPinecone")]
class MagicPineconeController extends AbstractController
{
    #[Route("/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function raid(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'magicPinecone/#/open');

        $possibleItems = [
            'Sugar',
            'Sugar',
            'Aging Powder',
            'Baking Soda',
            'Baking Powder',
            'Agar-agar',
            'Cocoa Beans',
        ];

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $em->remove($inventory);

        $juniper = SpiceRepository::findOneByName($em, 'Juniper');

        $listOfItems = $squirrel3->rngNextSubsetFromArray($possibleItems, 3);

        $listOfItems[] = 'Blueberries';
        $listOfItems[] = 'Blueberries';

        sort($listOfItems);

        foreach($listOfItems as $itemName)
        {
            $item = $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);
            $item->setSpice($juniper);
        }

        $em->flush();

        return $responseService->itemActionSuccess('You open the Magic Pinecone! Whooooaa! There\'s ' . ArrayFunctions::list_nice($listOfItems) . ' inside!', [ 'itemDeleted' => true ]);
    }
}
