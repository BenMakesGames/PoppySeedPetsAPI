<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\PetGroup;
use App\Enum\SerializationGroupEnum;
use App\Service\Filter\PetGroupFilterService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/petGroup")]
class PetGroupController extends AbstractController
{
    #[Route("", methods: ["GET"])]
    public function getAllGroups(
        ResponseService $responseService, PetGroupFilterService $petGroupFilterService, Request $request
    )
    {
        return $responseService->success(
            $petGroupFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::PET_GROUP_INDEX ]
        );
    }

    #[Route("/{group}", methods: ["GET"])]
    public function getGroup(PetGroup $group, ResponseService $responseService)
    {
        return $responseService->success($group, [ SerializationGroupEnum::PET_GROUP_DETAILS ]);
    }
}
