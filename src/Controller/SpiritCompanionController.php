<?php
declare(strict_types=1);

namespace App\Controller;

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\SpiritCompanion;
use App\Enum\SerializationGroupEnum;
use App\Service\Filter\SpiritCompanionFilterService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/spiritCompanion")]
class SpiritCompanionController extends AbstractController
{
    #[DoesNotRequireHouseHours]
    #[Route("/find/{spiritCompanion}", methods: ["GET"], requirements: ["spiritCompanion" => "\d+"])]
    public function find(SpiritCompanion $spiritCompanion, ResponseService $responseService): JsonResponse
    {
        return $responseService->success($spiritCompanion, [ SerializationGroupEnum::SPIRIT_COMPANION_PUBLIC_PROFILE ]);
    }

    #[DoesNotRequireHouseHours]
    #[Route("/search", methods: ["GET"])]
    public function search(
        Request $request, ResponseService $responseService, SpiritCompanionFilterService $spiritCompanionFilterService
    ): JsonResponse
    {
        $results = $spiritCompanionFilterService->getResults($request->query);

        return $responseService->success(
            $results,
            [
                SerializationGroupEnum::FILTER_RESULTS,
                SerializationGroupEnum::SPIRIT_COMPANION_PUBLIC_PROFILE
            ]
        );
    }
}
