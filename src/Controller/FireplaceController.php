<?php
namespace App\Controller;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Repository\InventoryRepository;
use App\Service\ResponseService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class FireplaceController extends PoppySeedPetsController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getFireplace()
    {
        $user = $this->getUser();
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