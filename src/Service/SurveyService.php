<?php

namespace App\Service;

use App\Entity\Survey;
use App\Entity\SurveyQuestion;
use App\Entity\SurveyQuestionAnswer;
use App\Entity\User;
use App\Repository\SurveyQuestionAnswerRepository;
use App\Repository\SurveyRepository;
use Doctrine\ORM\EntityManagerInterface;

class SurveyService
{
    private SurveyRepository $surveyRepository;
    private SurveyQuestionAnswerRepository $surveyQuestionAnswerRepository;
    private EntityManagerInterface $em;

    public function __construct(
        SurveyRepository $surveyRepository, SurveyQuestionAnswerRepository $surveyQuestionAnswerRepository,
        EntityManagerInterface $em
    )
    {
        $this->surveyRepository = $surveyRepository;
        $this->surveyQuestionAnswerRepository = $surveyQuestionAnswerRepository;
        $this->em = $em;
    }

    public function getActiveSurvey(string $guid, \DateTimeImmutable $dateTime): ?Survey
    {
        $qb = $this->surveyRepository->createQueryBuilder('s');

        return $qb
            ->andWhere('s.guid=:guid')
            ->andWhere('s.startDate <= :dateTime')
            ->andWhere('s.endDate >= :dateTime')
            ->setParameter('guid', $guid)
            ->setParameter('dateTime', $dateTime)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getSurveyQuestions(string $guid, \DateTimeImmutable $dateTime): ?array
    {
        $survey = $this->getActiveSurvey($guid, $dateTime);

        if(!$survey)
            return null;

        return $survey->getQuestions()->toArray();
    }

    public function getSurveyQuestionAnswers(string $guid, User $user): ?array
    {
        $survey = $this->getActiveSurvey($guid, new \DateTimeImmutable());

        if(!$survey)
            return null;

        $qb = $this->surveyQuestionAnswerRepository->createQueryBuilder('a');

        return $qb
            ->join('a.question', 'q')
            ->andWhere('a.user=:user')
            ->andWhere('q.survey=:survey')
            ->setParameter('user', $user->getId())
            ->setParameter('survey', $survey->getId())
            ->orderBy('q.id', 'ASC')
            ->getQuery()
            ->execute()
        ;
    }

    public function upsertAnswer(SurveyQuestion $question, User $user, string $answerText): SurveyQuestionAnswer
    {
        $answer = $this->surveyQuestionAnswerRepository->findOneBy([
            'user' => $user,
            'question' => $question
        ]);

        if($answer == null)
        {
            $answer = new SurveyQuestionAnswer();
            $answer->setUser($user);
            $answer->setQuestion($question);

            $this->em->persist($answer);
        }

        $answer->setAnswer($answerText);

        return $answer;
    }

    public function deleteAnswer(SurveyQuestion $question, User $user)
    {
        $answer = $this->surveyQuestionAnswerRepository->findOneBy([
            'user' => $user,
            'question' => $question
        ]);

        if($answer)
            $this->em->remove($answer);
    }
}