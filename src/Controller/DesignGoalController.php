<?php
declare(strict_types=1);

namespace App\Controller;

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\DesignGoal;
use App\Enum\SerializationGroupEnum;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/designGoal")]
class DesignGoalController extends AbstractController
{
    #[DoesNotRequireHouseHours]
    #[Route("", methods: ["GET"])]
    public function getAll(EntityManagerInterface $em, ResponseService $responseService)
    {
        return $responseService->success(
            $em->getRepository(DesignGoal::class)->findAll(),
            [ SerializationGroupEnum::DESIGN_GOAL ]
        );
    }

    #[DoesNotRequireHouseHours]
    #[Route("/{designGoal}", methods: ["GET"])]
    public function getDetails(DesignGoal $designGoal, ResponseService $responseService)
    {
        return $responseService->success([
            'id' => $designGoal->getId(),
            'name' => $designGoal->getName(),
            'description' => $designGoal->getDescription()
        ]);
    }
}