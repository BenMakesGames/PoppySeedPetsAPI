<?php
namespace App\Model;

use App\Entity\StorySection;
use Symfony\Component\Serializer\Annotation\Groups;

class StoryStep
{
    /**
     * @var string
     * @Groups({"story"})
     */
    public $style;

    /**
     * @var string
     * @Groups({"story"})
     */
    public $background;

    /**
     * @var string
     * @Groups({"story"})
     */
    public $image;

    /**
     * @var string
     * @Groups({"story"})
     */
    public $content;

    /**
     * @var StoryStepChoice[]
     * @Groups({"story"})
     */
    public $choices = [];

    public static function createFromStorySection(StorySection $s): StoryStep
    {
        $step = new StoryStep();

        $step->style = $s->getStyle();
        $step->background = $s->getBackground();
        $step->image = $s->getImage();
        $step->content = $s->getContent();
        $step->choices = [];

        return $step;
    }
}