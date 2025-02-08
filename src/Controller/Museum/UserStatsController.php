<?php
declare(strict_types=1);

namespace App\Controller\Museum;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\Filter\ItemFilterService;
use App\Service\Filter\MuseumFilterService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/museum")]
class UserStatsController extends AbstractController
{
    /**
     * @Route("/{user}/items", methods={"GET"}, requirements={"user"="\d+"})
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function userDonatedItems(
        User $user,
        Request $request, ResponseService $responseService, MuseumFilterService $museumFilterService
    )
    {
        if(!$this->getUser()->hasUnlockedFeature(UnlockableFeatureEnum::Museum))
            throw new PSPNotUnlockedException('Museum');

        $museumFilterService->addRequiredFilter('user', $user->getId());

        return $responseService->success(
            $museumFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MUSEUM ]
        );
    }

    /**
     * @Route("/{user}/nonItems", methods={"GET"}, requirements={"user"="\d+"})
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function userNonDonatedItems(
        User $user,
        Request $request, ResponseService $responseService, ItemFilterService $itemFilterService
    )
    {
        if(!$this->getUser()->hasUnlockedFeature(UnlockableFeatureEnum::Museum))
            throw new PSPNotUnlockedException('Museum');

        $itemFilterService->addRequiredFilter('notDonatedBy', $user->getId());

        return $responseService->success(
            $itemFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::ITEM_ENCYCLOPEDIA ]
        );
    }

    /**
     * @Route("/{user}/itemCount", methods={"GET"}, requirements={"user"="\d+"})
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function userItemCount(
        User $user,
        Request $request, ResponseService $responseService, MuseumFilterService $museumFilterService
    )
    {
        if(!$this->getUser()->hasUnlockedFeature(UnlockableFeatureEnum::Museum))
            throw new PSPNotUnlockedException('Museum');

        $museumFilterService->addRequiredFilter('user', $user->getId());

        return $responseService->success(
            $museumFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MUSEUM ]
        );
    }
}
