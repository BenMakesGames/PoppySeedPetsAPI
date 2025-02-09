<?php
declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Serializer\Attribute\Groups;

class UserMenuItem
{
    #[Groups(['myMenu'])]
    public string $location;

    #[Groups(['myMenu'])]
    public bool $isNew;

    #[Groups(['myMenu'])]
    public int $sortOrder;

    public function __construct(string $location, int|bool $sortOrder, ?\DateTimeImmutable $unlockDate)
    {
        $this->location = $location;
        $this->sortOrder = $sortOrder === false ? 0 : $sortOrder;
        $this->isNew = $unlockDate >= (new \DateTimeImmutable())->modify('-4 hours');
    }
}