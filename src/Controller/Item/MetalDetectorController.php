<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Functions\ItemRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/metalDetector")]
class MetalDetectorController extends AbstractController
{
    #[Route("/{inventory}/tune/iron", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function tuneMetalDetectorForIron(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'metalDetector/#/tune/iron');

        $inventory->changeItem(ItemRepository::findOneByName($em, 'Metal Detector (Iron)'));

        $em->flush();

        $responseService->setReloadPets($inventory->getHolder() !== null);
        $responseService->setReloadInventory($inventory->getHolder() === null);

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/tune/silver", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function tuneMetalDetectorForSilver(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'metalDetector/#/tune/silver');

        $inventory->changeItem(ItemRepository::findOneByName($em, 'Metal Detector (Silver)'));

        $em->flush();

        $responseService->setReloadPets($inventory->getHolder() !== null);
        $responseService->setReloadInventory($inventory->getHolder() === null);

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/tune/gold", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function tuneMetalDetectorForGold(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'metalDetector/#/tune/gold');

        $inventory->changeItem(ItemRepository::findOneByName($em, 'Metal Detector (Gold)'));

        $em->flush();

        $responseService->setReloadPets($inventory->getHolder() !== null);
        $responseService->setReloadInventory($inventory->getHolder() === null);

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
