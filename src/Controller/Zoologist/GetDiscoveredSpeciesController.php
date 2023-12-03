<?php
namespace App\Controller\Zoologist;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\Filter\PetSpeciesFilterService;
use App\Service\Filter\UserSpeciesCollectedFilterService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route("/zoologist")]
class GetDiscoveredSpeciesController extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getDiscoveredSpecies(
        UserSpeciesCollectedFilterService $userSpeciesCollectedFilterService, Request $request, ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Zoologist))
            throw new PSPNotUnlockedException('Zoologist');

        $userSpeciesCollectedFilterService->addRequiredFilter('user', $user->getId());

        return $responseService->success(
            $userSpeciesCollectedFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::ZOOLOGIST_CATALOG ]
        );
    }
}