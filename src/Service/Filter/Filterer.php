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

    public function addDefaultFilter(string $key, $value)
    {
        $this->defaultFilters[$key] = $value;
    }

    public function addRequiredFilter(string $key, $value)
    {
        $this->requiredFilters[$key] = $value;
    }

    public function filter(QueryBuilder $qb, ParameterBag $params): FilterResults
    {
        // sanitize parameters:

        $page = $params->getInt('page', 0);
        $orderBy = strtolower($params->getAlnum('orderBy'));
        $orderDir = strtolower($params->getAlpha('orderDir'));

        $filters = $params->get('filter', []);
        if(!is_array($filters))
            throw new UnprocessableEntityHttpException('filter must be an array.');

        $filters = array_merge($this->defaultFilters, $filters, $this->requiredFilters);

        if(!array_key_exists($orderBy, $this->orderByMap))
            $orderBy = array_key_first($this->orderByMap);

        if($orderDir !== 'asc' && $orderDir !== 'desc') $orderDir = $this->orderByMap[$orderBy][1];

        $filters = array_filter($filters, function($filter) { return array_key_exists($filter, $this->filterMap); }, ARRAY_FILTER_USE_KEY);

        // assemble query:

        $qb->orderBy($this->orderByMap[$orderBy][0], $orderDir);

        foreach($filters as $filter=>$value)
            $this->filterMap[$filter]($qb, $value);

        $paginator = new Paginator($qb->getQuery());

        $numResults = count($paginator);
        $lastPage = ceil($numResults / $this->pageSize);

        if($page < 0)
            $page = 0;
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
        $results->results = $paginator->getQuery()->execute();

        $results->query = [
            'sql' => $paginator->getQuery()->getSQL(),
            'parameters' => $paginator->getQuery()->getParameters()->toArray()
        ];

        return $results;
    }
}