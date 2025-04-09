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
    private int $pageSize;
    private array $defaultFilters = [];
    private array $requiredFilters = [];

    public function __construct(int $defaultPageSize, array $orderByMap, array $filterCallbacks, array $filtersWithoutCallbacks = [])
    {
        $this->pageSize = $defaultPageSize;
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

        $pageSize = $params->getInt('pageSize', $this->pageSize);

        if(in_array($pageSize, $filterService->allowedPageSizes()))
            $this->setPageSize($pageSize);

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
