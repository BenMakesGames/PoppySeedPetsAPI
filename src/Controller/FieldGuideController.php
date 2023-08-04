<?php
namespace App\Controller;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Repository\FieldGuideEntryRepository;
use App\Service\Filter\UserFieldGuideEntryFilterService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/fieldGuide")
 */
class FieldGuideController extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function userDonatedItems(
        Request $request, ResponseService $responseService, UserFieldGuideEntryFilterService $userFieldGuideEntryFilterService,
        FieldGuideEntryRepository $fieldGuideEntryRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::FieldGuide))
            throw new PSPNotUnlockedException('Field Guide');

        $userFieldGuideEntryFilterService->addRequiredFilter('user', $user->getId());

        return $responseService->success(
            [
                'totalEntries' => $fieldGuideEntryRepository->count([]),
                'entries' => $userFieldGuideEntryFilterService->getResults($request->query),
            ],
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MY_FIELD_GUIDE ]
        );
    }

}