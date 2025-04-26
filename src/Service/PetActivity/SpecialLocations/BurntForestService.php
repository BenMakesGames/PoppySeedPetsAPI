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


namespace App\Service\PetActivity\SpecialLocations;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\GuildEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetBadgeEnum;
use App\Enum\PetSkillEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ActivityHelpers;
use App\Functions\EnchantmentRepository;
use App\Functions\NumberFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Functions\StatusEffectHelpers;
use App\Functions\UserQuestRepository;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Service\FieldGuideService;
use App\Service\HattierService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class BurntForestService
{
    public function __construct(
        private readonly PetExperienceService $petExperienceService,
        private readonly InventoryService $inventoryService,
        private readonly IRandom $rng,
        private readonly HattierService $hattierService,
        private readonly EntityManagerInterface $em,
        private readonly FieldGuideService $fieldGuideService
    )
    {
    }

    public function adventure(ComputedPetSkills $petWithSkills): void
    {
        $pet = $petWithSkills->getPet();
        $maxSkill = 10
            + (int)(
                (
                    $petWithSkills->getStrength()->getTotal()
                    + $petWithSkills->getBrawl()->getTotal()
                    + $petWithSkills->getIntelligence()->getTotal()
                    + $petWithSkills->getArcana()->getTotal()
                ) / 2
            )
            - $pet->getAlcohol()
            + $petWithSkills->getExploreUmbraBonus()->getTotal()
        ;

        $maxSkill = NumberFunctions::clamp($maxSkill, 1, 15);

        $roll = $this->rng->rngNextInt(1, $maxSkill);

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
        {
            $activityLog->setChanges($changes->compare($pet));

            $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Burnt Forest', ActivityHelpers::PetName($pet) . ' used their ' . $pet->getTool()->getFullItemName() . ' to visit the Burnt Forest.');

            PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::VISITED_THE_BURNT_FOREST, $activityLog);
        }
    }

    private function failToFindAnything(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::UMBRA, false);

        return PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, but all they found were ashes...')
            ->setIcon('icons/activity-logs/confused')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra' ]))
        ;
    }

    private function findABitOfCharcoal(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($this->rng->rngNextInt(1, 2000) < $petWithSkills->getPerception()->getTotal())
        {
            $loot = 'Striped Microcline';
            $pet->increaseEsteem(4);
        }
        else
        {
            $loot = $this->rng->rngNextFromArray([
                'Charcoal', 'Charcoal',
                'Crooked Stick',
                'Chanterelle',
                'Iron Ore',
                'Silica Grounds',
                'Grandparoot',
                $this->rng->rngNextFromArray([ 'Glowing Four-sided Die', 'Glowing Six-sided Die' ])
            ]);
        }

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% explored the Burnt Forest for a while. They didn\'t encounter anything _super_ weird, but they did find ' . $loot . '!')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra' ]))
        ;

        $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' found this while exploring the Burnt Forest.', $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::ARCANA ], $activityLog);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);

        return $activityLog;
    }

    private function findAWoundedFairy(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal());

        if($roll >= 11)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and found a wounded fairy! They bandaged it up; thankful, the Fairy cast a minor blessing on ' . $pet->getName() . '!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra', 'Fae-kind' ]))
            ;

            StatusEffectHelpers::applyStatusEffect($this->em, $pet, $this->rng->rngNextFromArray([
                StatusEffectEnum::INSPIRED, StatusEffectEnum::ONEIRIC, StatusEffectEnum::EXTRA_EXTROVERTED
            ]), 4 * 60);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::ARCANA ], $activityLog);
            $pet->increaseEsteem($this->rng->rngNextInt(1, 2));

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);

            // triggers Hyssop letter #1
            $oldValue = UserQuestRepository::findOrCreate($this->em, $pet->getOwner(), 'Can Receive Letters from Fairies', 0);
            if($oldValue->getValue() === 0)
                $oldValue->setValue(1);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and found a wounded fairy! They weren\'t able to help, though. The fairy thanked them for trying, anyway...')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra', 'Fae-kind' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::ARCANA ], $activityLog);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::UMBRA, false);
        }

        return $activityLog;
    }

    private function findSquirmingMass(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getBrawl()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal());

        $resistsFire = $petWithSkills->getHasProtectionFromHeat()->getTotal() > 0;

        if($roll >= 12)
        {
            $loot = $this->rng->rngNextFromArray([ 'Tentacle', 'Tentacle', 'Quintessence' ]);

            if($resistsFire || $this->rng->rngNextInt(1, 20 + $petWithSkills->getStamina()->getTotal()) >= 10)
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and encountered a mass of flaming tentacles! They beat the tentacles back, and got a ' . $loot . '!')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra', 'Fighting', 'Heatstroke' ]))
                ;
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and encountered a mass of flaming tentacles! They beat the tentacles back, and got a ' . $loot . ', but not without getting burned in the fight!')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra', 'Fighting', 'Heatstroke' ]))
                ;
                $pet->increaseSafety(-$this->rng->rngNextInt(2, 4));
            }

            $pet->increaseEsteem($this->rng->rngNextInt(2, 4));
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' received this by defeating a mass of flaming tentacles in the Burnt Forest!', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);
        }
        else
        {
            if($resistsFire || $this->rng->rngNextInt(1, 20 + $petWithSkills->getStamina()->getTotal()) >= 15)
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and encountered a mass of flaming tentacles! They tried to fight, but were forced to flee...')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra', 'Fighting', 'Heatstroke' ]))
                ;
                $pet->increaseSafety(-$this->rng->rngNextInt(2, 4));
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and encountered a mass of flaming tentacles! They tried to fight, but got burned by one of the tentacles, and was forced to flee...')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra', 'Fighting', 'Heatstroke' ]))
                ;
                $pet->increaseSafety(-$this->rng->rngNextInt(4, 8));
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, false);
        }

        return $activityLog;
    }

    private function findBurningTree(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraRoll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal());
        $brawlRoll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getBrawl()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal());
        $exp = 1;

        if($pet->isInGuild(GuildEnum::LIGHT_AND_SHADOW))
            $brawlRoll = 0;
        else if($pet->isInGuild(GuildEnum::THE_UNIVERSE_FORGETS))
            $umbraRoll = 0;

        $loot = $this->rng->rngNextFromArray([
            'Crooked Stick',
            'Quintessence',
            $this->rng->rngNextFromArray([ 'Red', 'Orange', 'Pamplemousse', 'Apricot', 'Naner', 'Yellowy Lime', 'Ponzu' ])
        ]);

        if($umbraRoll > $brawlRoll)
        {
            if($umbraRoll >= 15)
            {
                if($pet->isInGuild(GuildEnum::LIGHT_AND_SHADOW))
                {
                    $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and found a fire spirit burning a still-living tree! ' . $pet->getName() . ' realized that the spirit was just hungry, and found it a piece of Charcoal to eat, instead. Grateful, the tree offered them ' . $loot . '.')
                        ->setIcon('guilds/light-and-shadow')
                        ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra', 'Guild' ]))
                    ;
                    $pet->getGuildMembership()->increaseReputation();
                    $exp = 2;
                }
                else
                {
                    $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and found a fire spirit burning a still-living tree! ' . $pet->getName() . ' found a piece of Charcoal, and convinced the spirit to eat that, instead. Grateful, the tree offered them ' . $loot . '.')
                        ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra' ]))
                    ;
                }

                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' was given this by a tree in the Burnt Forest, as thanks for saving it!', $activityLog);
                $pet->increaseLove($this->rng->rngNextInt(2, 4));
                $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::ARCANA ], $activityLog);

                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);

                if($pet->hasMerit(MeritEnum::BEHATTED))
                {
                    $livingFlame = EnchantmentRepository::findOneByName($this->em, 'of Living Flame');

                    if(!$this->hattierService->userHasUnlocked($pet->getOwner(), $livingFlame))
                    {
                        $this->hattierService->unlockAuraDuringPetActivity(
                            $pet,
                            $activityLog,
                            $livingFlame,
                            'After finishing its Charcoal, the spirit decided to follow ' . ActivityHelpers::PetName($pet) . ' home in their hat!',
                            'After finishing its Charcoal, the spirit decided to follow ' . ActivityHelpers::PetName($pet) . ' home!',
                            ActivityHelpers::PetName($pet) . ' fed a hungry fire spirit a bit of Charcoal, and the spirit followed them home...'
                        );
                    }
                }

                // triggers Hyssop letter #2
                $oldValue = UserQuestRepository::findOrCreate($this->em, $pet->getOwner(), 'Can Receive Letters from Fairies', 0);
                if($oldValue->getValue() === 1)
                    $oldValue->setValue(2);
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and found a fire spirit burning a still-living tree! ' . $pet->getName() . ' tried to find a piece of Charcoal to distract the spirit with, but couldn\'t find any...')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra' ]))
                ;
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::ARCANA ], $activityLog);

                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, false);
            }
        }
        else
        {
            if($brawlRoll >= 15)
            {
                if($pet->isInGuild(GuildEnum::THE_UNIVERSE_FORGETS))
                {
                    $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and found a fire spirit burning a still-living tree! ' . $pet->getName() . ' immediately put the fire out so that it could no longer harm anyone; grateful, the tree offered them ' . $loot . '.')
                        ->setIcon('guilds/the-universe-forgets')
                        ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra', 'Fighting', 'Guild' ]))
                    ;
                    $pet->getGuildMembership()->increaseReputation();
                    $exp = 2;
                }
                else
                {
                    $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and found a fire spirit burning a still-living tree! ' . $pet->getName() . ' was able to put the fire out; grateful, the tree offered them ' . $loot . '.')
                        ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra', 'Fighting' ]))
                    ;
                }

                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' was given this by a tree in the Burnt Forest, as thanks for saving it!', $activityLog);
                $pet->increaseLove($this->rng->rngNextInt(2, 4));
                $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::BRAWL ], $activityLog);

                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and found a fire spirit burning a still-living tree! ' . $pet->getName() . ' tried to put out the fire, but by the time they chased it off, the tree was already dead...')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra', 'Fighting' ]))
                ;
                $pet->increaseEsteem(-$this->rng->rngNextInt(2, 4));
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);

                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, false);
            }
        }

        return $activityLog;
    }

    private function findTearInTheTapestry(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal());

        if($roll >= 13)
        {
            if($pet->isInGuild(GuildEnum::TAPESTRIES))
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and found a tear in the fabric of reality! They were able to stitch it back together, and got some Quintessence!')
                    ->setIcon('guild/tapestries')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra', 'Guild' ]))
                ;
                $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' got this in the Burnt Forest while repairing a tear in the fabric of reality.', $activityLog);
                $pet->getGuildMembership()->increaseReputation();
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and found a tear in the fabric of reality! It was a little intimidating, but they managed to harvest some Quintessence!')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra' ]))
                ;
                $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' got this in the Burnt Forest from tear in the fabric of reality.', $activityLog);
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::ARCANA ], $activityLog);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);
        }
        else
        {
            if($pet->isInGuild(GuildEnum::TAPESTRIES))
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and found a tear in the fabric of reality! They tried to repair it, but were worried about getting unraveled, themselves!')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra', 'Guild' ]))
                ;
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and found a tear in the fabric of reality! They thought about harvesting some Quintessence, but were worried about getting unraveled, themselves!')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::ARCANA ], $activityLog);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::UMBRA, false);
        }

        return $activityLog;
    }

    private function breakToolBonus(Pet $pet): PetActivityLog
    {
        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to visit the Burnt Forest, but a crack of light appeared on ' . $pet->getTool()->getItem()->getName() . ', and it lost its "' . $pet->getTool()->getEnchantment()->getName() . '" bonus! Before the crack faded, a strange piece of paper slipped out of it...')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra' ]))
        ;

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::ARCANA ], $activityLog);

        $this->inventoryService->petCollectsItem('Fairy\'s Scroll', $pet, 'This slipped out of a crack of light in ' . $pet->getName() . '\'s ' . $pet->getTool()->getItem()->getName() . '...', $activityLog);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::OTHER, true);

        $pet->getTool()->setEnchantment(null);

        return $activityLog;
    }

    private function findScalySquirmingMass(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getBrawl()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal());

        $resistsFire = $petWithSkills->getHasProtectionFromHeat()->getTotal() > 0;

        if($roll >= 16)
        {
            $loot = $this->rng->rngNextFromArray([ 'Dark Scales', 'Quinacridone Magenta Dye', 'Quintessence' ]);

            if($resistsFire || $this->rng->rngNextInt(1, 20 + $petWithSkills->getStamina()->getTotal()) >= 15)
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and encountered a mass of flaming, scaly tentacles! They beat the tentacles back, and got a Tentacle, and ' . $loot . '!')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra', 'Fighting', 'Heatstroke' ]))
                ;
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and encountered a mass of flaming, scaly tentacles! They beat the tentacles back, and got a Tentacle, and ' . $loot . ', but not without getting burned in the fight!')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra', 'Fighting', 'Heatstroke' ]))
                ;
                $pet->increaseSafety(-$this->rng->rngNextInt(2, 4));
            }

            $pet->increaseEsteem($this->rng->rngNextInt(2, 4));

            $this->inventoryService->petCollectsItem('Tentacle', $pet, $pet->getName() . ' received this by defeating a mass of flaming, scaly tentacles in the Burnt Forest!', $activityLog);
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' received this by defeating a mass of flaming, scaly tentacles in the Burnt Forest!', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ], $activityLog);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);
        }
        else
        {
            if($resistsFire || $this->rng->rngNextInt(1, 20 + $petWithSkills->getStamina()->getTotal()) >= 20)
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and encountered a mass of flaming, scaly tentacles! They tried to fight, but were forced to flee...')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra', 'Fighting', 'Heatstroke' ]))
                ;
                $pet->increaseSafety(-$this->rng->rngNextInt(2, 4));
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% visited the Burnt Forest, and encountered a mass of flaming, scaly tentacles! They tried to fight, but got burned by one of the tentacles, and was forced to flee...')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra', 'Fighting', 'Heatstroke' ]))
                ;
                $pet->increaseSafety(-$this->rng->rngNextInt(4, 8));
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, false);
        }

        return $activityLog;
    }
}
