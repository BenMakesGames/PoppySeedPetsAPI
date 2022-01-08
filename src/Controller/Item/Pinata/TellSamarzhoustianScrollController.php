<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Model\ItemQuantity;
use App\Repository\EnchantmentRepository;
use App\Repository\ItemRepository;
use App\Service\InventoryService;
use App\Service\PetRelationshipService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/tellSamarzhoustianDelights")
 */
class TellSamarzhoustianScrollController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, Squirrel3 $squirrel3
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'tellSamarzhoustianDelights/#/open');

        $ingredients = [
            'Algae',
            'Celery',
            'Corn',
            'Jellyfish Jelly',
            'Noodles',
            'Onion',
            'Seaweed',
        ];

        $spices = [
            'Nutmeg',
            'Onion Powder',
            'Spicy Spice',
            'Duck Sauce',
        ];

        $fancyItems = [
            'Everlasting Syllabub',
            'Bizet Cake',
            'Chili Calamari',
            'Mushroom Broccoli Krahi',
            'Poutine',
            'Qatayef',
            'Red Cobbler',
            'Shakshouka',
            'Tentacle Fried Rice',
            'Tentacle Onigiri',
        ];

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $em->remove($inventory);

        $listOfItems = [
            $squirrel3->rngNextFromArray($ingredients),
            $squirrel3->rngNextFromArray($spices),
            $squirrel3->rngNextFromArray($fancyItems),
        ];

        foreach($listOfItems as $itemName)
        {
            $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);
        }

        $em->flush();

        return $responseService->itemActionSuccess('You read the scroll, producing ' . ArrayFunctions::list_nice($listOfItems) . '!', [ 'itemDeleted' => true ]);
    }
}
