<?php
declare(strict_types=1);

namespace App\Model;

use App\Entity\StorySection;
use Symfony\Component\Serializer\Attribute\Groups;

class StoryStep
{
    #[Groups(['story'])]
    public string $storyTitle;

    #[Groups(['story'])]
    public string $style;

    #[Groups(['story'])]
    public ?string $background;

    #[Groups(['story'])]
    public ?string $image;

    #[Groups(['story'])]
    public string $content;

    /** @var StoryStepChoice[] */
    #[Groups(['story'])]
    public array $choices = [];

    public static function createFromStorySection(StorySection $s): StoryStep
    {
        $step = new StoryStep();

        $step->storyTitle = $s->getStory()->getTitle();
        $step->style = $s->getStyle();
        $step->background = $s->getBackground();
        $step->image = $s->getImage();
        $step->content = $s->getContent();
        $step->choices = [];

        return $step;
    }
}