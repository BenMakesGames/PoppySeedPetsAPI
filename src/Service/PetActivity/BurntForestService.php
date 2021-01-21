<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\GuildEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ArrayFunctions;
use App\Functions\NumberFunctions;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;

class BurntForestService
{
    private $petExperienceService;
    private $responseService;
    private $inventoryService;
    private $userQuestRepository;
    private $squirrel3;

    public function __construct(
        PetExperienceService $petExperienceService, ResponseService $responseService, InventoryService $inventoryService,
        UserQuestRepository $userQuestRepository, Squirrel3 $squirrel3
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->userQuestRepository = $userQuestRepository;
        $this->squirrel3 = $squirrel3;
    }

    public function adventure(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $maxSkill = 10 + ($petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getUmbra()->getTotal()) / 2 - $pet->getAlcohol();

        $maxSkill = NumberFunctions::clamp($maxSkill, 1, 15);

        $roll = $this->squirrel3->rngNextInt(1, $maxSkill);

        $activityLog = null;
        $changes = new PetChanges($pet);

        switch($roll)
        {
            case 1:
            case 2:
            case 3:
                $activityLog = $this->failToFindAnything($petWithSkills);
                break;

            case 4:
            case 5:
                $activityLog = $this->findAWoundedFairy($petWithSkills);
                break;

            case 6:
                $activityLog = $this->findABitOfCharcoal($petWithSkills);
                break;

            case 7:
                $activityLog = $this->findSquirmingMass($petWithSkills);
                break;

            case 8:
            case 9:
                $activityLog = $this->findBurningTree($petWithSkills);
                break;

            case 10:
            case 11:
                $activityLog = $this->findTearInTheTapestry($petWithSkills);
                break;

            case 12:
            case 13:
                $activityLog = $this->breakToolBonus($pet);
                break;

            case 14:
            case 15:
                $activityLog = $this->findScalySquirmingMass($petWithSkills);
                break;
        }

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));
    }

    private function failToFindAnything(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::UMBRA, false);

        return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, but all they found were ashes...', 'icons/activity-logs/confused');
    }

    private function findABitOfCharcoal(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($this->squirrel3->rngNextInt(1, 2000) < $petWithSkills->getPerception()->getTotal())
        {
            $loot = 'Striped Microcline';
            $pet->increaseEsteem(4);
        }
        else
        {
            $loot = $this->squirrel3->rngNextFromArray([
                'Charcoal', 'Charcoal',
                'Crooked Stick',
                'Chanterelle',
                'Iron Ore',
                'Silica Grounds',
                'Grandparoot',
                $this->squirrel3->rngNextFromArray([ 'Glowing Four-sided Die', 'Glowing Six-sided Die' ])
            ]);
        }

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);

        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the Burnt Forest for a while. They didn\'t encounter anything _super_ weird, but they did find ' . $loot . '!', '');

        $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' found this while exploring the Burnt Forest.', $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

        return $activityLog;
    }

    private function findAWoundedFairy(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal());

        if($roll >= 11)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and found a wounded fairy! They bandaged it up; thankful, the Fairy cast a minor blessing on ' . $pet->getName() . '!', '');

            $this->inventoryService->applyStatusEffect($pet, $this->squirrel3->rngNextFromArray([
                StatusEffectEnum::INSPIRED, StatusEffectEnum::ONEIRIC, StatusEffectEnum::EXTRA_EXTROVERTED
            ]), 4 * 60);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem($this->squirrel3->rngNextInt(1, 2));

            // triggers Hyssop letter #1
            $this->userQuestRepository->findOrCreate($pet->getOwner(), 'Can Receive Letters from Fairies', 1);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::UMBRA, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and found a wounded fairy! They weren\'t able to help, though. The fairy thanked them for trying, anyway...', '');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
        }

        return $activityLog;
    }

    private function findSquirmingMass(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getBrawl()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal());

        $resistsFire = $petWithSkills->getHasProtectionFromHeat()->getTotal() > 0;

        if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);

            $loot = $this->squirrel3->rngNextFromArray([ 'Tentacle', 'Tentacle', 'Quintessence' ]);

            if($resistsFire || $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getStamina()->getTotal()) >= 10)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and encountered a mass of flaming tentacles! They beat the tentacles back, and got a ' . $loot . '!', '');
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and encountered a mass of flaming tentacles! They beat the tentacles back, and got a ' . $loot . ', but not without getting burned in the fight!', '');
                $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 4));
            }

            $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 4));
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' received this by defeating a mass of flaming tentacles in the Burnt Forest!', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, false);

            if($resistsFire || $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getStamina()->getTotal()) >= 15)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and encountered a mass of flaming tentacles! They tried to fight, but were forced to flee...', '');
                $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 4));
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and encountered a mass of flaming tentacles! They tried to fight, but got burned by one of the tentacles, and was forced to flee...', '');
                $pet->increaseSafety(-$this->squirrel3->rngNextInt(4, 8));
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
        }

        return $activityLog;
    }

    private function findBurningTree(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraRoll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal());
        $brawlRoll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getBrawl()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal());
        $exp = 1;

        if($pet->isInGuild(GuildEnum::LIGHT_AND_SHADOW))
            $brawlRoll = 0;
        else if($pet->isInGuild(GuildEnum::THE_UNIVERSE_FORGETS))
            $umbraRoll = 0;

        $loot = $this->squirrel3->rngNextFromArray([
            'Crooked Stick',
            'Quintessence',
            $this->squirrel3->rngNextFromArray([ 'Red', 'Orange', 'Pamplemousse', 'Apricot', 'Naner' ])
        ]);

        if($umbraRoll > $brawlRoll)
        {
            if($umbraRoll >= 15)
            {
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);

                if($pet->isInGuild(GuildEnum::LIGHT_AND_SHADOW))
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and found a fire spirit burning a still-living tree! ' . $pet->getName() . ' realized that the spirit was just hungry, and found it a piece of Charcoal to eat, instead. Grateful, the tree offered them ' . $loot . '.', 'guilds/light-and-shadow');
                    $pet->getGuildMembership()->increaseReputation();
                    $exp = 2;
                }
                else
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and found a fire spirit burning a still-living tree! ' . $pet->getName() . ' found a piece of Charcoal, and convinced the spirit to eat that, instead. Grateful, the tree offered them ' . $loot . '.', '');

                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' was given this by a tree in the Burnt Forest, as thanks for saving it!', $activityLog);
                $pet->increaseLove($this->squirrel3->rngNextInt(2, 4));
                $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::UMBRA ]);

                // triggers Hyssop letter #2
                $oldValue = $this->userQuestRepository->findOrCreate($pet->getOwner(), 'Can Receive Letters from Fairies', 0);
                if($oldValue->getValue() === 1)
                    $oldValue->setValue(2);
            }
            else
            {
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, false);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and found a fire spirit burning a still-living tree! ' . $pet->getName() . ' tried to find a piece of Charcoal to distract the spirit with, but couldn\'t find any...', '');
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            }
        }
        else
        {
            if($brawlRoll >= 15)
            {
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);

                if($pet->isInGuild(GuildEnum::THE_UNIVERSE_FORGETS))
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and found a fire spirit burning a still-living tree! ' . $pet->getName() . ' immediately put the fire out so that it could no longer harm anyone; grateful, the tree offered them ' . $loot . '.', 'guilds/the-universe-forgets');
                    $pet->getGuildMembership()->increaseReputation();
                    $exp = 2;
                }
                else
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and found a fire spirit burning a still-living tree! ' . $pet->getName() . ' was able to put the fire out; grateful, the tree offered them ' . $loot . '.', '');

                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' was given this by a tree in the Burnt Forest, as thanks for saving it!', $activityLog);
                $pet->increaseLove($this->squirrel3->rngNextInt(2, 4));
                $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::BRAWL ]);
            }
            else
            {
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, false);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and found a fire spirit burning a still-living tree! ' . $pet->getName() . ' tried to put out the fire, but by the time they chased it off, the tree was already dead...', '');
                $pet->increaseEsteem(-$this->squirrel3->rngNextInt(2, 4));
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
            }
        }

        return $activityLog;
    }

    private function findTearInTheTapestry(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal());

        if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);

            if($pet->isInGuild(GuildEnum::TAPESTRIES))
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and found a tear in the fabric of reality! They were able to stitch it back together, and got some Quintessence!', 'guild/tapestries');
                $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' got this in the Burnt Forest while repairing a tear in the fabric of reality.', $activityLog);
                $pet->getGuildMembership()->increaseReputation();
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and found a tear in the fabric of reality! It was a little intimidating, but they managed to harvest some Quintessence!', '');
                $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' got this in the Burnt Forest from tear in the fabric of reality.', $activityLog);
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::UMBRA, false);

            if($pet->isInGuild(GuildEnum::TAPESTRIES))
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and found a tear in the fabric of reality! They tried to repair it, but were worried about getting unraveled, themselves!', '');
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and found a tear in the fabric of reality! They thought about harvesting some Quintessence, but were worried about getting unraveled, themselves!', '');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
        }

        return $activityLog;
    }

    private function breakToolBonus(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::OTHER, true);

        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to visit the Burnt Forest, but a crack of light appeared on ' . $pet->getTool()->getItem()->getName() . ', and it lost its "' . $pet->getTool()->getEnchantment()->getName() . '" bonus! Before the crack faded, a strange piece of paper slipped out of it...', '');

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

        $this->inventoryService->petCollectsItem('Fairy\'s Scroll', $pet, 'This slipped out of a crack of light in ' . $pet->getName() . '\'s ' . $pet->getTool()->getItem()->getName() . '...', $activityLog);

        $pet->getTool()->setEnchantment(null);

        return $activityLog;
    }

    private function findScalySquirmingMass(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getBrawl()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal());

        $resistsFire = $petWithSkills->getHasProtectionFromHeat()->getTotal() > 0;

        if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);

            $loot = $this->squirrel3->rngNextFromArray([ 'Dark Scales', 'Quinacridone Magenta Dye', 'Quintessence' ]);

            if($resistsFire || $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getStamina()->getTotal()) >= 15)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and encountered a mass of flaming, scaly tentacles! They beat the tentacles back, and got a Tentacle, and ' . $loot . '!', '');
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and encountered a mass of flaming, scaly tentacles! They beat the tentacles back, and got a Tentacle, and ' . $loot . ', but not without getting burned in the fight!', '');
                $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 4));
            }

            $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 4));

            $this->inventoryService->petCollectsItem('Tentacle', $pet, $pet->getName() . ' received this by defeating a mass of flaming, scaly tentacles in the Burnt Forest!', $activityLog);
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' received this by defeating a mass of flaming, scaly tentacles in the Burnt Forest!', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ]);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, false);

            if($resistsFire || $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getStamina()->getTotal()) >= 20)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and encountered a mass of flaming, scaly tentacles! They tried to fight, but were forced to flee...', '');
                $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 4));
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and encountered a mass of flaming, scaly tentacles! They tried to fight, but got burned by one of the tentacles, and was forced to flee...', '');
                $pet->increaseSafety(-$this->squirrel3->rngNextInt(4, 8));
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
        }

        return $activityLog;
    }
}
