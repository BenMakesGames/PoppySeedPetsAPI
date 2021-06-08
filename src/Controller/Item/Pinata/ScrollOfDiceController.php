<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/scroll")
 */
class ScrollOfDiceController extends PoppySeedPetsItemController
{
    /**
     * @Route("/dice/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function readScrollOfDice(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'scroll/dice/#/read');
        $this->validateHouseSpace($inventory, $inventoryService);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $n = $squirrel3->rngNextInt(1, 100);
        $howRead = '';

        if($n <= 40)
        {
            $dice = 4;
            $howRead = ', stumbling over some of the words';
        }
        else if($n <= 80)
            $dice = 5;
        else if($n < 90)
        {
            $dice = 6;
            $howRead = ' loud and clear';
        }
        else
        {
            $dice = 8;
            $howRead = ' with ' . $squirrel3->rngNextFromArray([
                'a booming voice',
                'a voice as vibrant as a rainbow',
                'a voice as smooth as Chocolate Syrup',
                'dramatic flair and perfectly-rolled "r"s'
            ]);
        }

        for($i = 0; $i < $dice; $i++)
        {
            $die = $squirrel3->rngNextFromArray([
                'Glowing Four-sided Die', 'Glowing Four-sided Die',
                'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die',
                'Glowing Eight-sided Die', 'Glowing Eight-sided Die',
            ]);

            $inventoryService->receiveItem($die, $user, $user, $user->getName() . ' found this in ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $lockedToOwner);
        }

        $em->remove($inventory);

        $em->flush();

        $message = 'You read the scroll' . $howRead . ', and the shapes of ' . $dice . ' dice form on its surface before suddenly popping out';

        if($squirrel3->rngNextInt(1, 5) === 1)
        {
            $message .= '! The scroll\'s magic is consumed in the process, reducing it to mundane Paper.';
            $inventoryService->receiveItem('Paper', $user, $user, 'The mundane remains of ' . $inventory->getItem()->getNameWithArticle() . ' read by ' . $user->getName() . '.', $location, $lockedToOwner);
        }
        else
            $message .= ', reducing the scroll to shreds!';

        $responseService->addFlashMessage($message);

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}