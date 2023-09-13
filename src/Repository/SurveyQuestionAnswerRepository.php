<?php

namespace App\Repository;

use App\Entity\SurveyQuestionAnswer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SurveyQuestionAnswer|null find($id, $lockMode = null, $lockVersion = null)
 * @method SurveyQuestionAnswer|null findOneBy(array $criteria, array $orderBy = null)
 * @method SurveyQuestionAnswer[]    findAll()
 * @method SurveyQuestionAnswer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class SurveyQuestionAnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurveyQuestionAnswer::class);
    }
}
