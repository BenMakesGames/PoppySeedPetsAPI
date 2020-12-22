<?php
namespace App\Controller;

use App\Entity\UserLetter;
use App\Enum\SerializationGroupEnum;
use App\Repository\UserLetterRepository;
use App\Service\Filter\UserLetterFilterService;
use App\Service\ResponseService;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/letter")
 */
class LetterController extends PoppySeedPetsController
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
    public function markRead(UserLetter $letter, EntityManagerInterface $em, ResponseService $responseService)
    {
        if($letter->getUser()->getId() !== $this->getUser()->getId())
            throw new NotFoundHttpException();

        $letter->setIsRead();

        $em->flush();

        return $responseService->success();
    }
}
