<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Repository\ItemRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/sandyLump")
 */
class SandyLumpController extends AbstractController
{
    /**
     * @Route("/{lump}/clean", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function clean(
        Inventory $lump, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $lump, 'sandyLump/#/clean');
        ItemControllerHelpers::validateHouseSpace($lump, $inventoryService);

        $location = $lump->getLocation();
        $lockedToOwner = $lump->getLockedToOwner();

        if($squirrel3->rngNextInt(1, 25) === 1)
        {
            $item = $squirrel3->rngNextFromArray([
                'Secret Seashell',
                $squirrel3->rngNextFromArray([ 'Striped Microcline', 'Blackonite' ]),
                'Dino Skull',
                'Key Ring',
                $squirrel3->rngNextFromArray([ 'Meteorite', 'Species Transmigration Serum' ]),
            ]);
        }
        else
        {
            $item = $squirrel3->rngNextFromArray([
                'Iron Ore', 'Iron Ore', 'Silver Ore', 'Gold Ore',
                'Silica Grounds', 'Silica Grounds', 'Sand Dollar',
                'Talon',
                'Fish', 'Mermaid Egg', 'Mermaid Egg', 'Seaweed',
            ]);
        }

        $itemObject = ItemRepository::findOneByName($em, $item);

        UserStatsRepository::incrementStat($em, $user, 'Cleaned a ' . $lump->getItem()->getName());

        if($item === 'Silica Grounds')
        {
            $inventoryService->receiveItem($itemObject, $user, $lump->getCreatedBy(), $user->getName() . ' found this covered in Silica Grounds.', $location, $lockedToOwner);
            $message = 'You begin the clean the object, but realize it\'s just Silica Grounds all the way through! (Well... at least you got some Silica Grounds, I guess?)';
        }
        else
        {
            $inventoryService->receiveItem($itemObject, $user, $lump->getCreatedBy(), $user->getName() . ' found this covered in Silica Grounds.', $location, $lockedToOwner);
            $message = 'You clean off the object, which reveals itself to be ' . $itemObject->getNameWithArticle() . '!';
        }

        $em->remove($lump);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
