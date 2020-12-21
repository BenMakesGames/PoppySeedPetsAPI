<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Functions\ArrayFunctions;
use App\Repository\TraderRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\TraderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/linensAndThings")
 */
class LinensController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/rummage", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function rummageThroughLinens(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        $this->validateInventory($inventory, 'linensAndThings/#/rummage');
        $this->validateHouseSpace($inventory, $inventoryService);

        $user = $this->getUser();
        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $numberOfCloth = mt_rand(2, 3);

        for($i = 0; $i < $numberOfCloth; $i++)
            $inventoryService->receiveItem('White Cloth', $user, $user, $user->getName() . ' found this in a pile of Linens and Things.', $location, $lockedToOwner);

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess('You rummaged around in the pile, and pulled out ' . $numberOfCloth . ' pieces of good Cloth.', [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/giveToTrader", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function giveToTrader(
        Inventory $inventory, ResponseService $responseService, TraderService $traderService,
        EntityManagerInterface $em, TraderRepository $traderRepository
    )
    {
        $this->validateInventory($inventory, 'linensAndThings/#/giveToTrader');

        $user = $this->getUser();

        if(!$user->getUnlockedTrader())
            throw new UnprocessableEntityHttpException('On second thought, you realize you don\'t know anyone like that...');

        $trader = $traderRepository->findOneBy([ 'user' => $user->getId() ]);

        if(!$trader)
            throw new UnprocessableEntityHttpException('You should probably go visit the Trader first... at least once...');

        $traderService->recolorTrader($trader);

        $em->remove($inventory);
        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess($trader->getName() . ' thanks you for the new clothes, and changes into them immediately.', [ 'itemDeleted' => true ]);
    }
}
