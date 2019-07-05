<?php
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
     * @var array[]
     * @Groups({"filterResults"})
     */
    public $results;

    /**
     * @var array[]
     * @Groups({"queryAdmin"})
     */
    public $query;
}