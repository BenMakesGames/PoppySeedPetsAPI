<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Model\ItemQuantity;
use App\Repository\EnchantmentRepository;
use App\Repository\ItemRepository;
use App\Repository\SpiceRepository;
use App\Service\InventoryService;
use App\Service\PetRelationshipService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/magicPinecone")
 */
class MagicPineconeController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function raid(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, SpiceRepository $spiceRepository,
        Squirrel3 $squirrel3
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'magicPinecone/#/open');

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

        $juniper = $spiceRepository->findOneBy([ 'name' => 'Juniper' ]);

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
