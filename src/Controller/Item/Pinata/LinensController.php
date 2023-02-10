<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Functions\ArrayFunctions;
use App\Repository\TraderRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\TraderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/linensAndThings")
 */
class LinensController extends AbstractController
{
    /**
     * @Route("/{inventory}/rummage", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function rummageThroughLinens(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, Squirrel3 $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'linensAndThings/#/rummage');
        ItemControllerHelpers::validateHouseSpace($inventory, $inventoryService);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $baseNumberOfCloth = $squirrel3->rngNextInt(1, 2);

        if($squirrel3->rngNextInt(1, 2) === 1)
        {
            $extraItem = $squirrel3->rngNextInt(1, 10) == 1
                ? $squirrel3->rngNextFromArray([ '4-function Calculator', 'Coconut', 'Glowing Six-sided Die', 'Music Note', 'Paper', 'Password', 'Red Hard Candy', 'Sand Dollar', 'Spider', 'Tentacle' ])
                : 'Filthy Cloth';
        }
        else
            $extraItem = 'White Cloth';

        $inventoryService->receiveItem($extraItem, $user, $user, $user->getName() . ' found this in a pile of Linens and Things.', $location, $lockedToOwner);

        for($i = 0; $i < $baseNumberOfCloth; $i++)
            $inventoryService->receiveItem('White Cloth', $user, $user, $user->getName() . ' found this in a pile of Linens and Things.', $location, $lockedToOwner);

        $em->remove($inventory);
        $em->flush();

        if($extraItem === 'White Cloth')
            return $responseService->itemActionSuccess('You rummaged around in the pile, and pulled out ' . ($baseNumberOfCloth + 1) . ' pieces of good cloth...', [ 'itemDeleted' => true ]);
        else if($extraItem === 'Filthy Cloth')
            return $responseService->itemActionSuccess('You rummaged around in the pile, and pulled out ' . $baseNumberOfCloth . ' ' . ($baseNumberOfCloth === 1 ? 'piece' : 'pieces') . ' of good cloth... and 1 piece of BAD cloth...', [ 'itemDeleted' => true ]);
        else
            return $responseService->itemActionSuccess('You rummaged around in the pile, and pulled out ' . $baseNumberOfCloth . ' ' . ($baseNumberOfCloth === 1 ? 'piece' : 'pieces') . ' of good cloth, and what\'s this? Tangled up in the folds of cloth is a ' . $extraItem . '!', [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/giveToTrader", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function giveToTrader(
        Inventory $inventory, ResponseService $responseService, Squirrel3 $rng,
        EntityManagerInterface $em, TraderRepository $traderRepository
    )
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'linensAndThings/#/giveToTrader');

        $user = $this->getUser();

        if(!$user->getUnlockedTrader())
            throw new UnprocessableEntityHttpException('On second thought, you realize you don\'t know anyone like that...');

        $trader = $traderRepository->findOneBy([ 'user' => $user->getId() ]);

        if(!$trader)
            throw new UnprocessableEntityHttpException('You should probably go visit the Trader first... at least once...');

        TraderService::recolorTrader($rng, $trader);

        $em->remove($inventory);
        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess($trader->getName() . ' thanks you for the new clothes, and changes into them immediately.', [ 'itemDeleted' => true ]);
    }
}
