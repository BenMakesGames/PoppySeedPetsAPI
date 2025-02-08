<?php
declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Serializer\Attribute\Groups;

class StoryStepChoice
{
    #[Groups(['story'])]
    public string $text;

    #[Groups(['story'])]
    public bool $enabled;

    #[Groups(['story'])]
    public bool $exitOnSelect;
}