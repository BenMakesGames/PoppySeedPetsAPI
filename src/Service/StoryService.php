<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Service;

use App\Entity\Inventory;
use App\Entity\Story;
use App\Entity\StorySection;
use App\Entity\User;
use App\Entity\UserQuest;
use App\Enum\LocationEnum;
use App\Enum\StoryActionTypeEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ArrayFunctions;
use App\Functions\ItemRepository;
use App\Functions\UserQuestRepository;
use App\Functions\UserUnlockedFeatureHelpers;
use App\Model\ItemQuantity;
use App\Model\StoryStep;
use App\Model\StoryStepChoice;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class StoryService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly InventoryService $inventoryService,
        private readonly JsonLogicParserService $jsonLogicParserService,
        private readonly UserStatsService $userStatsRepository,
        private readonly ResponseService $responseService,
        private readonly MuseumService $museumService
    )
    {
    }

    /**
     * @throws \Exception
     */
    public function doStory(User $user, int $storyId, ParameterBag $request, Inventory $callingInventory): StoryStep
    {
        $story = $this->em->getRepository(Story::class)->find($storyId)
            ?? throw new PSPNotFoundException('That Story doesn\'t exist! (Uh oh! Is something broken? Maybe reload and try again?)');

        $step = UserQuestRepository::findOrCreate($this->em, $user, $story->getQuestValue(), $story->getFirstSection()->getId());

        $currentSection = $this->em->getRepository(StorySection::class)->find($step->getValue())
            ?? throw new \Exception('Uh oh! You\'re apparently on a step of the story that doesn\'t exist! This is a terrible error! Please let Ben know!');

        $storyState = new StoryState(
            user: $user,
            step: $step,
            story: $story,
            currentSection: $currentSection,
            userInventory: null,
            callingInventory: $callingInventory
        );

        if($request->has('choice'))
        {
            $choice = mb_trim($request->getString('choice'));

            if($choice === '')
                throw new PSPFormValidationException('You didn\'t choose a choice!');

            $response = $this->makeChoice($storyState, $choice);
        }
        else
            $response = $this->getStoryStep($storyState);

        $this->em->flush();

        return $response;
    }

    private function getStoryStep(StoryState $state): StoryStep
    {
        return $this->serializeStorySection($state);
    }

    /**
     * @throws \Exception
     */
    private function makeChoice(StoryState $state, string $userChoice): StoryStep
    {
        $availableChoices = $state->currentSection->getChoices()
            ?? throw new PSPFormValidationException('There is no such option. (Maybe reload and try again?)');

        $choice = ArrayFunctions::find_one($availableChoices, function($c) use ($userChoice) {
            return $c['text'] === $userChoice;
        });

        if(!$choice || !$this->choiceIsChoosable($state, $choice))
            throw new PSPFormValidationException('There is no such option. (Maybe reload and try again?)');

        // in case we had to create new stuff before interpreting the actions, flush the DB
        $this->em->flush();

        $this->payAnyCosts($state, $choice);
        $this->interpretActions($state, $choice['actions']);

        $this->em->flush();

        return $this->serializeStorySection($state);
    }

    private function payAnyCosts(StoryState $state, array $choice): void
    {
        if(array_key_exists('requiredInventory', $choice))
        {
            $requiredInventory = InventoryService::deserializeItemList($this->em, $choice['requiredInventory']);

            foreach($requiredInventory as $quantity)
                $this->inventoryService->loseItem($state->user, $quantity->item->getId(), [ LocationEnum::Home, LocationEnum::Basement ], $quantity->quantity);
        }
    }

    private function serializeStorySection(StoryState $state): StoryStep
    {
        $storyStep = StoryStep::createFromStorySection($state->currentSection);

        foreach($state->currentSection->getChoices() as $choice)
        {
            if($this->choiceIsVisible($state, $choice))
            {
                $c = new StoryStepChoice();
                $c->text = $choice['text'];
                $c->enabled = $this->choiceIsEnabled($state, $choice);
                $c->exitOnSelect = StoryService::choiceContainsExit($choice);

                $storyStep->choices[] = $c;
            }
        }

        return $storyStep;
    }

    private function choiceIsChoosable(StoryState $state, array $choice): bool
    {
        return
            $this->choiceIsVisible($state, $choice) &&
            $this->choiceIsEnabled($state, $choice)
        ;
    }

    private function choiceIsVisible(StoryState $state, array $choice): bool
    {
        if(array_key_exists('hideIf', $choice))
        {
            if($this->jsonLogicParserService->evaluate($choice['hideIf'], $state->user))
                return false;
        }

        return true;
    }

    private function choiceIsEnabled(StoryState $state, array $choice): bool
    {
        if(array_key_exists('requiredInventory', $choice))
        {
            $requiredInventory = InventoryService::deserializeItemList($this->em, $choice['requiredInventory']);
            $userInventory = $this->getUserInventory($state);

            if(!InventoryService::hasRequiredItems($requiredInventory, $userInventory))
                return false;
        }

        if(array_key_exists('disabledIf', $choice))
        {
            if($this->jsonLogicParserService->evaluate($choice['disabledIf'], $state->user))
                return false;
        }

        return true;
    }

    /**
     * @return ItemQuantity[]
     */
    private function getUserInventory(StoryState $state): array
    {
        if(!$state->userInventory)
            $state->userInventory = $this->inventoryService->getInventoryQuantities($state->user, LocationEnum::Home, 'name');

        return $state->userInventory;
    }

    private static function choiceContainsExit(array $choice): bool
    {
        return ArrayFunctions::any($choice['actions'], fn($action) => $action['type'] === StoryActionTypeEnum::Exit);
    }

    /**
     * @throws \Exception
     */
    private function interpretActions(StoryState $state, array $actions): void
    {
        foreach($actions as $action)
            $this->interpretAction($state, $action);
    }

    /**
     * @throws \Exception
     */
    private function interpretAction(StoryState $state, array $action): void
    {
        switch($action['type'])
        {
            case StoryActionTypeEnum::SetStep:
                $this->setStep($state, $action['step']);
                break;

            case StoryActionTypeEnum::ReceiveItem:
                $lockedToOwner = array_key_exists('locked', $action) && $action['locked'];
                $description = str_replace([ '%user.name%' ], [ $state->user->getName() ], $action['description']);

                $this->inventoryService->receiveItem($action['item'], $state->user, null, $description, LocationEnum::Home, $lockedToOwner);

                break;

            case StoryActionTypeEnum::DonateItem:
                $this->museumService->forceDonateItem($state->user, $action['item'], $action['description']);
                break;

            case StoryActionTypeEnum::LoseItem:
                $itemId = ItemRepository::getIdByName($this->em, $action['item']);
                $this->inventoryService->loseItem($state->user, $itemId, [ LocationEnum::Home, LocationEnum::Basement ]);
                break;

            case StoryActionTypeEnum::LoseCallingInventory:
                $this->em->remove($state->callingInventory);

                $this->responseService->setReloadInventory();

                break;

            case StoryActionTypeEnum::IncrementStat:
                $this->userStatsRepository->incrementStat($state->user, $action['stat'], array_key_exists('change', $action) ? $action['change'] : 1);
                break;

            case StoryActionTypeEnum::SetQuestValue:
                UserQuestRepository::findOrCreate($this->em, $state->user, $action['quest'], $action['value'])
                    ->setValue($action['value'])
                ;
                break;

            case StoryActionTypeEnum::UnlockTrader:
                if(!$state->user->hasUnlockedFeature(UnlockableFeatureEnum::Trader))
                    UserUnlockedFeatureHelpers::create($this->em, $state->user, UnlockableFeatureEnum::Trader);
                break;

            case StoryActionTypeEnum::Exit:
                break;

            default:
                throw new \Exception('Unhandled story action type "' . $action['type'] . '"');
        }
    }

    /**
     * @throws \Exception
     */
    private function setStep(StoryState $state, int $newStep): void
    {
        $state->step->setValue($newStep);

        $state->currentSection = $this->em->getRepository(StorySection::class)->find($newStep)
            ?? throw new \Exception('Uh oh! You\'re apparently on a step of the story that doesn\'t exist! This is a terrible error! Please let Ben know!');
    }
}

final class StoryState
{
    public function __construct(
        public readonly User $user,
        public UserQuest $step,
        public Story $story,
        public StorySection $currentSection,
        /** @var ItemQuantity[]|null $userInventory */
        public ?array $userInventory,
        public Inventory $callingInventory
    )
    {
    }
}