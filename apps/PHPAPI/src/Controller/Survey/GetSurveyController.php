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


namespace App\Controller\Survey;

use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPNotFoundException;
use App\Service\ResponseService;
use App\Service\SurveyService;
use App\Service\UserAccessor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route("/survey")]
class GetSurveyController
{
    #[Route("/{guid}", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getSurveyQuestions(
        string $guid, SurveyService $surveyService, ResponseService $responseService, NormalizerInterface $normalizer,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $now = new \DateTimeImmutable();

        $survey = $surveyService->getActiveSurvey($guid, $now);
        $questions = $surveyService->getSurveyQuestions($guid, $now);

        if(!$questions)
            throw new PSPNotFoundException('Survey not found.');

        $user = $userAccessor->getUserOrThrow();

        $answers = $surveyService->getSurveyQuestionAnswers($guid, $user);

        return $responseService->success([
            'survey' => $normalizer->normalize($survey, null, [ 'groups' => [ SerializationGroupEnum::SURVEY_SUMMARY ] ]),
            'questions' => $normalizer->normalize($questions, null, [ 'groups' => [ SerializationGroupEnum::SURVEY_QUESTION ] ]),
            'answers' => $normalizer->normalize($answers, null, [ 'groups' => [ SerializationGroupEnum::SURVEY_QUESTION_ANSWER ] ])
        ]);
    }
}