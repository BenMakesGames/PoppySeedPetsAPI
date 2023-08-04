<?php
namespace App\Controller;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Repository\TraderRepository;
use App\Repository\UserQuestRepository;
use App\Service\FieldGuideService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\TraderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/trader")
 */
class TraderController extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getExchanges(
        TraderService $traderService, ResponseService $responseService, TraderRepository $traderRepository,
        EntityManagerInterface $em, Squirrel3 $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Trader))
            throw new PSPNotUnlockedException('Trader');

        $offers = $traderService->getOffers($user);

        $trader = $traderRepository->findOneBy([ 'user' => $user->getId() ]);

        if(!$trader)
        {
            $trader = TraderService::generateTrader($rng)
                ->setUser($user)
            ;

            $em->persist($trader);
            $em->flush();
        }

        $data = [
            'trades' => $offers,
            'trader' => $trader,
        ];

        return $responseService->success($data, [ SerializationGroupEnum::TRADER_OFFER, SerializationGroupEnum::MARKET_ITEM ]);
    }

    /**
     * @Route("/{id}/exchange", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function makeExchange(
        string $id, TraderService $traderService, ResponseService $responseService, EntityManagerInterface $em,
        UserQuestRepository $userQuestRepository, InventoryService $inventoryService, Request $request,
        FieldGuideService $fieldGuideService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Trader))
            throw new PSPNotUnlockedException('Trader');

        $quantity = $request->request->getInt('quantity', 1);

        if($quantity < 1)
            throw new PSPFormValidationException('Quantity must be 1, or more.');

        $exchange = $traderService->getOfferById($user, $id);

        if($quantity > $exchange->canMakeExchange)
            throw new PSPInvalidOperationException('You only have the stuff to do this exchange up to ' . $exchange->canMakeExchange . ' times.');

        if(!$exchange)
            throw new PSPNotFoundException('There is no such exchange available.');

        if(!$traderService->userCanMakeExchange($user, $exchange))
            throw new PSPNotFoundException('The items you need to make this exchange could not be found in your house.');

        $traderService->makeExchange($user, $exchange, $quantity);

        $message = null;

        $now = new \DateTimeImmutable();

        // october
        if((int)$now->format('n') === 10)
        {
            $quest = $userQuestRepository->findOrCreate($user, 'Get October ' . $now->format('Y') . ' Behatting Scroll', false);
            if($quest->getValue() === false)
            {
                $quest->setValue(true);
                $inventoryService->receiveItem('Behatting Scroll', $user, null, 'The Trader gave you this, for Halloween.', LocationEnum::HOME, true);

                $message = 'Oh, and here, have a Behatting Scroll. It\'ll come in handy for Halloween, trust me!';
            }
        }

        $fieldGuideService->maybeUnlock($user, 'Tell Samarzhoustia', '%user:' . $user->getId() . '.Name% made an exchange with a trader from Tell Samarzhoustia.');

        $em->flush();

        $offers = $traderService->getOffers($user);

        return $responseService->success(
            [
                'message' => $message,
                'trades' => $offers
            ],
            [ SerializationGroupEnum::TRADER_OFFER, SerializationGroupEnum::MARKET_ITEM ]
        );
    }
}
