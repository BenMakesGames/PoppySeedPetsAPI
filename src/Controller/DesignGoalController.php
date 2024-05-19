<?php
namespace App\Controller;

use App\Entity\DesignGoal;
use App\Enum\SerializationGroupEnum;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/designGoal")]
class DesignGoalController extends AbstractController
{
    #[Route("", methods: ["GET"])]
    /**
     * @DoesNotRequireHouseHours()
     */
    public function getAll(EntityManagerInterface $em, ResponseService $responseService)
    {
        return $responseService->success(
            $em->getRepository(DesignGoal::class)->findAll(),
            [ SerializationGroupEnum::DESIGN_GOAL ]
        );
    }

    #[Route("/{designGoal}", methods: ["GET"])]
    /**
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