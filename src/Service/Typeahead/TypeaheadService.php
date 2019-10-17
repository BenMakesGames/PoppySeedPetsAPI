<?php
namespace App\Service\Typeahead;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;

abstract class TypeaheadService
{
    private $repository;

    public function __construct(ServiceEntityRepository $repository)
    {
        $this->repository = $repository;
    }

    abstract public function addQueryBuilderConditions(QueryBuilder $qb): QueryBuilder;

    public function search(string $fieldToSearch, string $searchString, int $maxResults = 5)
    {
        $search = trim($searchString);

        if($search === '')
            throw new \InvalidArgumentException('trim($searchString) must contain at least one character.');

        $qb = $this->repository->createQueryBuilder('e')
            ->andWhere('e.' . $fieldToSearch . ' LIKE :searchLike')
            ->setParameter('searchLike', $search . '%')
            ->setMaxResults($maxResults)
            ->orderBy('e.' . $fieldToSearch, 'ASC')
        ;

        $qb = $this->addQueryBuilderConditions($qb);

        $entities = $qb->getQuery()->execute();

        if(count($entities) < $maxResults)
        {
            $ids = array_map(function($e) { return $e->getId(); }, $entities);

            $qb = $this->repository->createQueryBuilder('e')
                ->andWhere('e.' . $fieldToSearch . ' LIKE :searchLike')
                ->setParameter('searchLike', '%' . $search . '%')
                ->orderBy('e.' . $fieldToSearch, 'ASC')
            ;

            if(count($entities) > 0)
            {
                $qb
                    ->andWhere('e.id NOT IN (:ids)')
                    ->setParameter('ids', $ids)
                    ->setMaxResults($maxResults - count($entities))
                ;
            }
            else
            {
                $qb->setMaxResults($maxResults);
            }

            $qb = $this->addQueryBuilderConditions($qb);

            $entities = array_merge($entities, $qb->getQuery()->execute());
        }

        return $entities;
    }
}