<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserLetter;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPNotFoundException;
use App\Service\FieldGuideService;
use App\Service\Filter\UserLetterFilterService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/letter")]
class LetterController extends AbstractController
{
    #[Route("", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getLetters(
        Request $request, ResponseService $responseService,
        UserLetterFilterService $userLetterFilterService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $userLetterFilterService->addRequiredFilter('user', $user->getId());

        $results = $userLetterFilterService->getResults($request->request);

        return $responseService->success($results, [
            SerializationGroupEnum::FILTER_RESULTS,
            SerializationGroupEnum::MY_LETTERS
        ]);
    }

    #[Route("/{letter}/read", methods: ["PATCH"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function markRead(
        UserLetter $letter, EntityManagerInterface $em, ResponseService $responseService,
        FieldGuideService $fieldGuideService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($letter->getUser()->getId() !== $user->getId())
            throw new PSPNotFoundException('That letter does not exist??!?');

        $letter->setIsRead();

        if($letter->getLetter()->getFieldGuideEntry())
        {
            $fieldGuideService->maybeUnlock(
                $user,
                $letter->getLetter()->getFieldGuideEntry(),
                '%user:' . $user->getId() . '.Name% read a letter from ' . $letter->getLetter()->getSender() . '.'
            );
        }

        $em->flush();

        return $responseService->success();
    }
}
