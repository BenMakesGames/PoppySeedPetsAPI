<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Repository\ItemRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/sandyLump")
 */
class SandyLumpController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{lump}/clean", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function clean(
        Inventory $lump, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, ItemRepository $itemRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($lump, 'sandyLump/#/clean');
        $this->validateHouseSpace($lump, $inventoryService);

        $location = $lump->getLocation();
        $lockedToOwner = $lump->getLockedToOwner();

        if(mt_rand(1, 25) === 1)
        {
            $item = ArrayFunctions::pick_one([
                'Secret Seashell',
                ArrayFunctions::pick_one([ 'Striped Microcline', 'Blackonite' ]),
                'Dino Skull',
                'Key Ring',
                ArrayFunctions::pick_one([ 'Meteorite', 'Species Transmigration Serum' ]),
            ]);
        }
        else
        {
            $item = ArrayFunctions::pick_one([
                'Iron Ore', 'Iron Ore', 'Silver Ore', 'Gold Ore',
                'Silica Grounds', 'Silica Grounds', 'Sand Dollar',
                'Talon',
                'Fish', 'Mermaid Egg', 'Mermaid Egg', 'Seaweed',
            ]);
        }

        $itemObject = $itemRepository->findOneByName($item);

        $userStatsRepository->incrementStat($user, 'Cleaned a ' . $lump->getItem()->getName());

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

        return $responseService->itemActionSuccess($message, [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}
