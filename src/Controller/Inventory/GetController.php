<?php
declare(strict_types=1);

namespace App\Controller\Inventory;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Repository\InventoryRepository;
use App\Service\Filter\InventoryFilterService;
use App\Service\ResponseService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/inventory")]
class GetController extends AbstractController
{
    #[Route("/my", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getMyHouseInventory(
        ResponseService $responseService, ManagerRegistry $doctrine
    )
    {
        $inventoryRepository = $doctrine->getRepository(Inventory::class, 'readonly');

        $inventory = $inventoryRepository->findBy([
            'owner' => $this->getUser(),
            'location' => LocationEnum::HOME
        ]);

        return $responseService->success($inventory, [ SerializationGroupEnum::MY_INVENTORY ]);
    }

    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/my/{location}", methods: ["GET"], requirements: ["location" => "\d+"])]
    public function getMyInventory(
        Request $request, ResponseService $responseService, InventoryFilterService $inventoryFilterService,
        int $location
    )
    {
        if(!LocationEnum::isAValue($location))
            throw new PSPFormValidationException('Invalid location given.');

        /** @var User $user */
        $user = $this->getUser();

        $inventoryFilterService->addRequiredFilter('user', $user->getId());
        $inventoryFilterService->addRequiredFilter('location', $location);

        $inventoryFilterService->setUser($user);

        $inventory = $inventoryFilterService->getResults($request->query);

        return $responseService->success($inventory, [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MY_INVENTORY ]);
    }

    #[Route("/summary/{location}", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getSummary(
        int $location, ResponseService $responseService, InventoryRepository $inventoryRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $summary = $inventoryRepository->getInventoryQuantities($user, $location);

        return $responseService->success($summary, [ SerializationGroupEnum::MY_INVENTORY ]);
    }
}
