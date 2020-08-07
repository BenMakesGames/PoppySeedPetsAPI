<?php
namespace App\Service\Filter;

use App\Model\FilterResults;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class Filterer
{
    private $orderByMap;
    private $filterMap;
    private $pageSize;
    private $defaultFilters = [];
    private $requiredFilters = [];

    public function __construct(int $pageSize, array $orderByMap, array $filterCallbacks)
    {
        $this->pageSize = $pageSize;
        $this->orderByMap = $orderByMap;
        $this->filterMap = $filterCallbacks;
    }

    public function setPageSize(int $pageSize)
    {
        $this->pageSize = $pageSize;
    }

    public function addDefaultFilter(string $key, $value)
    {
        $this->defaultFilters[$key] = $value;
    }

    public function addRequiredFilter(string $key, $value)
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

        $filters = $params->get('filter', []);
        if(!is_array($filters))
            throw new UnprocessableEntityHttpException('filter must be an array.');

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
                    return array_key_exists($filter, $this->filterMap);
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
            $this->filterMap[$filter]($qb, $value);

        $paginator = new Paginator($qb->getQuery());

        $numResults = count($paginator);
        $lastPage = ceil($numResults / $this->pageSize);

        if($page < -$lastPage)
            $page = 0;
        else if($page < 0)
            $page = $lastPage + $page;
        else if($lastPage > 0 && $page >= $lastPage)
            $page = $lastPage - 1;

        $paginator->getQuery()
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
        $results->results = $paginator->getQuery()->execute();

        $parameters = [];

        foreach($paginator->getQuery()->getParameters() as $parameter)
            $parameters[$parameter->getName()] = $parameter->getValue();

        $results->query = [
            'sql' => $paginator->getQuery()->getSQL(),
            'parameters' => $parameters
        ];

        return $results;
    }

    private function getGrandTotal(QueryBuilder $qb): int
    {
        $filters = array_filter($this->requiredFilters, function($filter) { return array_key_exists($filter, $this->filterMap); }, ARRAY_FILTER_USE_KEY);

        foreach($filters as $filter=>$value)
            $this->filterMap[$filter]($qb, $value);

        $paginator = new Paginator($qb->getQuery());

        return $paginator->count();
    }
}
