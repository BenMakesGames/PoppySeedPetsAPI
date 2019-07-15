<?php
namespace App\Controller;

use App\Enum\SerializationGroupEnum;
use App\Repository\ItemRepository;
use App\Service\ResponseService;
use App\Service\TravelingMerchantService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/travelingMerchant")
 */
class TravelingMerchantController extends PsyPetsController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getExchanges(TravelingMerchantService $travelingMerchantService, ResponseService $responseService)
    {
        $offers = $travelingMerchantService->getOffers();

        if(count($offers['offers']) === 0)
            throw new NotFoundHttpException();

        return $responseService->success($offers, [ SerializationGroupEnum::MARKET_ITEM ]);
    }

    /**
     * @Route("/{id}/exchange", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function makeExchange(
        int $id, TravelingMerchantService $travelingMerchantService, ResponseService $responseService
    )
    {
        $offers = $travelingMerchantService->getOffers();
        $exchange = null;

        foreach($offers['offers'] as $offer)
        {
            if($offer['id'] === $id)
            {
                $exchange = $offer;
                break;
            }
        }

        if(!$exchange)
            throw new NotFoundHttpException('There is no such exchange available.');

        $user = $this->getUser();

        // TODO: finish the traveling merchant
    }
}