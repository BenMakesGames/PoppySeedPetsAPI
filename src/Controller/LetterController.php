<?php
namespace App\Controller;

use App\Entity\UserLetter;
use App\Enum\SerializationGroupEnum;
use App\Service\FieldGuideService;
use App\Service\Filter\UserLetterFilterService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/letter")
 */
class LetterController extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getLetters(
        Request $request, ResponseService $responseService,
        UserLetterFilterService $userLetterFilterService
    )
    {
        $user = $this->getUser();

        $userLetterFilterService->addRequiredFilter('user', $user->getId());

        $results = $userLetterFilterService->getResults($request->request);

        return $responseService->success($results, [
            SerializationGroupEnum::FILTER_RESULTS,
            SerializationGroupEnum::MY_LETTERS
        ]);
    }

    /**
     * @Route("/{letter}/read", methods={"PATCH"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function markRead(
        UserLetter $letter, EntityManagerInterface $em, ResponseService $responseService,
        FieldGuideService $fieldGuideService
    )
    {
        $user = $this->getUser();

        if($letter->getUser()->getId() !== $user->getId())
            throw new NotFoundHttpException();

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
