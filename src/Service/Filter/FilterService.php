<?php
namespace App\Service\Filter;

use App\Model\FilterResults;
use Symfony\Component\HttpFoundation\ParameterBag;

trait FilterService
{
    /**
     * @var Filterer
     */
    private $filterer;

    public function getResults(ParameterBag $parameters): FilterResults
    {
        return $this->filterer->filter($parameters);
    }

    public function addFilter(string $key, $value)
    {
        $this->filterer->addFilter($key, $value);
    }
}