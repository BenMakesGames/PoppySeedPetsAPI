<?php

namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\GuildEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ActivityHelpers;
use App\Model\ComputedPetSkills;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\SpiceRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

class LeonidsService
{
    private IRandom $rng;
    private ResponseService $responseService;
    private InventoryService $inventoryService;
    private PetExperienceService $petExperienceService;
    private EntityManagerInterface $em;

    public function __construct(
        IRandom $rng, ResponseService $responseService, InventoryService $inventoryService,
        PetExperienceService $petExperienceService, EntityManagerInterface $em
    )
    {
        $this->rng = $rng;
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->em = $em;
    }

    public function adventure(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $adventure = $this->rng->rngNextInt(1, 3);

        if($adventure === 1)
            $activityLog = $this->encounterWerecreaturePack($petWithSkills);
        else if($adventure === 2)
            $activityLog = $this->encounterRaccoonSpiritScavenger($petWithSkills);
        else
            $activityLog = $this->encounterFairies($petWithSkills);

        $activityLog
            ->addInterestingness(PetActivityLogInterestingnessEnum::HOLIDAY_OR_SPECIAL_EVENT)
            ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Umbra', 'Special Event', 'Leonids' ]))
        ;

        return $activityLog;
    }

    private function encounterWerecreaturePack(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($petWithSkills->getPet()->hasStatusEffect(StatusEffectEnum::WEREFORM))
        {
            $starrySpice = SpiceRepository::findOneByName($this->em, 'Starry');

            $activityLog = $this->responseService->createActivityLog($pet, $this->getActivityLogPrefix($pet) . ' There, they saw a group of werecreatures playing in the Stardust! ' . ActivityHelpers::PetName($pet) . ' joined them, rolling in the dust, playing tug-of-war with Pobo bones, and howling at the stars!', '');

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::BRAWL ], $activityLog);

            $this->inventoryService->petCollectsItem('Stardust', $pet, $pet->getName() . ' got this in their fur while playing with werecreatures in the Umbra!', $activityLog);

            $loot = $this->rng->rngNextFromArray([ 'Fluff', 'Talon', 'Stereotypical Bone', 'Stereotypical Bone' ]);

