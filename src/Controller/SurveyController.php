<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPNotFoundException;
use App\Service\ResponseService;
use App\Service\SurveyService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route("/survey")]
class SurveyController extends AbstractController
{
    #[Route("/{guid}", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getSurveyQuestions(
        string $guid, SurveyService $surveyService, ResponseService $responseService, NormalizerInterface $normalizer
    )
    {
        $now = new \DateTimeImmutable();

        $survey = $surveyService->getActiveSurvey($guid, $now);
        $questions = $surveyService->getSurveyQuestions($guid, $now);

        if(!$questions)
            throw new PSPNotFoundException('Survey not found.');

        /** @var User $user */
        $user = $this->getUser();

        $answers = $surveyService->getSurveyQuestionAnswers($guid, $user);

        return $responseService->success([
            'survey' => $normalizer->normalize($survey, null, [ 'groups' => [ SerializationGroupEnum::SURVEY_SUMMARY ] ]),
            'questions' => $normalizer->normalize($questions, null, [ 'groups' => [ SerializationGroupEnum::SURVEY_QUESTION ] ]),
            'answers' => $normalizer->normalize($answers, null, [ 'groups' => [ SerializationGroupEnum::SURVEY_QUESTION_ANSWER ] ])
        ]);
    }

    #[Route("/{guid}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function submitSurvey(
        string $guid, SurveyService $surveyService, Request $request, ResponseService $responseService,
        EntityManagerInterface $em
    )
    {
        $now = new \DateTimeImmutable();

        $questions = $surveyService->getSurveyQuestions($guid, $now);

        if(!$questions)
            throw new PSPNotFoundException('Survey not found.');

        /** @var User $user */
        $user = $this->getUser();

        foreach($questions as $question)
        {
            $answer = trim($request->request->get($question->getId()));

            if($answer)
                $surveyService->upsertAnswer($question, $user, $answer);
            else
                $surveyService->deleteAnswer($question, $user);
        }

        $em->flush();

        return $responseService->success();
    }
}