<?php
namespace App\Service\Typeahead;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

        $entities = $this->repository->createQueryBuilder('e')
            ->andWhere('e.' . $fieldToSearch . ' LIKE :searchLike')
            ->setParameter('searchLike', $search . '%')
            ->setMaxResults($maxResults)
            ->orderBy('e.' . $fieldToSearch, 'ASC')
            ->getQuery()
            ->execute()
        ;

        if(count($entities) < $maxResults)
        {
            $ids = array_map(function($e) { return $e->getId(); }, $entities);

            $entities = array_merge($entities, $this->repository->createQueryBuilder('e')
                ->andWhere('e.' . $fieldToSearch . ' LIKE :searchLike')
                ->andWhere('e.id NOT IN (:ids)')
                ->setParameter('searchLike', '%' . $search . '%')
                ->setParameter('ids', $ids)
                ->setMaxResults($maxResults - count($entities))
                ->orderBy('e.' . $fieldToSearch, 'ASC')
                ->getQuery()
                ->execute()
            );
        }

        return array_map(function($e) use ($fieldToSearch) {
            return [
                $fieldToSearch => $e->{'get' . $fieldToSearch }(),
                'id' => $e->getId()
            ];
        }, $entities);
    }
}