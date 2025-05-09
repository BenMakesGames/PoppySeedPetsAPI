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

use App\Model\FilterResults;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\ParameterBag;

trait FilterService
{
    /**
     * @var Filterer
     */
    private Filterer $filterer;

    public function getResults(ParameterBag $parameters): FilterResults
    {
        return $this->filterer->filter($this, $parameters);
    }

    public function addDefaultFilter(string $key, mixed $value): void
    {
        $this->filterer->addDefaultFilter($key, $value);
    }

    public function addRequiredFilter(string $key, mixed $value): void
    {
        $this->filterer->addRequiredFilter($key, $value);
    }

    abstract function createQueryBuilder(): QueryBuilder;

    abstract function applyResultCache(Query $qb, string $cacheKey): AbstractQuery;

    abstract public function allowedPageSizes(): array;
}