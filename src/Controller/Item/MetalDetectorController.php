<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Repository\ItemRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/metalDetector")
 */
class MetalDetectorController extends AbstractController
{
    /**
     * @Route("/{inventory}/tune/iron", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function tuneMetalDetectorForIron(
        Inventory $inventory, ResponseService $responseService, ItemRepository $itemRepository,
        EntityManagerInterface $em
    )
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'metalDetector/#/tune/iron');

        $inventory->changeItem($itemRepository->findOneByName('Metal Detector (Iron)'));

        $em->flush();

        $responseService->setReloadPets($inventory->getHolder() !== null);
        $responseService->setReloadInventory($inventory->getHolder() === null);

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/tune/silver", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function tuneMetalDetectorForSilver(
        Inventory $inventory, ResponseService $responseService, ItemRepository $itemRepository,
        EntityManagerInterface $em
    )
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'metalDetector/#/tune/silver');

        $inventory->changeItem($itemRepository->findOneByName('Metal Detector (Silver)'));

        $em->flush();

        $responseService->setReloadPets($inventory->getHolder() !== null);
        $responseService->setReloadInventory($inventory->getHolder() === null);

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/tune/gold", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function tuneMetalDetectorForGold(
        Inventory $inventory, ResponseService $responseService, ItemRepository $itemRepository,
        EntityManagerInterface $em
    )
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'metalDetector/#/tune/gold');

        $inventory->changeItem($itemRepository->findOneByName('Metal Detector (Gold)'));

        $em->flush();

        $responseService->setReloadPets($inventory->getHolder() !== null);
        $responseService->setReloadInventory($inventory->getHolder() === null);

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
