<?php
declare(strict_types=1);

namespace App\Model;
use Symfony\Component\Serializer\Annotation\Groups;

class FilterResults
{
    /**
     * @var int
     * @Groups({"filterResults"})
     */
    public $pageSize;

    /**
     * @var int
     * @Groups({"filterResults"})
     */
    public $pageCount;

    /**
     * @var int
     * @Groups({"filterResults"})
     */
    public $page;

    /**
     * @var int
     * @Groups({"filterResults"})
     */
    public $resultCount;

    /**
     * @var int
     * @Groups({"filterResults"})
     */
    public $unfilteredTotal;

    /**
     * @var array[]
     * @Groups({"filterResults"})
     */
    public $results;

    /**
     * @var array[]|null
     * @Groups({"queryAdmin"})
     */
    public $query;
}