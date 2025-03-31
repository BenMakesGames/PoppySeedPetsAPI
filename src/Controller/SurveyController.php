<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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