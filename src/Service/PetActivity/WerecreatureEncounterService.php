<?php
namespace App\Service\PetActivity;

use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\StatusEffectEnum;
use App\Model\ComputedPetSkills;
use App\Repository\ItemRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\StatusEffectService;

class WerecreatureEncounterService
{
    private $petExperienceService;
    private $squirrel3;
    private $itemRepository;
    private $responseService;
    private $inventoryService;
    private $statusEffectService;
    private PetActivityLogTagRepository $petActivityLogTagRepository;

    public function __construct(
        PetExperienceService $petExperienceService, Squirrel3 $squirrel3, ItemRepository $itemRepository,
        ResponseService $responseService, InventoryService $inventoryService, StatusEffectService $statusEffectService,
        PetActivityLogTagRepository $petActivityLogTagRepository
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->squirrel3 = $squirrel3;
        $this->itemRepository = $itemRepository;
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->statusEffectService = $statusEffectService;
        $this->petActivityLogTagRepository = $petActivityLogTagRepository;
    }

    public function encounterWerecreature(ComputedPetSkills $petWithSkills, string $doingWhat, array $tags): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $message = 'Under the influence of the full moon, a werecreature leapt out and attacked %pet:' . $pet->getId() . '.name% while they were out ' . $doingWhat . '! ';

        $hat = $pet->getHat();

        if($hat)
        {
            $treasure = $hat->getItem()->getTreasure();

            if($treasure && $treasure->getSilver() > 0)
            {
                $lootItem = $this->itemRepository->deprecatedFindOneByName($this->squirrel3->rngNextFromArray([
                    'Talon', 'Fluff'
                ]));

                $pet
                    ->increaseEsteem($this->squirrel3->rngNextInt(2, 4))
                    ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
                ;

                $message .= 'However, upon seeing %pet:' . $pet->getId() . '.name%\'s silver ' . $hat->getItem()->getName() . ', the creature ran off, dropping ' . $lootItem->getNameWithArticle() . ' as it went!';

                $activityLog = $this->responseService->createActivityLog($pet, $message, '')
                    ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames(array_merge($tags, [ 'Werecreature', 'Fighting' ])))
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, true);

                $this->inventoryService->petCollectsItem($lootItem, $pet, $pet->getName() . ' scared off a werecreature, and received this.', $activityLog);

                return $activityLog;
            }
        }

        $tool = $pet->getTool();

        if($tool)
        {
            $treasure = $tool->getItem()->getTreasure();

            if($treasure && $treasure->getSilver() > 0)
            {
                $lootItem = $this->itemRepository->deprecatedFindOneByName($this->squirrel3->rngNextFromArray([
                    'Talon', 'Fluff'
                ]));

                $pet
                    ->increaseEsteem($this->squirrel3->rngNextInt(2, 4))
                    ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
                ;

                $message .= '%pet:' . $pet->getId() . '.name% brandished their silver ' . $tool->getItem()->getName() . '; the creature ran off at the sight of it, dropping ' . $lootItem->getNameWithArticle() . ' as it went!';

                $activityLog = $this->responseService->createActivityLog($pet, $message, '')
                    ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames(array_merge($tags, [ 'Werecreature', 'Fighting' ])))
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, true);

                $this->inventoryService->petCollectsItem($lootItem, $pet, $pet->getName() . ' chased off a werecreature, and received this.', $activityLog);

                return $activityLog;
            }
        }

        $skill = 20 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl()->getTotal();

        if($this->squirrel3->rngNextInt(1, $skill) >= 15)
        {
            $lootItem = $this->itemRepository->deprecatedFindOneByName($this->squirrel3->rngNextFromArray([
                'Talon', 'Fluff'
            ]));

            $silverblood = $pet->hasMerit(MeritEnum::SILVERBLOOD);

            $this->statusEffectService->applyStatusEffect($pet, StatusEffectEnum::BITTEN_BY_A_WERECREATURE, 1);

            $pet
                ->increaseEsteem($this->squirrel3->rngNextInt(2, 4))
                ->increaseSafety(-$this->squirrel3->rngNextInt(2, 4))
            ;

            if($silverblood)
                $message .= '%pet:' . $pet->getId() . '.name% beat the creature back, and received ' . $lootItem->getNameWithArticle() . ', but also received a bite during the encounter... (Good thing they\'re a silverblood!)';
            else
                $message .= '%pet:' . $pet->getId() . '.name% beat the creature back, and received ' . $lootItem->getNameWithArticle() . ', but also received a bite during the encounter... (Uh oh...)';

            $activityLog = $this->responseService->createActivityLog($pet, $message, '')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames(array_merge($tags, [ 'Werecreature', 'Fighting' ])))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, true);

            $this->inventoryService->petCollectsItem($lootItem, $pet, $pet->getName() . ' received this from a fight with a werecreature.', $activityLog);

            return $activityLog;
        }
        else
        {
            $pet
                ->increaseEsteem(-$this->squirrel3->rngNextInt(2, 4))
                ->increaseSafety(-$this->squirrel3->rngNextInt(4, 8))
            ;

            $this->statusEffectService->applyStatusEffect($pet, StatusEffectEnum::BITTEN_BY_A_WERECREATURE, 1);

            $message .= '%pet:' . $pet->getId() . '.name% eventually escaped the creature, but not before being scratched and bitten! (Uh oh!)';

            $activityLog = $this->responseService->createActivityLog($pet, $message, '')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames(array_merge($tags, [ 'Werecreature', 'Fighting' ])))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, true);

            return $activityLog;
        }
    }

}