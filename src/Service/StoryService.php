<?php
namespace App\Service;

use App\Entity\Story;
use App\Entity\StorySection;
use App\Entity\User;
use App\Entity\UserQuest;
use App\Enum\LocationEnum;
use App\Enum\StoryActionTypeEnum;
use App\Functions\ArrayFunctions;
use App\Model\ItemQuantity;
use App\Model\StoryStep;
use App\Model\StoryStepChoice;
use App\Repository\ItemRepository;
use App\Repository\StoryRepository;
use App\Repository\StorySectionRepository;
use App\Repository\UserQuestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class StoryService
{
    private $em;
    private $storyRepository;
    private $storySectionRepository;
    private $userQuestRepository;
    private $inventoryService;
    private $itemRepository;

    /** @var User */ private $user;
    /** @var UserQuest */ private $step;
    /** @var Story */ private $story;
    /** @var StorySection */ private $currentSection;
    /** @var ItemQuantity[] */ private $userInventory;

    public function __construct(
        EntityManagerInterface $em, StoryRepository $storyRepository, StorySectionRepository $storySectionRepository,
        UserQuestRepository $userQuestRepository, InventoryService $inventoryService, ItemRepository $itemRepository
    )
    {
        $this->em = $em;
        $this->storyRepository = $storyRepository;
        $this->storySectionRepository = $storySectionRepository;
        $this->userQuestRepository = $userQuestRepository;
        $this->inventoryService = $inventoryService;
        $this->itemRepository = $itemRepository;
    }

    /**
     * @throws \Exception
     */
    public function prepareStory(User $user, int $storyId)
    {
        $this->story = $this->storyRepository->find($storyId);

        if (!$this->story)
            throw new NotFoundHttpException('That Story doesn\'t exist! (Uh oh! Is something broken? Maybe reload and try again?)');

        $this->user = $user;
        $this->step = $this->userQuestRepository->findOrCreate($user, $this->story->getQuestValue(), $this->story->getFirstSection()->getId());

        $this->setCurrentSection();
    }

    /**
     * @throws \Exception
     */
    public function getStoryStep(): StoryStep
    {
        if(!$this->story) throw new \Exception('StoryService was not properly prepared!');

        return $this->serializeStorySection();
    }

    /**
     * @throws \Exception
     */
    public function makeChoice(string $userChoice): StoryStep
    {
        if(!$this->story) throw new \Exception('StoryService was not properly prepared!');

        $choice = ArrayFunctions::find_one($this->currentSection->getChoices(), function($c) use ($userChoice) {
            return $c['text'] === $userChoice;
        });

        if(!$choice)
            throw new UnprocessableEntityHttpException('There is no such option. (Maybe reload and try again?)');

        // in case we had to create new stuff before interpreting the actions, flush the DB
        $this->em->flush();

        $this->payAnyCosts($choice);
        $this->interpretActions($choice['actions']);

        $this->em->flush();

        return $this->serializeStorySection();
    }

    private function payAnyCosts(array $choice)
    {
        if(array_key_exists('requiredInventory', $choice))
        {
            $requiredInventory = $this->inventoryService->deserializeItemList($choice['requiredInventory']);

            foreach($requiredInventory as $quantity)
                $this->inventoryService->loseItem($quantity->item, $this->user, LocationEnum::HOME, $quantity->quantity);
        }
    }

    /**
     * @throws \Exception
     */
    private function setCurrentSection()
    {
        $this->currentSection = $this->storySectionRepository->find($this->step->getValue());

        if(!$this->currentSection) throw new \Exception('Uh oh! You\'re apparently on a step of the story that doesn\'t exist! This is a terrible error! Please let Ben know!');
    }

    private function serializeStorySection()
    {
        $storyStep = StoryStep::createFromStorySection($this->currentSection);

        foreach($this->currentSection->getChoices() as $choice)
        {
            if($this->choiceIsVisible($choice))
            {
                $c = new StoryStepChoice();
                $c->text = $choice['text'];
                $c->enabled = $this->choiceIsEnabled($choice);
                $c->exitOnSelect = $this->choiceContainsExit($choice);

                $storyStep->choices[] = $c;
            }
        }

        return $storyStep;
    }

    private function choiceIsVisible(array $choice): bool
    {
        return true;
    }

    private function choiceIsEnabled(array $choice): bool
    {
        if(array_key_exists('requiredInventory', $choice))
        {
            $requiredInventory = $this->inventoryService->deserializeItemList($choice['requiredInventory']);
            $userInventory = $this->getUserInventory();

            if(!$this->inventoryService->hasRequiredItems($requiredInventory, $userInventory))
                return false;
        }

        return true;
    }

    private function getUserInventory()
    {
        if(!$this->userInventory)
            $this->userInventory = $this->itemRepository->getInventoryQuantities($this->user, LocationEnum::HOME, 'name');

        return $this->userInventory;
    }

    private function choiceContainsExit(array $choice): bool
    {
        return ArrayFunctions::any($choice['actions'], function($action) {
            return $action['type'] === StoryActionTypeEnum::EXIT;
        });
    }

    /**
     * @throws \Exception
     */
    private function interpretActions(array $actions)
    {
        foreach($actions as $action)
            $this->interpretAction($action);
    }

    /**
     * @throws \Exception
     */
    private function interpretAction(array $action)
    {
        switch($action['type'])
        {
            case StoryActionTypeEnum::SET_STEP:
                $this->setStep($action['step']);
                break;

            case StoryActionTypeEnum::RECEIVE_ITEM:
                $this->inventoryService->receiveItem($action['item'], $this->user, null, 'Sharuminyinka made this for ' . $this->user->getName() . '.', LocationEnum::HOME);
                break;

            case StoryActionTypeEnum::EXIT:
                break;

            default:
                throw new \Exception('Unhandled story action type "' . $action['type'] . '"');
        }
    }

    /**
     * @throws \Exception
     */
    private function setStep(int $newStep)
    {
        $this->step->setValue($newStep);
        $this->setCurrentSection();
    }
}