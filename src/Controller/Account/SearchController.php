<?php
namespace App\Controller\Account;

use App\Enum\SerializationGroupEnum;
use App\Service\Filter\UserFilterService;
use App\Service\ResponseService;
use App\Service\Typeahead\UserTypeaheadService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Annotations\DoesNotRequireHouseHours;

#[Route("/account")]
class SearchController extends AbstractController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("/search", methods={"GET"})
     */
    public function search(Request $request, UserFilterService $userFilterService, ResponseService $responseService)
    {
        return $responseService->success(
            $userFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::USER_PUBLIC_PROFILE ]
        );
    }

    /**
     * @DoesNotRequireHouseHours()
     */
    #[Route("/typeahead", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function typeaheadSearch(
        Request $request, ResponseService $responseService, UserTypeaheadService $userTypeaheadService
    )
    {
        $suggestions = $userTypeaheadService->search('name', $request->query->get('search', ''), 5);

        return $responseService->success($suggestions, [ SerializationGroupEnum::USER_TYPEAHEAD ]);
    }
}
