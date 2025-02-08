<?php
declare(strict_types=1);

namespace App\Controller\Greenhouse;

use App\Entity\GreenhousePlant;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\GrammarFunctions;
use App\Functions\PlayerLogFactory;
use App\Repository\InventoryRepository;
use App\Service\GreenhouseService;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/greenhouse")]
class FertilizePlantController extends AbstractController
{
    #[Route("/{plant}/fertilize", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function fertilizePlant(
        GreenhousePlant $plant, ResponseService $responseService, Request $request, EntityManagerInterface $em,
        InventoryRepository $inventoryRepository, UserStatsService $userStatsRepository,
        GreenhouseService $greenhouseService
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if($plant->getOwner()->getId() !== $user->getId())
            throw new PSPNotFoundException('That plant does not exist.');

        if(new \DateTimeImmutable() < $plant->getCanNextInteract())
            throw new PSPInvalidOperationException('This plant is not yet ready to fertilize.');

        $fertilizerId = $request->request->getInt('fertilizer', 0);

        $fertilizer = $inventoryRepository->findOneBy([
            'id' => $fertilizerId,
            'owner' => $user->getId(),
            'location' => Inventory::CONSUMABLE_LOCATIONS,
        ]);

        if(!$fertilizer || $fertilizer->getTotalFertilizerValue() <= 0)
            throw new PSPFormValidationException('A fertilizer must be selected.');

        $plant->fertilize($fertilizer);

        $userStatsRepository->incrementStat($user, UserStatEnum::FERTILIZED_PLANT);

        $plantNameArticle = GrammarFunctions::indefiniteArticle($plant->getPlant()->getName());

        PlayerLogFactory::create(
            $em,
            $user,
            "You fertilized $plantNameArticle {$plant->getPlant()->getName()} plant with {$fertilizer->getFullItemName()}.",
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
