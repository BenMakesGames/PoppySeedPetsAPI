<?php
namespace App\Model;

use Symfony\Component\Serializer\Annotation\Groups;

class UserMenuItem
{
    /**
     * @var string
     * @Groups({"myMenu"})
     */
    public $location;

    /**
     * @var bool
     * @Groups({"myMenu"})
     */
    public $isNew;

    /**
     * @var int
     * @Groups({"myMenu"})
     */
    public $sortOrder;

    public function __construct(string $location, array $sortOrder, ?\DateTimeImmutable $unlockDate)
    {
        $this->location = $location;
        $this->sortOrder = array_search($location, $sortOrder);
        $this->isNew = $unlockDate >= (new \DateTimeImmutable())->modify('-4 hours');
    }
}