<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/item/takeApart")
 */
class TakeApartController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function doIt(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService
    )
    {
        $this->validateInventory($inventory, 'takeApart/#');
        $this->validateHouseSpace($inventory, $inventoryService);

        $user = $this->getUser();

        $takeApartTable = [
            'Glowing Russet Staff of Swiftness' => [
                'loot' => [ 'Hot Potato', 'Warm Potato', 'Potato' ],
                'verbing' => 'de-potato-ing'
            ],
            'Water Strider' => [
                'loot' => [ 'Hunting Spear', 'Cast Net' ],
                'verbing' => 'dismantling'
            ],
            'Lightning Axe' => [
                'loot' => [ 'Searing Blade', 'Searing Blade', 'Iron Bar' ],
                'verbing' => 'splitting',
            ]
        ];

        if(!array_key_exists($inventory->getItem()->getName(), $takeApartTable))
            throw new \Exception('Ben messed up and didn\'t make this item take-apartable :(');

        $info = $takeApartTable[$inventory->getItem()->getName()];

        foreach($info['loot'] as $item)
        {
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' received this by ' . $info['verbing'] . ' ' . $inventory->getItem()->getNameWithArticle() . '.', $inventory->getLocation(), $inventory->getLockedToOwner());
        }

        $em->remove($inventory);
        $em->flush();

        $responseService->addReloadInventory();

        return $responseService->itemActionSuccess(ucfirst($info['verbing']) . ' the ' . $inventory->getItem()->getName() . ' yielded ' . ArrayFunctions::list_nice($info['loot']) . '!', [ 'itemDeleted' => true ]);
    }
}
