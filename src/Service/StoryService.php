<?php
namespace App\Service;

use App\Entity\Inventory;
use App\Entity\MuseumItem;
use App\Entity\Story;
use App\Entity\StorySection;
use App\Entity\User;
use App\Entity\UserQuest;
use App\Enum\LocationEnum;
use App\Enum\StoryActionTypeEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Model\ItemQuantity;
use App\Model\StoryStep;
use App\Model\StoryStepChoice;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\StoryRepository;
use App\Repository\StorySectionRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class StoryService
{
    private $em;
    private $storyRepository;
    private $storySectionRepository;
    private $userQuestRepository;
    private $inventoryService;
    private $inventoryRepository;
    private $itemRepository;
    private $jsonLogicParserService;
    private $userStatsRepository;
    private $responseService;
    private $museumService;

    /** @var User */ private $user;
    /** @var UserQuest */ private $step;
    /** @var Story */ private $story;
    /** @var StorySection */ private $currentSection;
    /** @var ItemQuantity[] */ private $userInventory;

    /** @var Inventory */ private $callingInventory;

    public function __construct(
        EntityManagerInterface $em, StoryRepository $storyRepository, StorySectionRepository $storySectionRepository,
        UserQuestRepository $userQuestRepository, InventoryService $inventoryService, ItemRepository $itemRepository,
        JsonLogicParserService $jsonLogicParserService, UserStatsRepository $userStatsRepository,
        InventoryRepository $inventoryRepository, ResponseService $responseService, MuseumService $museumService
    )
    {
        $this->em = $em;
        $this->storyRepository = $storyRepository;
        $this->storySectionRepository = $storySectionRepository;
        $this->userQuestRepository = $userQuestRepository;
        $this->inventoryService = $inventoryService;
        $this->itemRepository = $itemRepository;
        $this->jsonLogicParserService = $jsonLogicParserService;
        $this->userStatsRepository = $userStatsRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->responseService = $responseService;
        $this->museumService = $museumService;
    }

    /**
     * @param User $user
     * @param int $storyId
     * @param ParameterBag $request
     * @return StoryStep
     * @throws \Exception
     */
    public function doStory(User $user, int $storyId, ParameterBag $request, Inventory $callingInventory = null): StoryStep
    {
        $this->story = $this->storyRepository->find($storyId);

        if (!$this->story)
            throw new NotFoundHttpException('That Story doesn\'t exist! (Uh oh! Is something broken? Maybe reload and try again?)');

        $this->callingInventory = $callingInventory;
        $this->user = $user;
        $this->step = $this->userQuestRepository->findOrCreate($user, $this->story->getQuestValue(), $this->story->getFirstSection()->getId());

        $this->setCurrentSection();

        if($request->has('choice'))
        {
            $choice = trim($request->get('choice', ''));

            if($choice === '')
                throw new UnprocessableEntityHttpException('You didn\'t choose a choice!');

            $response = $this->makeChoice($choice);
        }
        else
            $response = $this->getStoryStep();

        $this->em->flush();

        return $response;
    }

    /**
     * @throws \Exception
     */
    private function getStoryStep(): StoryStep
    {
        if(!$this->story) throw new \Exception('StoryService was not properly prepared!');

        return $this->serializeStorySection();
    }

    /**
     * @throws \Exception
     */
    private function makeChoice(string $userChoice): StoryStep
    {
        if(!$this->story) throw new \Exception('StoryService was not properly prepared!');

        $choice = ArrayFunctions::find_one($this->currentSection->getChoices(), function($c) use ($userChoice) {
            return $c['text'] === $userChoice;
        });

        if(!$choice || !$this->choiceIsChoosable($choice))
            throw new UnprocessableEntityHttpException('There is no such option. (Maybe reload and try again?)');

        // in case we had to create new stuff before interpreting the actions, flush the DB
        $this->em->flush();

        $this->payAnyCosts($choice);
        $this->interpretActions($choice['actions']);

        $this->em->flush();

        return $this->serializeStorySection();
    }

    private function payAnyCosts(array $choice): void
    {
        if(array_key_exists('requiredInventory', $choice))
        {
            $requiredInventory = $this->inventoryService->deserializeItemList($choice['requiredInventory']);

            foreach($requiredInventory as $quantity)
                $this->inventoryService->loseItem($quantity->item, $this->user, [ LocationEnum::HOME, LocationEnum::BASEMENT ], $quantity->quantity);
        }
    }

    /**
     * @throws \Exception
     */
    private function setCurrentSection(): void
    {
        $this->currentSection = $this->storySectionRepository->find($this->step->getValue());

        if(!$this->currentSection) throw new \Exception('Uh oh! You\'re apparently on a step of the story that doesn\'t exist! This is a terrible error! Please let Ben know!');
    }

    private function serializeStorySection(): StoryStep
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

    private function choiceIsChoosable(array $choice): bool
    {
        return
            $this->choiceIsVisible($choice) &&
            $this->choiceIsEnabled($choice)
        ;
    }

    private function choiceIsVisible(array $choice): bool
    {
        if(array_key_exists('hideIf', $choice))
        {
            if($this->jsonLogicParserService->evaluate($choice['hideIf'], $this->user))
                return false;
        }

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

        if(array_key_exists('disabledIf', $choice))
        {
            if($this->jsonLogicParserService->evaluate($choice['disabledIf'], $this->user))
                return false;
        }

        return true;
    }

    /**
     * @return ItemQuantity[]
     */
    private function getUserInventory(): array
    {
        if(!$this->userInventory)
            $this->userInventory = $this->inventoryRepository->getInventoryQuantities($this->user, LocationEnum::HOME, 'name');

        return $this->userInventory;
    }

    private function choiceContainsExit(array $choice): bool
    {
        return ArrayFunctions::any($choice['actions'], fn($action) => $action['type'] === StoryActionTypeEnum::EXIT);
    }

    /**
     * @throws \Exception
     */
    private function interpretActions(array $actions): void
    {
        foreach($actions as $action)
            $this->interpretAction($action);
    }

    /**
     * @throws \Exception
     */
    private function interpretAction(array $action): void
    {
        switch($action['type'])
        {
            case StoryActionTypeEnum::SET_STEP:
                $this->setStep($action['step']);
                break;

            case StoryActionTypeEnum::RECEIVE_ITEM:
                $lockedToOwner = array_key_exists('locked', $action) && $action['locked'];
                $description = str_replace([ '%user.name%' ], [ $this->user->getName() ], $action['description']);

                $this->inventoryService->receiveItem($action['item'], $this->user, null, $description, LocationEnum::HOME, $lockedToOwner);

                break;

            case StoryActionTypeEnum::DONATE_ITEM:
                $this->museumService->forceDonateItem($this->user, $action['item'], $action['description']);
                break;

            case StoryActionTypeEnum::LOSE_ITEM:
                $this->inventoryService->loseItem($action['item'], $this->user, [ LocationEnum::HOME, LocationEnum::BASEMENT ]);
                break;

            case StoryActionTypeEnum::LOSE_CALLING_INVENTORY:
                if($this->callingInventory)
                    $this->em->remove($this->callingInventory);
                else
                    throw new \InvalidArgumentException('Ben made a boo-boo: no calling inventory was set.');

                $this->responseService->setReloadInventory();

                break;

            case StoryActionTypeEnum::INCREMENT_STAT:
                $this->userStatsRepository->incrementStat($this->user, $action['stat'], array_key_exists('change', $action) ? $action['change'] : 1);
                break;

            case StoryActionTypeEnum::SET_QUEST_VALUE:
                $this->userQuestRepository->findOrCreate($this->user, $action['quest'], $action['value'])
                    ->setValue($action['value'])
                ;
                break;

            case StoryActionTypeEnum::UNLOCK_TRADER:
                $this->user->setUnlockedTrader();
                break;

            case StoryActionTypeEnum::EXIT:
                break;

            default:
                throw new \Exception('Unhandled story action type "' . $action['type'] . '"');
        }
    }

    /**
     * @param int $newStep
     * @throws \Exception
     */
    private function setStep(int $newStep): void
    {
        $this->step->setValue($newStep);
        $this->setCurrentSection();
    }
}
