<?php
namespace App\Service;

use App\Entity\StorySection;
use App\Entity\User;
use App\Functions\ArrayFunctions;
use App\Model\StoryStep;
use App\Model\StoryStepChoice;
use App\Repository\StoryRepository;
use App\Repository\StorySectionRepository;
use App\Repository\UserQuestRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class StoryService
{
    private $storyRepository;
    private $storySectionRepository;
    private $userQuestRepository;

    public function __construct(
        StoryRepository $storyRepository, StorySectionRepository $storySectionRepository,
        UserQuestRepository $userQuestRepository
    )
    {
        $this->storyRepository = $storyRepository;
        $this->storySectionRepository = $storySectionRepository;
        $this->userQuestRepository = $userQuestRepository;
    }

    public function getStoryStep(User $user, int $storyId): StoryStep
    {
        $section = $this->getCurrentSection($user, $storyId);

        return $this->serializeStorySection($user, $section);
    }

    private function getCurrentSection(User $user, int $storyId): StorySection
    {
        $story = $this->storyRepository->find($storyId);

        if (!$story)
            throw new NotFoundHttpException('That Story doesn\'t exist! (Uh oh! Is something broken? Maybe reload and try again?)');

        $stepId = $this->userQuestRepository->findOrCreate($user, $story->getTitle() . ' - Step', $story->getFirstSection()->getId());

        $section = $this->storySectionRepository->find($stepId);

        if(!$section)
            throw new \Exception('Uh oh! You\'re apparently on a step of the story that doesn\'t exist! This is a terrible error! Please let Ben know!');

        return $section;
    }

    private function serializeStorySection(User $user, StorySection $section)
    {
        $storyStep = StoryStep::createFromStorySection($section);

        foreach($section->getChoices() as $choice)
        {
            if($this->choiceIsVisible($user, $choice))
            {
                $c = new StoryStepChoice();
                $c->text = $choice['text'];
                $c->enabled = $this->choiceIsEnabled($user, $choice);

                $storyStep->choices[] = $c;
            }
        }

        return $storyStep;
    }

    public function makeChoice(User $user, int $storyId, string $userChoice): StoryStep
    {
        $section = $this->getCurrentSection($user, $storyId);

        $choice = ArrayFunctions::find_one($section->getChoices(), function($choice) use ($userChoice) {
            return $choice['text'] === $userChoice;
        });

        if(!$choice)
            throw new UnprocessableEntityHttpException('There is no such option. (Maybe reload and try again?)');

        return $this->serializeStorySection($user, $section);
    }
}