<?php
declare(strict_types=1);

namespace App\Controller\Inventory;

use App\Entity\User;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotFoundException;
use App\Repository\InventoryRepository;
use App\Service\RecyclingService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/inventory")]
class ThrowAwayController extends AbstractController
{
    #[Route("/throwAway", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function throwAway(
        Request $request, ResponseService $responseService, InventoryRepository $inventoryRepository,
        EntityManagerInterface $em, RecyclingService $recyclingService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $inventoryIds = $request->request->all('inventory');

        if(count($inventoryIds) > 200)
            throw new PSPFormValidationException('Oh, goodness, please don\'t try to recycle more than 200 items at a time. Sorry.');

        $inventory = $inventoryRepository->findBy([
            'owner' => $user,
            'id' => $inventoryIds
        ]);

        if(count($inventory) !== count($inventoryIds))
            throw new PSPNotFoundException('Some of the items could not be found??');

        $idsNotRecycled = $recyclingService->recycleInventory($user, $inventory);

        $em->flush();

        return $responseService->success($idsNotRecycled);
    }
}
