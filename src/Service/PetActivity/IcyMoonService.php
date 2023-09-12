<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\Spice;
use App\Enum\GuildEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Functions\NumberFunctions;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\SpiceRepository;
use App\Service\FieldGuideService;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;

class IcyMoonService
{
    private ResponseService $responseService;
    private InventoryService $inventoryService;
    private PetExperienceService $petExperienceService;
    private IRandom $squirrel3;
    private FieldGuideService $fieldGuideService;
    private PetActivityLogTagRepository $petActivityLogTagRepository;
    private SpiceRepository $spiceRepository;
    private HouseSimService $houseSimService;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetExperienceService $petExperienceService,
        Squirrel3 $squirrel3, FieldGuideService $fieldGuideService, SpiceRepository $spiceRepository,
        PetActivityLogTagRepository $petActivityLogTagRepository, HouseSimService $houseSimService
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->squirrel3 = $squirrel3;
        $this->fieldGuideService = $fieldGuideService;
        $this->petActivityLogTagRepository = $petActivityLogTagRepository;
        $this->spiceRepository = $spiceRepository;
        $this->houseSimService = $houseSimService;
    }

    public function adventure(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $maxSkill = 5 + ceil(($petWithSkills->getStamina()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getScience()->getTotal()) / 2) - $pet->getAlcohol();

        $maxSkill = NumberFunctions::clamp($maxSkill, 1, 15);

        $roll = $this->squirrel3->rngNextInt(1, $maxSkill);

        $activityLog = null;
        $changes = new PetChanges($pet);

        switch($roll)
        {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
                $activityLog = $this->lostInBlizzard($pet);
                break;
            case 6:
            case 7:
                $activityLog = $this->foundEverice($pet);
                break;
            case 8:
                $activityLog = $this->foundRock($pet);
                break;
            case 9:
            case 10:
                $activityLog = $this->findCryovolcano($petWithSkills);
                break;
            case 11:
            case 12:
            case 13:
                $activityLog = $this->fightBabyCrystallineEntity($petWithSkills);
                break;
            case 14:
            case 15:
                $activityLog = $this->exploreCore($petWithSkills);
                break;
        }

        if($activityLog)
        {
            $activityLog->setChanges($changes->compare($pet));
        }
    }

    private function lostInBlizzard(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, false);

        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to explore an Icy Moon, but a blizzard started up, and they were unable to make any progress.', 'icons/activity-logs/confused')
            ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Icy Moon' ]))
        ;

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ], $activityLog);

        return $activityLog;
    }

    private function getIcySpice(): ?Spice
    {
        return $this->squirrel3->rngNextBool()
            ? null
            : $this->spiceRepository->findOneByName('Freezer-burned')
        ;
    }

    private function foundEverice(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to explore an Icy Moon, but got lost in the endless snowfields. They picked up a chunk of Everice, at least.', '')
            ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Icy Moon', 'Gathering' ]))
        ;

        $this->inventoryService->petCollectsEnhancedItem('Everice', null, $this->getIcySpice(), $pet, $pet->getName() . ' found this in a snowfield on an Icy Moon.', $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ], $activityLog);

        return $activityLog;
    }

    private function foundRock(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored an Icy Moon. They found a field of rocks that poked up out of the moon\'s icy surface, and grabbed a sample to take home.', '')
            ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Icy Moon', 'Gathering' ]))
        ;

        $this->inventoryService->petCollectsEnhancedItem('Rock', null, $this->getIcySpice(), $pet, $pet->getName() . ' found this in a field of rocks on an Icy Moon.', $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ], $activityLog);

        return $activityLog;
    }

    private function findCryovolcano(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal() - $pet->getAlcohol());

        $pet->increaseFood(-1);

        if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

            if($roll >= 25)
            {
                $loot = $this->squirrel3->rngNextFromArray([
                    'Alien Tissue', 'Quinacridone Magenta Dye', 'Quintessence'
                ]);

                $extra = $this->squirrel3->rngNextFromArray([
                    ' (Fascinating!)', ' (Interesting...)', ' (Curious...)'
                ]);
            }
            else
            {
                $loot = $this->squirrel3->rngNextFromArray([
                    'Fish', 'Jellyfish Jelly', 'Seaweed', 'Algae', 'Silica Grounds', 'Tentacle', 'Green Dye',
                ]);

                $extra = '';
            }

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored an Icy Moon, and found a Cryovolcano! It was a bit cold, but they rummaged through the deposits, and found a sample of ' . $loot . '!' . $extra, '')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Icy Moon', 'Gathering' ]))
            ;

            $this->petExperienceService->gainExp($pet, $roll >= 25 ? 3 : 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ], $activityLog);
            $this->inventoryService->petCollectsEnhancedItem($loot, null, $this->getIcySpice(), $pet, $pet->getName() . ' found this amidst cryovolcano deposits on an Icy Moon.', $activityLog);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored an Icy Moon, and found a Cryovolcano! The water was scary-cold, so they watched it from a distance for a while before finally moving on...', '')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Icy Moon', 'Gathering' ]))
            ;

            $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 4));

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ], $activityLog);
        }

        $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Cryovolcano', ActivityHelpers::PetName($pet) . ' found a Cryovolcano on an Icy Moon.');

        return $activityLog;
    }

    private function fightBabyCrystallineEntity(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();

        $pet->increaseFood(-1);

        $roll = $this->squirrel3->rngNextInt(1, 20 + max($petWithSkills->getStrength()->getTotal(), $petWithSkills->getStamina()->getTotal()) + $petWithSkills->getBrawl()->getTotal() - $pet->getAlcohol());

        $roll += $petWithSkills->getHasProtectionFromElectricity()->getTotal() * 2;

        if($roll >= 20)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);

            $pieces = 1;

            if($pet->isInGuild(GuildEnum::HIGH_IMPACT))
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was attacked by a Mini Crystalline Entity while exploring an Icy Moon! As a member of High Impact, they immediately stepped up to the challenge and fought the creature, breaking off a piece of it before it flew away!', '')
                    ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Icy Moon', 'Fighting', 'Guild' ]))
                ;
                $roll += 5; // greater chance to get more stuff
                $pet->getGuildMembership()->increaseReputation();
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was attacked by a Mini Crystalline Entity while exploring an Icy Moon! They fought the creature, breaking off a piece of it before it flew away!', '')
                    ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Icy Moon', 'Fighting' ]))
                ;
            }

            $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 4));

            if($roll >= 30) $pieces++;
            if($roll >= 40) $pieces++;

            for($i = 0; $i < $pieces; $i++)
                $this->inventoryService->petCollectsItem($this->squirrel3->rngNextFromArray([ 'Glass', 'Gypsum', 'Fiberglass' ]), $pet, $pet->getName() . ' got this by defeating a Mini Crystalline Entity on an Icy Moon!', $activityLog);

            $this->petExperienceService->gainExp($pet, 1 + $pieces, [ PetSkillEnum::BRAWL ], $activityLog);
        }
        else if($pet->isInGuild(GuildEnum::HIGH_IMPACT))
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was attacked by a Mini Crystalline Entity while exploring an Icy Moon! As a member of High Impact, they immediately stepped up to the challenge, but the creature was wildly throwing sparks and electricity, and ' . $pet->getName() . ' was forced to retreat...', '')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Icy Moon', 'Fighting', 'Guild' ]))
            ;

            if($pet->hasMerit(MeritEnum::SHOCK_RESISTANT))
            {
                $activityLog
                    ->setEntry($activityLog->getEntry() . ' (Their shock-resistance helped, but dang, that thing is crazy!)')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;
            }
            else
                $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 4));

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ], $activityLog);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was attacked by a Mini Crystalline Entity while exploring an Icy Moon! The creature was wildly throwing sparks and electricity, and ' . $pet->getName() . ' was forced to retreat...', '')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Icy Moon', 'Fighting' ]))
            ;

            if($pet->hasMerit(MeritEnum::SHOCK_RESISTANT))
            {
                $activityLog
                    ->setEntry($activityLog->getEntry() . ' (Their shock-resistance helped, but dang, that thing is crazy!)')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;
            }
            else
                $pet->increaseSafety(-$this->squirrel3->rngNextInt(3, 6));

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ], $activityLog);
        }

        return $activityLog;
    }

    private function exploreCore(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal() - $pet->getAlcohol());

        if($petWithSkills->getCanSeeInTheDark()->getTotal() <= 0)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::GATHER, false);

            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found an entrance to the core of an Icy Moon, but it was too dark to see anything...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Icy Moon', 'Gathering', 'Dark' ]))
            ;
        }

        if($roll >= 20)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

            $loot = [
                $this->squirrel3->rngNextFromArray([ 'Liquid-hot Magma', 'Glass', 'Silica Grounds' ]),
                $this->squirrel3->rngNextFromArray([ 'Gravitational Waves', 'Silica Grounds', 'Rock' ]),
            ];

            if($roll >= 30)
            {
                $period = '! (What was that doing there?!)';
                $loot[] = 'Tiny Rocketship';
            }
            else
                $period = '.';

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found an entrance to the core of an Icy Moon! It was dark inside, but thanks to their ' . ActivityHelpers::SourceOfLight($petWithSkills) . ' they found some ' . ArrayFunctions::list_nice($loot) . $period, '')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Icy Moon', 'Gathering', 'Dark' ]))
            ;

            foreach($loot as $itemName)
                $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' found this in the core of an Icy Moon.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::SCIENCE, PetSkillEnum::NATURE ], $activityLog);

            if($this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getStamina()->getTotal()) < 8)
            {
                if($petWithSkills->getHasProtectionFromHeat()->getTotal() > 0)
                {
                    $activityLog->setEntry($activityLog->getEntry() . ' The core was hot, but their ' . ActivityHelpers::SourceOfHeatProtection($petWithSkills) . ' protected them.')
                        ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                        ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Icy Moon', 'Gathering', 'Dark', 'Heatstroke' ]))
                    ;
                }
                else
                {
                    $pet->increaseFood(-2);
                    $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 4));

                    if($this->squirrel3->rngNextInt(1, 20) === 1)
                        $activityLog->setEntry($activityLog->getEntry() . ' The core was CRAZY hot, and I don\'t mean in a sexy way; %pet:' . $pet->getId() . '.name% got a bit light-headed while crawling through the tunnels.');
                    else
                        $activityLog->setEntry($activityLog->getEntry() . ' The core was CRAZY hot, and %pet:' . $pet->getId() . '.name% got a bit light-headed while while crawling through the tunnels.');

                    $activityLog->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Icy Moon', 'Gathering', 'Dark', 'Heatstroke' ]));
                }
            }

            if($roll >= 30)
            {
                $this->houseSimService->getState()->loseItem('Icy Moon', 1);

                $activityLog->setEntry($activityLog->getEntry() . ' As they were leaving, the caves began to shake violently! %pet:' . $pet->getId() . '.name% managed to start up the Tiny Rocketship, and shot out of the moon as it crumbled into pieces!')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ;

                $this->inventoryService->petCollectsEnhancedItem('Everice', null, $this->getIcySpice(), $pet, 'The remains of a collapsed Icy Moon.', $activityLog);
                $this->inventoryService->petCollectsEnhancedItem('Everice', null, $this->getIcySpice(), $pet, 'The remains of a collapsed Icy Moon.', $activityLog);
                $this->inventoryService->petCollectsItem($this->squirrel3->rngNextFromArray([ 'Rock', 'Silica Grounds' ]), $pet, 'The remains of a collapsed Icy Moon.', $activityLog);
                $this->inventoryService->petCollectsItem($this->squirrel3->rngNextFromArray([ 'Liquid-hot Magma', 'Firestone' ]), $pet, 'The remains of a collapsed Icy Moon.', $activityLog);
            }
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::GATHER, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found the entrance to the core of an Icy Moon! It was dark inside, and even with their ' . ActivityHelpers::SourceOfLight($petWithSkills) . ' they were unable to find anything...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Icy Moon', 'Gathering', 'Dark' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::NATURE ], $activityLog);
        }

        return $activityLog;
    }
}
