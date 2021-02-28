<?php
namespace App\Controller;

use App\Annotations\DoesNotRequireHouseHours;
use App\Entity\DesignGoal;
use App\Enum\SerializationGroupEnum;
use App\Repository\DesignGoalRepository;
use App\Service\ResponseService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/designGoal")
 */
class DesignGoalController extends PoppySeedPetsController
{
    /**
     * @Route("", methods={"GET"})
     * @DoesNotRequireHouseHours()
     */
    public function getAll(DesignGoalRepository $designGoalRepository, ResponseService $responseService)
    {
        return $responseService->success(
            $designGoalRepository->findAll(),
            [ SerializationGroupEnum::DESIGN_GOAL ]
        );
    }

    /**
     * @Route("/{designGoal}", methods={"GET"})
     * @DoesNotRequireHouseHours()
     */
    public function getDetails(DesignGoal $designGoal, ResponseService $responseService)
    {
        return $responseService->success([
            'id' => $designGoal->getId(),
            'name' => $designGoal->getName(),
            'description' => $designGoal->getDescription()
        ]);
    }
}