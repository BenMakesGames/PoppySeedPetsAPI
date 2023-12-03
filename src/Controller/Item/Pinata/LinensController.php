<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Repository\TraderRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\TraderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @Route("/item/linensAndThings")
 */
class LinensController extends AbstractController
{
    #[Route("/{inventory}/rummage", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function rummageThroughLinens(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'linensAndThings/#/rummage');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $baseNumberOfCloth = $squirrel3->rngNextInt(1, 2);

        $extraItem = $squirrel3->rngNextFromArray([ 'White Cloth', 'Super-wrinkled Cloth' ]);

        $inventoryService->receiveItem($extraItem, $user, $user, $user->getName() . ' found this in a pile of Linens and Things.', $location, $lockedToOwner);

        for($i = 0; $i < $baseNumberOfCloth; $i++)
            $inventoryService->receiveItem('White Cloth', $user, $user, $user->getName() . ' found this in a pile of Linens and Things.', $location, $lockedToOwner);

        $em->remove($inventory);
        $em->flush();

        if($extraItem === 'Super-wrinkled Cloth')
            return $responseService->itemActionSuccess('You rummaged around in the pile, and pulled out ' . $baseNumberOfCloth . ' ' . ($baseNumberOfCloth === 1 ? 'piece' : 'pieces') . ' of good cloth... and 1 piece of Super-wrinkled Cloth...', [ 'itemDeleted' => true ]);
        else
            return $responseService->itemActionSuccess('You rummaged around in the pile, and pulled out ' . ($baseNumberOfCloth + 1) . ' pieces of good cloth...', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/giveToTrader", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function giveToTrader(
        Inventory $inventory, ResponseService $responseService, IRandom $rng,
        EntityManagerInterface $em, TraderRepository $traderRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'linensAndThings/#/giveToTrader');

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Trader))
            throw new PSPNotUnlockedException('Trader');

        $trader = $traderRepository->findOneBy([ 'user' => $user->getId() ]);

        if(!$trader)
            throw new PSPInvalidOperationException('You should probably go visit the Trader first... at least once...');

        TraderService::recolorTrader($rng, $trader);

        $em->remove($inventory);
        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess($trader->getName() . ' thanks you for the new clothes, and changes into them immediately.', [ 'itemDeleted' => true ]);
    }
}
