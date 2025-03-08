<?php
declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Serializer\Attribute\Groups;

class FilterResults
{
    #[Groups(['filterResults'])]
    public int $pageSize;

    #[Groups(['filterResults'])]
    public int $pageCount;

    #[Groups(['filterResults'])]
    public int $page;

    #[Groups(['filterResults'])]
    public int $resultCount;

    #[Groups(['filterResults'])]
    public int $unfilteredTotal;

    /**
     * @var array[]
     */
    #[Groups(['filterResults'])]
    public $results;
}