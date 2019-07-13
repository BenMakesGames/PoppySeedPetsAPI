<?php
namespace App\Service\Filter;

use App\Model\FilterResults;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\ParameterBag;

trait FilterService
{
    /**
     * @var Filterer
     */
    private $filterer;

    public function getResults(ParameterBag $parameters): FilterResults
    {
        return $this->filterer->filter($this->createQueryBuilder(), $parameters);
    }

    public function addDefaultFilter(string $key, $value)
    {
        $this->filterer->addDefaultFilter($key, $value);
    }

    public function addRequiredFilter(string $key, $value)
    {
        $this->filterer->addRequiredFilter($key, $value);
    }

    abstract function createQueryBuilder(): QueryBuilder;
}