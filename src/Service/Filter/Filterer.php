<?php
declare(strict_types=1);

namespace App\Service\Filter;

use App\Exceptions\PSPFormValidationException;
use App\Model\FilterResults;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\ParameterBag;

class Filterer
{
    private $orderByMap;
    private $filterMap;
    private $filterWithoutCallbackMap;
    private $pageSize;
    private $defaultFilters = [];
    private $requiredFilters = [];

    public function __construct(int $pageSize, array $orderByMap, array $filterCallbacks, array $filtersWithoutCallbacks = [])
    {
        $this->pageSize = $pageSize;
        $this->orderByMap = $orderByMap;
        $this->filterMap = $filterCallbacks;
        $this->filterWithoutCallbackMap = $filtersWithoutCallbacks;
    }

    public function setPageSize(int $pageSize): void
    {
        $this->pageSize = $pageSize;
    }

    public function addDefaultFilter(string $key, $value): void
    {
        $this->defaultFilters[$key] = $value;
    }

    public function addRequiredFilter(string $key, $value): void
    {
        $this->requiredFilters[$key] = $value;
    }

    /**
     * @param FilterService $filterService
     */
    public function filter($filterService, ParameterBag $params): FilterResults
    {
        // sanitize parameters:

        $page = $params->getInt('page', 0);
        $orderBy = strtolower($params->getAlnum('orderBy'));
        $orderDir = strtolower($params->getAlpha('orderDir'));

        $filters = $params->all('filter');

        $grandTotal = $this->getGrandTotal($filterService->createQueryBuilder());

        $filters = array_merge($this->defaultFilters, $filters, $this->requiredFilters);

        if(!array_key_exists($orderBy, $this->orderByMap))
            $orderBy = array_key_first($this->orderByMap);

        if($orderDir !== '' && $orderDir !== 'reverse') $orderDir = '';

        $filters = array_filter(
            $filters,
            function($value, $filter) {
                if(is_array($value) && count($value) === 0)
                    return false;
                else if($value == '')
                    return false;
                else
                    return array_key_exists($filter, $this->filterMap) || in_array($filter, $this->filterWithoutCallbackMap);
            },
            ARRAY_FILTER_USE_BOTH
        );

        // assemble query:
        $qb = $filterService->createQueryBuilder();

        foreach($this->orderByMap[$orderBy] as $by=>$dir)
        {
            if($orderDir === 'reverse')
                $dir = $dir === 'asc' ? 'desc' : 'asc';

            $qb->addOrderBy($by, $dir);
        }

        foreach($filters as $filter=>$value)
        {
            if(array_key_exists($filter, $this->filterMap))
                $this->filterMap[$filter]($qb, $value, $filters);
        }

        $paginator = new Paginator($qb->getQuery());

        $numResults = count($paginator);
        $lastPage = (int)ceil($numResults / $this->pageSize);

        if($page < -$lastPage)
            $page = 0;
        else if($page < 0)
            $page = $lastPage + $page;
        else if($lastPage > 0 && $page >= $lastPage)
            $page = $lastPage - 1;

        $resultQuery = $paginator->getQuery()
            ->setFirstResult($page * $this->pageSize)
            ->setMaxResults($this->pageSize)
        ;

        // get results:

        $results = new FilterResults();

        $results->page = $page;
        $results->pageSize = $this->pageSize;
        $results->pageCount = $lastPage;
        $results->resultCount = $numResults;
        $results->unfilteredTotal = $grandTotal;

        $resultQuery = $filterService->applyResultCache($resultQuery, Filterer::cacheKey('Results', $page, $orderBy, $orderDir, $filters));

        $results->results = $resultQuery->execute();

        $parameters = [];

        foreach($paginator->getQuery()->getParameters() as $parameter)
            $parameters[$parameter->getName()] = $parameter->getValue();

        return $results;
    }

    private static function cacheKey(string $prefix, int $page, string $orderBy, string $orderDir, array $filters): string
    {
        return sprintf('%s_%d_%s_%s_%s', $prefix, $page, $orderBy, $orderDir, md5(json_encode($filters)));
    }

    private function getGrandTotal(QueryBuilder $qb): int
    {
        $filters = array_filter($this->requiredFilters, fn($filter) => array_key_exists($filter, $this->filterMap), ARRAY_FILTER_USE_KEY);

        foreach($filters as $filter=>$value)
            $this->filterMap[$filter]($qb, $value);

        $paginator = new Paginator($qb->getQuery());

        return $paginator->count();
    }
}
