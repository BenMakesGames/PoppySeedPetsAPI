<?php
namespace App\Controller\Market;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Service\Filter\MarketFilterService;
use App\Service\MarketService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @Route("/market")
 */
class SearchController extends AbstractController
{
    /**
     * @Route("/search", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function search(
        Request $request, ResponseService $responseService, MarketFilterService $marketFilterService,
        NormalizerInterface $normalizer, MarketService $marketService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $marketFilterService->setUser($user);

        $results = $marketFilterService->getResults($request->query);

        $data = $normalizer->normalize($results, null, [ 'groups' => [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MARKET_ITEM ] ]);

        foreach($data['results'] as &$result)
        {
            $result['minSellPrice'] = $marketService->getLowestPriceForItem(
                $result['inventory']['item']['id'],
                $result['inventory']['enchantment'] ? $result['inventory']['enchantment']['id'] : null,
                $result['inventory']['spice'] ? $result['inventory']['spice']['id'] : null
            );
        }

        return $responseService->success(
            $data,
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MARKET_ITEM ]
        );
    }
}
