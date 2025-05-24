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
    private User $user;
    private UserQuest $step;
    private Story $story;
    private StorySection $currentSection;
    /** @var ItemQuantity[]|null */ private ?array $userInventory = null;

    private Inventory $callingInventory;

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
    public function doStory(User $user, int $storyId, ParameterBag $request, Inventory $callingInventory = null): StoryStep
    {
        $story = $this->em->getRepository(Story::class)->find($storyId);

        if (!$story)
            throw new PSPNotFoundException('That Story doesn\'t exist! (Uh oh! Is something broken? Maybe reload and try again?)');

        $this->story = $story;
        $this->callingInventory = $callingInventory;
        $this->user = $user;
        $this->step = UserQuestRepository::findOrCreate($this->em, $user, $this->story->getQuestValue(), $this->story->getFirstSection()->getId());

        $this->setCurrentSection();

        if($request->has('choice'))
        {
            $choice = trim($request->getString('choice'));

            if($choice === '')
                throw new PSPFormValidationException('You didn\'t choose a choice!');

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
            throw new PSPFormValidationException('There is no such option. (Maybe reload and try again?)');

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
            $requiredInventory = InventoryService::deserializeItemList($this->em, $choice['requiredInventory']);

            foreach($requiredInventory as $quantity)
                $this->inventoryService->loseItem($this->user, $quantity->item->getId(), [ LocationEnum::Home, LocationEnum::Basement ], $quantity->quantity);
        }
    }

    /**
     * @throws \Exception
     */
    private function setCurrentSection(): void
    {
        $this->currentSection = $this->em->getRepository(StorySection::class)->find($this->step->getValue());

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
                $c->exitOnSelect = StoryService::choiceContainsExit($choice);

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
            $requiredInventory = InventoryService::deserializeItemList($this->em, $choice['requiredInventory']);
            $userInventory = $this->getUserInventory();

            if(!InventoryService::hasRequiredItems($requiredInventory, $userInventory))
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
            $this->userInventory = $this->inventoryService->getInventoryQuantities($this->user, LocationEnum::Home, 'name');

        return $this->userInventory;
    }

    private static function choiceContainsExit(array $choice): bool
    {
        return ArrayFunctions::any($choice['actions'], fn($action) => $action['type'] === StoryActionTypeEnum::Exit);
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
            case StoryActionTypeEnum::SetStep:
                $this->setStep($action['step']);
                break;

            case StoryActionTypeEnum::ReceiveItem:
                $lockedToOwner = array_key_exists('locked', $action) && $action['locked'];
                $description = str_replace([ '%user.name%' ], [ $this->user->getName() ], $action['description']);

                $this->inventoryService->receiveItem($action['item'], $this->user, null, $description, LocationEnum::Home, $lockedToOwner);

                break;

            case StoryActionTypeEnum::DonateItem:
                $this->museumService->forceDonateItem($this->user, $action['item'], $action['description']);
                break;

            case StoryActionTypeEnum::LoseItem:
                $itemId = ItemRepository::getIdByName($this->em, $action['item']);
                $this->inventoryService->loseItem($this->user, $itemId, [ LocationEnum::Home, LocationEnum::Basement ]);
                break;

            case StoryActionTypeEnum::LoseCallingInventory:
                if($this->callingInventory)
                    $this->em->remove($this->callingInventory);
                else
                    throw new \InvalidArgumentException('Ben made a boo-boo: no calling inventory was set.');

                $this->responseService->setReloadInventory();

                break;

            case StoryActionTypeEnum::IncrementStat:
                $this->userStatsRepository->incrementStat($this->user, $action['stat'], array_key_exists('change', $action) ? $action['change'] : 1);
                break;

            case StoryActionTypeEnum::SetQuestValue:
                UserQuestRepository::findOrCreate($this->em, $this->user, $action['quest'], $action['value'])
                    ->setValue($action['value'])
                ;
                break;

            case StoryActionTypeEnum::UnlockTrader:
                if(!$this->user->hasUnlockedFeature(UnlockableFeatureEnum::Trader))
                    UserUnlockedFeatureHelpers::create($this->em, $this->user, UnlockableFeatureEnum::Trader);
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
    private function setStep(int $newStep): void
    {
        $this->step->setValue($newStep);
        $this->setCurrentSection();
    }
}
