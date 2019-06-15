<?php
namespace App\Controller;

use App\Enum\SerializationGroup;
use App\Repository\InventoryRepository;
use App\Service\ResponseService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/inventory")
 */
class InventoryController extends APIController
{
    /**
     * @Route("/my", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getMyInventory(ResponseService $responseService, InventoryRepository $inventoryRepository)
    {
        $inventoryRepository->findBy([ 'owner' => $this->getUser() ]);
        return $responseService->success($inventoryRepository, SerializationGroup::MY_INVENTORY);
    }

}