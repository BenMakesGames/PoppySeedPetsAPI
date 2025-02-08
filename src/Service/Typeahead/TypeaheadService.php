<?php
declare(strict_types=1);

namespace App\Service\Typeahead;

use App\Exceptions\PSPFormValidationException;
use App\Functions\StringFunctions;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

abstract class TypeaheadService
{
    public function __construct(
        private readonly EntityRepository $repository
    )
    {
    }

    abstract public function addQueryBuilderConditions(QueryBuilder $qb): QueryBuilder;

    public function search(string $fieldToSearch, string $searchString, int $maxResults = 5): array
    {
        $search = trim($searchString);

        if($search === '')
            throw new PSPFormValidationException('Search text is missing...');

        $qb = $this->repository->createQueryBuilder('e')
            ->andWhere('e.' . $fieldToSearch . ' LIKE :searchLike')
            ->setParameter('searchLike', StringFunctions::escapeMySqlWildcardCharacters($search) . '%')
            ->setMaxResults($maxResults)
            ->orderBy('e.' . $fieldToSearch, 'ASC')
        ;

        $qb = $this->addQueryBuilderConditions($qb);

        $entities = $qb->getQuery()->execute();

        if(count($entities) < $maxResults)
        {
            $ids = array_map(fn($e) => $e->getId(), $entities);

            $qb = $this->repository->createQueryBuilder('e')
                ->andWhere('e.' . $fieldToSearch . ' LIKE :searchLike')
                ->setParameter('searchLike', '%' . StringFunctions::escapeMySqlWildcardCharacters($search) . '%')
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
