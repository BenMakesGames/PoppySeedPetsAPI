<?php
declare(strict_types=1);

namespace App\Controller\Trader;

use App\Entity\Trader;
use App\Entity\User;
use App\Entity\UserFavoriteTrade;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\TraderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/trader")]
class GetTraderController extends AbstractController
{
    #[Route("", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getExchanges(
        TraderService $traderService, ResponseService $responseService,
        EntityManagerInterface $em, IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Trader))
            throw new PSPNotUnlockedException('Trader');

        $offers = $traderService->getOffers($user);

        $trader = $em->getRepository(Trader::class)->findOneBy([ 'user' => $user->getId() ]);

        if(!$trader)
        {
            $trader = TraderService::generateTrader($rng)
                ->setUser($user)
            ;

            $em->persist($trader);
            $em->flush();
        }

        $favorites = $em->getRepository(UserFavoriteTrade::class)->findBy([ 'user' => $user ]);

        $data = [
            'trades' => $offers,
            'trader' => $trader,
            'favorites' => array_map(fn(UserFavoriteTrade $f) => $f->getTrade(), $favorites)
        ];

        return $responseService->success($data, [ SerializationGroupEnum::TRADER_OFFER, SerializationGroupEnum::MARKET_ITEM ]);
    }
}
