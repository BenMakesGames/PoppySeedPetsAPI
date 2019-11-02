<?php
namespace App\Controller;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Repository\InventoryRepository;
use App\Service\ResponseService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/fireplace")
 */
class FireplaceController extends PoppySeedPetsController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getFireplace(
        InventoryRepository $inventoryRepository, ResponseService $responseService
    )
    {
        $user = $this->getUser();

        if(!$user->getUnlockedFireplace())
            throw new AccessDeniedHttpException('You haven\'t got a Fireplace, yet!');

        $mantle = $inventoryRepository->findBy([
            'owner' => $user,
            'location' => LocationEnum::MANTLE
        ]);

        return $responseService->success(
            [
                'mantle' => $mantle,
                'fireplace' => $user->getFireplace(),
            ],
            [
                SerializationGroupEnum::MY_INVENTORY,
                SerializationGroupEnum::MY_FIREPLACE,
            ]
        );
    }

    /**
     * @Route("/mantle/{user}", methods={"GET"}, requirements={"user"="\d+"})
     */
    public function getMantle(User $user, InventoryRepository $inventoryRepository, ResponseService $responseService)
    {
        $inventory = $inventoryRepository->findBy([
            'owner' => $user,
            'location' => LocationEnum::MANTLE
        ]);

        return $responseService->success($inventory, SerializationGroupEnum::FIREPLACE_MANTLE);
    }
}