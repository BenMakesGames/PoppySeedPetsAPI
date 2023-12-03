<?php
namespace App\Controller\Market;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Service\Filter\MarketFilterService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/market")]
class SearchController extends AbstractController
{
    #[Route("/search", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function search(Request $request, ResponseService $responseService, MarketFilterService $marketFilterService)
    {
        /** @var User $user */
        $user = $this->getUser();

        $marketFilterService->setUser($user);

        return $responseService->success(
            $marketFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MARKET_ITEM ]
        );
    }
}
