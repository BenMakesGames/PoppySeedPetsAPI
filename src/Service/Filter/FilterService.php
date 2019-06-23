<?php
namespace App\Service\Filter;

use App\Model\FilterResults;
use Symfony\Component\HttpFoundation\Request;

trait FilterService
{
    /**
     * @var Filterer
     */
    private $filterer;

    public function getResults(Request $request): FilterResults
    {
        return $this->filterer->filter($request->query);
    }
}