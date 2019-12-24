<?php
namespace App\Model;

use Symfony\Component\Serializer\Annotation\Groups;

class StoryStepChoice
{
    /**
     * @var string
     * @Groups({"story"})
     */
    public $text;

    /**
     * @var bool
     * @Groups({"story"})
     */
    public $enabled;

    /**
     * @var bool
     * @Groups({"story"})
     */
    public $exitOnSelect;
}