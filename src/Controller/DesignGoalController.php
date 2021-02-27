<?php
namespace App\Controller;

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
     */
    public function getAll(DesignGoalRepository $designGoalRepository, ResponseService $responseService)
    {
        return $responseService->success(
            $designGoalRepository->findAll(),
            [ SerializationGroupEnum::DESIGN_GOAL ]
        );
    }
}