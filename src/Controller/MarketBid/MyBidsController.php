<?php
namespace App\Controller\MarketBid;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Repository\MarketBidRepository;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/marketBid")]
class MyBidsController extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getMyBids(ResponseService $responseService, MarketBidRepository $marketBidRepository)
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Market))
            throw new PSPNotUnlockedException('Market');

        $myBids = $marketBidRepository->findBy([ 'user' => $user ], [ 'createdOn' => 'DESC' ]);

        return $responseService->success($myBids, [ SerializationGroupEnum::MY_MARKET_BIDS ]);
    }
}