            $this->inventoryService->petCollectsEnhancedItem($loot, null, $starrySpice, $pet, $pet->getName() . ' got this while playing with werecreatures in the Umbra!', $activityLog);
        }
        else if($petWithSkills->getPet()->hasStatusEffect(StatusEffectEnum::BITTEN_BY_A_WERECREATURE))
        {
            if($pet->getTool() && $pet->getTool()->getItem()->getTreasure() && $pet->getTool()->getItem()->getTreasure()->getSilver() > 0)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $this->getActivityLogPrefix($pet) . ' There, they saw a group of werecreatures playing in the Stardust! The werecreatures gave ' . ActivityHelpers::PetName($pet) . ' strange looks (perhaps it\'s the silvery ' . $pet->getTool()->getFullItemName() . '?) but kept their distance while ' . ActivityHelpers::PetName($pet) . ' gathered some Stardust...', '');

                $this->inventoryService->petCollectsItem('Stardust', $pet, $pet->getName() . ' gathered this from fallen Leonids, while receiving strange looks from some werecreatures.', $activityLog);
            }
            else if($pet->getHat() && $pet->getHat()->getItem()->getTreasure() && $pet->getHat()->getItem()->getTreasure()->getSilver() > 0)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $this->getActivityLogPrefix($pet) . ' There, they saw a group of werecreatures playing in the Stardust! The werecreatures gave ' . ActivityHelpers::PetName($pet) . ' strange looks (perhaps it\'s the silvery ' . $pet->getHat()->getFullItemName() . '?) but kept their distance while ' . ActivityHelpers::PetName($pet) . ' gathered some Stardust...', '');

                $this->inventoryService->petCollectsItem('Stardust', $pet, $pet->getName() . ' gathered this from fallen Leonids, while receiving strange looks from some werecreatures.', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $this->getActivityLogPrefix($pet) . ' There, they saw a group of werecreatures playing in the Stardust! One of them approached ' . ActivityHelpers::PetName($pet) . ', and pushed a pile of Stardust forward with its paw, then left.', '');

                $this->inventoryService->petCollectsItem('Stardust', $pet, $pet->getName() . ' was given this by some werecreatures in the Umbra!', $activityLog);
            }

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
        }
        else
        {
            $stealthCheck = $this->rng->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStealth()->getTotal());

            if($stealthCheck <= 2)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $this->getActivityLogPrefix($pet) . ' There, they saw a group of werecreatures playing in the Stardust! ' . ActivityHelpers::PetName($pet) . ' tried to gather some Stardust in secret, but were spotted, and forced to flee!', '');

                if($pet->getTool() && $pet->getTool()->getItem()->getTreasure() && $pet->getTool()->getItem()->getTreasure()->getSilver() > 0)
                {
                    $activityLog->setEntry($activityLog->getEntry() . ' (Even a silvery ' . $pet->getTool()->getFullItemName() . ' isn\'t going to keep away a whole pack of werecreatures!)');
                }
                else if($pet->getHat() && $pet->getHat()->getItem()->getTreasure() && $pet->getHat()->getItem()->getTreasure()->getSilver() > 0)
                {
                    $activityLog->setEntry($activityLog->getEntry() . ' (Even a silvery ' . $pet->getHat()->getFullItemName() . ' isn\'t going to keep away a whole pack of werecreatures!)');
                }

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::STEALTH ], $activityLog);

                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::UMBRA, false);
            }
            else if($stealthCheck >= 12)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $this->getActivityLogPrefix($pet) . ' There, they saw a group of werecreatures playing in the Stardust! ' . ActivityHelpers::PetName($pet) . ' kept to the shadows, and gathered some Stardust in secret...', '');

                $this->inventoryService->petCollectsItem('Stardust', $pet, $pet->getName() . ' gathered this from fallen Leonids in the Umbra!', $activityLog);
                $this->inventoryService->petCollectsItem('Stardust', $pet, $pet->getName() . ' gathered this from fallen Leonids in the Umbra!', $activityLog);

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA, PetSkillEnum::STEALTH ], $activityLog);

                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $this->getActivityLogPrefix($pet) . ' There, they saw a group of werecreatures playing in the Stardust! ' . ActivityHelpers::PetName($pet) . ' quickly scooped some up, and ran away before they could get spotted!', '');

                $this->inventoryService->petCollectsItem('Stardust', $pet, $pet->getName() . ' gathered this from fallen Leonids in the Umbra!', $activityLog);

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::STEALTH ], $activityLog);

                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);
            }
        }

        return $activityLog
            ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Werecreature' ]))
        ;
    }

    private function encounterRaccoonSpiritScavenger(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($pet->isInGuild(GuildEnum::LIGHT_AND_SHADOW))
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);

            $activityLog = $this->responseService->createActivityLog($pet, $this->getActivityLogPrefix($pet) . ' There, they encountered a large raccoon spirit, gathering Stardust. It snarled at ' . ActivityHelpers::PetName($pet) . ', but they calmed it down, and helped it gather some Stardust (it\'s the Light and Shadow way)! In addition to getting some Stardust of their own, the spirit gave ' . ActivityHelpers::PetName($pet) . ' some Quintesence as thanks!', '')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Guild' ]))
            ;

            $this->inventoryService->petCollectsItem('Stardust', $pet, $pet->getName() . ' gathered this with a large raccoon spirit they met in the Umbra!', $activityLog);
            $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' received this from a large raccoon spirit they met in the Umbra while gathering Stardust!', $activityLog);

            $pet->getGuildMembership()->increaseReputation();

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
        }
        else
        {
            $combatRoll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl()->getTotal());

            if($combatRoll >= 15)
            {
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);

                $activityLog = $this->responseService->createActivityLog($pet, $this->getActivityLogPrefix($pet) . ' There, they encountered a large raccoon spirit, gathering Stardust. It snarled at ' . ActivityHelpers::PetName($pet) . ', but they calmed it down, and helped it gather some Stardust (it\'s the Light and Shadow way)! In addition to getting some Stardust of their own, the spirit gave ' . ActivityHelpers::PetName($pet) . ' some Quintesence as thanks!', '')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Fighting' ]))
                ;

                $loot = $this->rng->rngNextFromArray([ 'Fluff', 'Talon', 'Quintessence' ]);
                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' got this by defeating an angry raccoon spirit they encountered in the Umbra while gathering Stardust! It snarled at ' . $pet->getName() . ', and attacked, but ' . $pet->getName() . ' overpowered the spirit, and drove it away!', $activityLog);
                $this->inventoryService->petCollectsItem('Stardust', $pet, $pet->getName() . ' gathered this from fallen Leonids in the Umbra, after defeating a large raccoon spirit!', $activityLog);

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA, PetSkillEnum::BRAWL ], $activityLog);
            }
            else
            {
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, false);

                $activityLog = $this->responseService->createActivityLog($pet, $this->getActivityLogPrefix($pet) . ' There, they encountered a large raccoon spirit, gathering Stardust. It snarled at ' . ActivityHelpers::PetName($pet) . ', and attacked; after a long fight in the Stardust, ' . ActivityHelpers::PetName($pet) . ' was forced to retreat!', '');

                $this->inventoryService->petCollectsItem('Stardust', $pet, $pet->getName() . ' got this all over themselves during a fight with a large raccoon spirit in the Umbra!', $activityLog);

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::BRAWL ], $activityLog);
            }
        }

        return $activityLog;
    }

    private function encounterFairies(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $gatheringRoll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());

        if($gatheringRoll >= 10)
        {
            if($gatheringRoll >= 20)
            {
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::UMBRA, true);

                $activityLog = $this->responseService->createActivityLog($pet, $this->getActivityLogPrefix($pet) . ' There, they ran into some fairies. They helped the fairies gather a ton of Stardust, for which they received lunch and Quintessence as way of thanks!', '');

                $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' received this from some fairies after helping them gather tons of Stardust in the Umbra!', $activityLog);

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            }
            else
            {
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);

                $activityLog = $this->responseService->createActivityLog($pet, $this->getActivityLogPrefix($pet) . ' There, they ran into some fairies. After working at it for a while, they all took a break, and the fairies shared some of their food!', '');

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            }

            $spice = SpiceRepository::findOneByName($this->em, $this->rng->rngNextFromArray([
                'Rain-scented',
                'Juniper',
                'with Rosemary',
                'with Toad Jelly',
            ]));

            $foodItem = $this->rng->rngNextFromArray([
                'Pumpkin Bread',
                'Slice of Naner Bread',
                'World\'s Best Sugar Cookie',
                'Shortbread Cookies',
                'Cheese',
            ]);

            $this->inventoryService->petCollectsEnhancedItem($foodItem, null, $spice, $pet, $pet->getName() . ' received this from some fairies after helping them gather Stardust in the Umbra!', $activityLog);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);

            $activityLog = $this->responseService->createActivityLog($pet, $this->getActivityLogPrefix($pet) . ' There, they ran into some fairies. They all hung out and kept each other company while gathering Stardust for a while...', '');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
        }

        $this->inventoryService->petCollectsItem('Stardust', $pet, $pet->getName() . ' gathered this with some fairies they met in the Umbra!', $activityLog);

        return $activityLog
            ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Fae-kind' ]))
        ;
    }

    private function getActivityLogPrefix(Pet $pet)
    {
        return '%pet:' . $pet->getId() . '.name% went into the Umbra, and followed the Leonids to where they were falling!';
    }
}