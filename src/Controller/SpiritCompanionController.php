<?php
namespace App\Controller;

use App\Entity\SpiritCompanion;
use App\Enum\SerializationGroupEnum;
use App\Service\Filter\SpiritCompanionFilterService;
use App\Service\ResponseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/spiritCompanion")
 */
class SpiritCompanionController extends PoppySeedPetsController
{
    /**
     * @Route("/{spiritCompanion}", methods={"GET"}, requirements={"spiritCompanion"="\d+"})
     */
    public function find(SpiritCompanion $spiritCompanion, ResponseService $responseService)
    {
        return $responseService->success($spiritCompanion, SerializationGroupEnum::SPIRIT_COMPANION_PUBLIC_PROFILE);
    }

    /**
     * @Route("/search", methods={"GET"})
     */
    public function search(
        Request $request, ResponseService $responseService, SpiritCompanionFilterService $spiritCompanionFilterService
    )
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
