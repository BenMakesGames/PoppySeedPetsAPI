<?php
namespace App\Controller;

use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\TraderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/trader")
 */
class TraderController extends PoppySeedPetsController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getExchanges(TraderService $traderService, ResponseService $responseService)
    {
        $user = $this->getUser();

        if(!$user->getUnlockedTrader())
            throw new AccessDeniedHttpException('You haven\'t unlocked the Trader yet.');

        $offers = $traderService->getOffers($user);

        return $responseService->success($offers, [ SerializationGroupEnum::TRADER_OFFER, SerializationGroupEnum::MARKET_ITEM ]);
    }

    /**
     * @Route("/{id}/exchange", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function makeExchange(
        string $id, TraderService $traderService, ResponseService $responseService, EntityManagerInterface $em,
        UserQuestRepository $userQuestRepository, InventoryService $inventoryService
    )
    {
        $user = $this->getUser();

        if(!$user->getUnlockedTrader())
            throw new AccessDeniedHttpException('You haven\'t unlocked the Trader yet.');

        $exchange = $traderService->getOfferById($user, $id);

        if(!$exchange)
            throw new NotFoundHttpException('There is no such exchange available.');

        if(!$traderService->userCanMakeExchange($user, $exchange))
            throw new UnprocessableEntityHttpException('You don\'t have the items needed to make this exchange.');

        $traderService->makeExchange($user, $exchange);

        $message = null;

        // october
        if((int)(new \DateTimeImmutable())->format('n') === 10)
        {
            $quest = $userQuestRepository->findOrCreate($user, 'Get October Behatting Scroll', false);
            if($quest->getValue() === false)
            {
                $quest->setValue(true);
                $inventoryService->receiveItem('Behatting Scroll', $user, null, 'The Trader gave you this, for Halloween.', LocationEnum::HOME, true);

                $message = 'Oh, and here, have a Behatting Scroll. It\'ll come in handy for Halloween, trust me!';
            }
        }

        $em->flush();

        return $responseService->success([ 'message' => $message ]);
    }
}
