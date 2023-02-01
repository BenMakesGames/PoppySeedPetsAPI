<?php
namespace App\Controller\Greenhouse;

use App\Entity\GreenhousePlant;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Functions\PlayerLogHelpers;
use App\Repository\InventoryRepository;
use App\Repository\UserStatsRepository;
use App\Service\GreenhouseService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/greenhouse")
 */
class FertilizePlantController extends AbstractController
{
    /**
     * @Route("/{plant}/fertilize", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function fertilizePlant(
        GreenhousePlant $plant, ResponseService $responseService, Request $request, EntityManagerInterface $em,
        InventoryRepository $inventoryRepository, UserStatsRepository $userStatsRepository,
        GreenhouseService $greenhouseService
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if($plant->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('That plant does not exist.');

        if(new \DateTimeImmutable() < $plant->getCanNextInteract())
            throw new UnprocessableEntityHttpException('This plant is not yet ready to fertilize.');

        $fertilizerId = $request->request->getInt('fertilizer', 0);

        $fertilizer = $inventoryRepository->findOneBy([
            'id' => $fertilizerId,
            'owner' => $user->getId(),
            'location' => Inventory::CONSUMABLE_LOCATIONS,
        ]);

        if(!$fertilizer || $fertilizer->getItem()->getFertilizer() === 0)
            throw new UnprocessableEntityHttpException('A fertilizer must be selected.');

        $plant->increaseGrowth($fertilizer->getItem()->getFertilizer());

        $userStatsRepository->incrementStat($user, UserStatEnum::FERTILIZED_PLANT);

        PlayerLogHelpers::Create(
            $em,
            $user,
            "You fertilized a {$plant->getPlant()->getName()} plant with {$fertilizer->getFullItemName()}.",
            [ 'Greenhouse' ]
        );

        $em->remove($fertilizer);
        $em->flush();

        return $responseService->success(
            $greenhouseService->getGreenhouseResponseData($user),
            [ SerializationGroupEnum::GREENHOUSE_PLANT, SerializationGroupEnum::MY_GREENHOUSE, SerializationGroupEnum::HELPER_PET ]
        );
    }
}
