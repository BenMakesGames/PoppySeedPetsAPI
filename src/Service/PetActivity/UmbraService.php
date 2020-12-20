<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\GuildEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\SpiritCompanionStarEnum;
use App\Functions\ArrayFunctions;
use App\Functions\GrammarFunctions;
use App\Functions\NumberFunctions;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\EnchantmentRepository;
use App\Repository\ItemRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\InventoryModifierService;
use App\Service\TransactionService;

class UmbraService
{
    private $responseService;
    private $inventoryService;
    private $petExperienceService;
    private $transactionService;
    private $enchantmentRepository;
    private $itemRepository;
    private $toolBonusService;
    private $strangeUmbralEncounters;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetExperienceService $petExperienceService,
        TransactionService $transactionService, GuildService $guildService, EnchantmentRepository $enchantmentRepository,
        ItemRepository $itemRepository, InventoryModifierService $toolBonusService, StrangeUmbralEncounters $strangeUmbralEncounters
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->transactionService = $transactionService;
        $this->guildService = $guildService;
        $this->enchantmentRepository = $enchantmentRepository;
        $this->itemRepository = $itemRepository;
        $this->toolBonusService = $toolBonusService;
        $this->strangeUmbralEncounters = $strangeUmbralEncounters;
    }

    public function adventure(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $skill = 10 + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getUmbra()->getTotal(); // psychedelics bonus is built into getUmbra()

        $skill = NumberFunctions::constrain($skill, 1, 22);

        $roll = mt_rand(1, $skill);

        $activityLog = null;
        $changes = new PetChanges($pet);

        switch($roll)
        {
            case 1:
            case 2:
            case 3:
                $activityLog = $this->foundNothing($pet, $roll);
                break;
            case 4:
            case 5:
            case 6:
                $activityLog = $this->foundScragglyBush($petWithSkills);
                break;
            case 7:
            case 8:
                $activityLog = $this->helpedLostSoul($petWithSkills);
                break;
            case 9:
                $activityLog = $this->found2Moneys($petWithSkills);
                break;
            case 10:
            case 11:
                $activityLog = $this->fightEvilSpirit($petWithSkills);
                break;

            case 12:
                if($pet->getOwner()->getFireplace() && $pet->getOwner()->getFireplace()->getWhelpName())
                    $activityLog = $this->visitLibraryOfFire($petWithSkills);
                else
                    $activityLog = $this->foundNothing($pet, $roll);
                break;

            case 13:
                $activityLog = $this->found2Moneys($petWithSkills);
                break;
            case 14:
            case 15:
                $activityLog = $this->fishingAtRiver($petWithSkills);
                break;
            case 16:
                $activityLog = $this->strangeUmbralEncounters->adventure($petWithSkills);
                break;
            case 17:
                $activityLog = $this->gatheringAtTheNoetala($petWithSkills);
                break;
            case 18:
                $activityLog = $this->foundVampireCastle($petWithSkills);
                break;
            case 19:
            case 20:
                $activityLog = $this->frozenQuag($petWithSkills);
                break;
            case 21:
                $activityLog = $this->fightAbandondero($petWithSkills);
                break;
            case 22:
                $activityLog = $this->foundCursedGarden($petWithSkills);
                break;
        }

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));
    }

    private function foundNothing(Pet $pet, int $roll): PetActivityLog
    {
        $exp = ceil($roll / 10);

        $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::UMBRA ]);

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::UMBRA, false);

        return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% crossed into the Umbra, but the Storm was too harsh; ' . $pet->getName() . ' retreated before finding anything.', 'icons/activity-logs/confused');
    }

    private function visitLibraryOfFire(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if(mt_rand(1, 10) === 1)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::UMBRA, 'true');

            // visit the library's arboretum

            if(mt_rand(1, 5) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Library of Fire\'s arboretum, and found the brick with your name on it!', '');

                $pet
                    ->increaseEsteem(mt_rand(3, 6))
                    ->increaseSafety(mt_rand(2, 4))
                ;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Library of Fire\'s arboretum.', '');

                $pet->increaseSafety(mt_rand(2, 4));
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }
        else if(mt_rand(1, 3) === 1 && $pet->getGuildMembership() === null)
        {
            $activityLog = $this->guildService->joinGuildUmbra($pet);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::UMBRA, 'true');

            // visit a floor of the library and read some books

            $floor = mt_rand(8, 414);

            if($floor === 29)
                $floor = 28;
            else if($floor === 30)
                $floor = 31;

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the ' . GrammarFunctions::ordinalize($floor) . ' floor of the Library of Fire, and read a random book...', '');

            $this->petExperienceService->gainExp($pet, mt_rand(1, 2), PetSkillEnum::getValues());
            $pet->increaseSafety(mt_rand(2, 4));
        }

        $activityLog->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY);

        return $activityLog;
    }

    private function foundScragglyBush(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $skill = mt_rand(1, 20 + $petWithSkills->getGatheringBonus()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($skill >= 11)
        {
            $reward = mt_rand(1, 3);

            if($reward === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'In the Umbra, ' . '%pet:' . $pet->getId() . '.name% found an outcropping of rocks where the full force of the Storm could not reach. Some Grandparoot was growing there; ' . $pet->getName() . ' took one.', 'items/veggie/grandparoot');
                $this->inventoryService->petCollectsItem('Grandparoot', $pet, $pet->getName() . ' pulled this up from between some rocks in the Umbra.', $activityLog);
            }
            else if($reward === 2)
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'In the Umbra, ' . '%pet:' . $pet->getId() . '.name% found an outcropping of rocks where the full force of the Storm could not reach. A dry bush once grew there; ' . $pet->getName() . ' took a Crooked Stick from its remains.', 'items/plant/stick-crooked');
                $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' took this from the remains of a dead bush in the Umbra.', $activityLog);
            }
            else // if($reward === 3)
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'In the Umbra, ' . '%pet:' . $pet->getId() . '.name% found an outcropping of rocks where the full force of the Storm could not reach. A small Blackberry bush was growing there; ' . $pet->getName() . ' took a few berries.', 'items/fruit/blackberries');
                $this->inventoryService->petCollectsItem('Blackberries', $pet, $pet->getName() . ' harvested these exceptionally-dark Blackberries from a rock-sheltered berry bush in the Umbra.', $activityLog);
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::UMBRA, 'true');

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::UMBRA, false);
            return $this->responseService->createActivityLog($pet, 'In the Umbra, ' . '%pet:' . $pet->getId() . '.name% found an outcropping of rocks where the full force of the Storm could not reach. Some weeds were growing there, but nothing of value.', 'icons/activity-logs/confused');
        }
    }

    private function helpedLostSoul(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $hasEideticMemory = $pet->hasMerit(MeritEnum::EIDETIC_MEMORY);
        $hasRelevantSpirit = $pet->getSpiritCompanion() !== null && $pet->getSpiritCompanion()->getStar() === SpiritCompanionStarEnum::ALTAIR;

        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getUmbra()->getTotal());

        $rewards = [
            'Quintessence' => 'some',
            'Music Note' => 'a',
            'Ginger' => 'some',
            'Oil' => 'some',
            'Silica Grounds' => 'some'
        ];

        if(mt_rand(1, 10) === 1)
            $rewards['Broccolinomicon'] = 'a copy of the';

        $reward = array_rand($rewards);

        if($hasEideticMemory || $hasRelevantSpirit)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::UMBRA, true);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            $messageDetail = $hasEideticMemory
                ? $pet->getName() . ' had already memorized the lay of the land, and pointed the way'
                : $pet->getName() . ' and ' . $pet->getSpiritCompanion()->getName() . ' were able to point the way'
            ;

            if(mt_rand(1, 2) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% met a friendly spirit lost in the Umbra. ' . $messageDetail . '; the spirit was very thankful, and insisted that ' . $pet->getName() . ' take ' . $rewards[$reward] . ' ' . $reward . '.', '');
                $this->inventoryService->petCollectsItem($reward, $pet, $pet->getName() . ' received this from a friendly spirit as thanks for helping it navigate the Umbra.', $activityLog);
                $pet->increaseEsteem(1);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% met a friendly spirit lost in the Umbra. ' . $messageDetail . '; the spirit was very thankful, and wished ' . $pet->getName() . ' well.', '');
                $pet->increaseEsteem(4);
            }

            $activityLog->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT);

            return $activityLog;
        }
        else if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::UMBRA, true);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% met a friendly spirit lost in the Umbra. ' . $pet->getName() . ' was able to point the way; the spirit was very thankful, and insisted that ' . $pet->getName() . ' take ' . $rewards[$reward] . ' ' . $reward . '.', '');
                $this->inventoryService->petCollectsItem($reward, $pet, $pet->getName() . ' received this from a friendly spirit as thanks for helping it navigate the Umbra.', $activityLog);
                $pet->increaseEsteem(1);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% met a friendly spirit lost in the Umbra. ' . $pet->getName() . ' was able to point the way; the spirit was very thankful, and wished ' . $pet->getName() . ' well.', '');
                $pet->increaseEsteem(4);
            }

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::UMBRA, false);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            if($hasRelevantSpirit)
            {
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% met a friendly spirit lost in the Umbra. It asked for directions, but ' . $pet->getName() . ' and ' . $pet->getSpiritCompanion()->getName() . ' didn\'t know how to help.', 'icons/activity-logs/confused')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;
            }
            else
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% met a friendly spirit lost in the Umbra. It asked for directions, but ' . $pet->getName() . ' didn\'t know how to help.', 'icons/activity-logs/confused');
        }
    }

    private function found2Moneys(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::UMBRA, false);

        if($pet->hasMerit(MeritEnum::LUCKY) && mt_rand(1, 80) === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% walked along a dark river for a while. On its shore, ' . $pet->getName() . ' spotted a Little Strongbox! Lucky~!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
            ;

            $this->inventoryService->petCollectsItem('Little Strongbox', $pet, $pet->getName() . ' found this on the shores of a dark river in the Umbra.', $activityLog);

            return $activityLog;
        }

        if(mt_rand(1, 100) === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% walked along a dark river for a while. On its shore, ' . $pet->getName() . ' spotted a Little Strongbox, and took it!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ;

            $this->inventoryService->petCollectsItem('Little Strongbox', $pet, $pet->getName() . ' found this on the shores of a dark river in the Umbra.', $activityLog);

            return $activityLog;
        }

        if($pet->hasMerit(MeritEnum::LUCKY))
            $die = ArrayFunctions::pick_one([ 'Glowing Four-sided Die', 'Glowing Six-sided Die', 'Glowing Eight-sided Die' ]);
        else
            $die = ArrayFunctions::pick_one([ 'Glowing Four-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Eight-sided Die' ]);

        if($pet->hasMerit(MeritEnum::LUCKY) && mt_rand(1, 50) === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% walked along a dark river for a while. On its shore, ' . $pet->getName() . ' spotted a ' . $die . '! Lucky~!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
            ;

            $this->inventoryService->petCollectsItem($die, $pet, $pet->getName() . ' found this on the shores of a dark river in the Umbra.', $activityLog);

            return $activityLog;
        }

        if(mt_rand(1, 80) === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% walked along a dark river for a while. On its shore, ' . $pet->getName() . ' spotted a ' . $die . ', and took it!', '');

            $this->inventoryService->petCollectsItem($die, $pet, $pet->getName() . ' found this on the shores of a dark river in the Umbra.', $activityLog);

            return $activityLog;
        }

        $this->transactionService->getMoney($pet->getOwner(), 2, $pet->getName() . ' found this on the shores of a dark river in the Umbra.');

        return $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% walked along a dark river for a while. On its shore, ' . $pet->getName() . ' spotted 2~~m~~. No one else was around, so...', 'icons/activity-logs/moneys');
    }

    private function fightEvilSpirit(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $prizes = [
            'Silica Grounds', 'Quintessence', 'Aging Powder', 'Fluff'
        ];

        if(mt_rand(1, 100) === 1)
            $prize = 'Forgetting Scroll';
        else if(mt_rand(1, 50) === 1)
            $prize = 'Spirit Polymorph Potion Recipe';
        else if(mt_rand(1, 100) === 1)
            $prize = 'Blackonite';
        else if(mt_rand(1, 50) === 1)
            $prize = 'Charcoal';
        else
            $prize = ArrayFunctions::pick_one($prizes);

        if($pet->isInGuild(GuildEnum::LIGHT_AND_SHADOW))
        {
            $skill = 20 + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal();

            $roll = mt_rand(1, $skill);
            $success = $roll >= 12;

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::UMBRA, $success);

            if($success)
            {
                $pet->getGuildMembership()->increaseReputation();
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);

                $prizeItem = $this->itemRepository->findOneByName($prize);

                $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% encountered a super gross-looking mummy dragging its long arms through the Umbral sand. It screeched and swung wildly; but ' . $pet->getName() . ' endured its attacks long enough to calm it down! It eventually wandered away, dropping ' . $prizeItem->getNameWithArticle() . ' as it went...', 'guilds/light-and-shadow')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                ;

                $this->inventoryService->petCollectsItem($prize, $pet, $pet->getName() . ' defeated a gross-looking mummy with crazy-long arms, and took this.', $activityLog);

                return $activityLog;
            }
            else
            {
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
                return $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% encountered a super gross-looking mummy dragging its long arms through the Umbral sand. It screeched and swung wildly. ' . $pet->getName() . ' tried to endure its attacks long enough to calm it down, but was eventually forced to retreat!', 'guilds/light-and-shadow');
            }
        }

        $skill = 20 + max($petWithSkills->getBrawl()->getTotal(), $petWithSkills->getUmbra()->getTotal()) + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal();

        $roll = mt_rand(1, $skill);
        $success = $roll >= 12;

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, $roll >= $success);

        $isRanged = $pet->getTool() && $pet->getTool()->getItem()->getTool()->getIsRanged() && $pet->getTool()->getItem()->getTool()->getBrawl() > 0;

        $defeated = $isRanged ? 'shot it down' : 'beat it back';

        if($success)
        {
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);

            if($pet->isInGuild(GuildEnum::THE_UNIVERSE_FORGETS))
            {
                $pet->getGuildMembership()->increaseReputation();
                $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% encountered a super gross-looking mummy dragging its long arms through the Umbral sand. It screeched and swung wildly; but ' . $pet->getName() . ' ' . $defeated . ', and claimed its ' . $prize . '!', 'guilds/the-universe-forgets')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                ;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% encountered a super gross-looking mummy dragging its long arms through the Umbral sand. It screeched and swung wildly; but ' . $pet->getName() . ' ' . $defeated . ', and claimed its ' . $prize . '!', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                ;
            }

            $this->inventoryService->petCollectsItem($prize, $pet, $pet->getName() . ' defeated a gross-looking mummy with crazy-long arms, and took this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% encountered a super gross-looking mummy dragging its long arms through the Umbral sand. It screeched and swung wildly; ' . $pet->getName() . ' made a hasty retreat.', '');
        }
    }

    private function fishingAtRiver(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $fishingSkill = mt_rand(1, 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() + $petWithSkills->getUmbra()->getTotal());

        $roll = mt_rand(1, $fishingSkill);

        $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::FISH, $roll >= 13);

        if($roll >= 13)
        {
            $prizes = [ 'Fish' ];

            if(mt_rand(1, 2) == 1)
            {
                $prizes[] = 'Dark Scales';

                if(mt_rand(1, 10) === 1)
                    $prizes[] = 'Secret Seashell';
                else
                    $prizes[] = 'Seaweed';

                $fish = 'some horrible, writhing thing';
            }
            else
            {
                $prizes[] = 'Quintessence';

                if(mt_rand(1, 4) === 1)
                    $prizes[] = 'Music Note';
                else
                    $prizes[] = 'Creamy Milk';

                $fish = 'an oddly-beautiful, squirming mass';
            }

            shuffle($prizes);

            if($roll >= 18)
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% decided to fish in a dark river. They caught ' . $fish . ', and harvested its ' . $prizes[0] . ' and ' . $prizes[1] . '.', '');
                $this->inventoryService->petCollectsItem($prizes[0], $pet, $pet->getName() . ' got this from fishing in the Umbra.', $activityLog);
                $this->inventoryService->petCollectsItem($prizes[1], $pet, $pet->getName() . ' got this from fishing in the Umbra.', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% decided to fish in a dark river. They caught ' . $fish . ', and harvested its ' . $prizes[0] . '.', '');
                $this->inventoryService->petCollectsItem($prizes[0], $pet, $pet->getName() . ' got this from fishing in the Umbra.', $activityLog);
            }

            $activityLog->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% decided to fish in a dark river. Plenty of strange things swam by, but ' . $pet->getName() . ' didn\'t manage to catch any of them.', '');
        }
    }

    private function gatheringAtTheNoetala(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $loot = [ 'Noetala Egg' ];

        if(mt_rand(1, 20 + $petWithSkills->getStealth()->getTotal() + $petWithSkills->getDexterity()->getTotal()) < 15)
        {
            $pet->increaseFood(-1);

            if(mt_rand(1, 20) + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl()->getTotal() >= 20)
            {
                $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::HUNT, true);

                if(mt_rand(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 25)
                    $loot[] = 'Quintessence';

                if(mt_rand(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 15)
                    $loot[] = 'Fluff';

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::STEALTH, PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);
                $pet->increaseEsteem(3);
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% fell into a giant cocoon. While trying to find their way out, ' . $pet->getName() . ' was ambushed by one of Noetala\'s guard, but was able to defeat it!', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ;

                $didWhat = 'defeated one of Noetala\'s guard, and took this';
            }
            else
            {
                $loot = [ 'Fluff' ];

                $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::HUNT, false);

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);
                $pet->increaseEsteem(-3);
                $pet->increaseSafety(-mt_rand(4, 8));
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% fell into a giant cocoon. While trying to find their way out, ' . $pet->getName() . ' was ambushed by one of Noetala\'s guard, and was wounded and covered in Fluff before being able to escape!', '');
                $didWhat = 'was attacked by one of Noetala\'s guard, and covered in this';
            }
        }
        else
        {
            $didWhat = 'stole this from a giant cocoon';

            if(mt_rand(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 25)
                $loot[] = 'Quintessence';

            if(mt_rand(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 15)
                $loot[] = 'Fluff';

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% stumbled upon Noetala\'s giant cocoon. They snuck around inside for a bit, and made off with ' . ArrayFunctions::list_nice($loot) . '.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 100) === 1)
                $activityLog->setEntry($activityLog->getEntry() . ' ("Snuck"? "Sneaked"? I dunno. One of thems.)');

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::UMBRA, true);
        }

        foreach($loot as $itemName)
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' ' . $didWhat . '.', $activityLog);

        return $activityLog;
    }

    private function foundVampireCastle(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();

        $umbraCheck = mt_rand(1, 10 + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getPerception()->getTotal());

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::UMBRA, true);

        if($umbraCheck >= 12)
        {
            // realize it's vampires; chance to steal
            $stealthCheck = mt_rand(1, 20 + $petWithSkills->getStealth()->getTotal() + $petWithSkills->getDexterity()->getTotal());

            if($stealthCheck >= 16)
            {
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::STEALTH, PetSkillEnum::UMBRA ]);
                $loot = ArrayFunctions::pick_one([ 'Blood Wine', 'Linens and Things' ]);

                $pet->increaseEsteem(2);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% stumbled upon a castle that was obviously home to vampires. They snuck around inside for a while, and made off with some ' . $loot . '.', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ;

                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' stole this from a vampire castle.', $activityLog);
            }
            else
            {
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::UMBRA ]);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% stumbled upon a castle that was obviously home to vampires. They snuck around inside for a while, but couldn\'t find a good opportunity to steal anything.', 'icons/activity-logs/confused');
            }
        }
        else if($pet->getTool() && $pet->getTool()->isGrayscaling())
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $loot = ArrayFunctions::pick_one([ 'Blood Wine', 'Linens and Things' ]);

            $pet->increaseEsteem(2);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% stumbled upon a castle that was apparently home to vampires! Fortunately, the vampires mistook ' . $pet->getName() . '\'s monochromatic appearance as vampirism, and welcomed them as kin. ' . $pet->getName() . ' stole a few items while none of the vampires were looking, and fled the castle as soon as they could!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ;

            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' stole this from a vampire castle.', $activityLog);
        }
        else
        {
            // don't realize; get in a fight
            $brawlCheck = mt_rand(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl()->getTotal());

            if($brawlCheck >= 20)
            {
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);
                $loot = ArrayFunctions::pick_one([ 'White Cloth', 'Talon', 'Quintessence' ]);

                $pet
                    ->increaseEsteem(2)
                    ->increaseSafety(2)
                ;

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% stumbled upon a castle. While exploring it, a vampire attacked them! ' . $pet->getName() . ' was able to drive them away, however, and even nab ' . $loot . '!', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ;

                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' beat up a vampire and took this.', $activityLog);
            }
            else
            {
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);

                $pet
                    ->increaseEsteem(-2)
                    ->increaseSafety(-2)
                ;

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% stumbled upon a castle. While exploring it, a vampire attacked them! ' . $pet->getName() . ', caught completely by surprise, was forced to flee...', '');
            }
        }

        return $activityLog;
    }

    private function frozenQuag(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($petWithSkills->getCanSeeInTheDark()->getTotal() <= 0)
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $this->petExperienceService->spendTime($pet, mt_rand(30, 45), PetActivityStatEnum::UMBRA, false);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wandered into a deep, dark part of the Umbra, but they didn\'t have a light, so turned back...', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ;
        }

        $pet->increaseFood(-1);

        if(mt_rand(1, 20) + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getPerception()->getTotal() >= 18)
        {
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);

            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::GATHER, true);

            if($pet->getTool() && $pet->getTool()->providesLight())
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wandered into a frozen quag deep in the Umbra. The light of their ' . $this->toolBonusService->getNameWithModifiers($pet->getTool()) . ' caught on a cube of Everice, which ' . $pet->getName() . ' took!', '');
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wandered into a frozen quag deep in the Umbra, and happened to spot a cube of Everice!', '');

            $this->inventoryService->petCollectsItem('Everice', $pet, $pet->getName() . ' found this in a frozen quag in the deep Umbra.', $activityLog);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::GATHER, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% crept through a frozen quag deep in the Umbra, but all they found was a Crooked Stick.', '');
            $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' found this in a frozen quag in the deep Umbra.', $activityLog);
        }

        return $activityLog;
    }

    private function fightAbandondero(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skill = 20 + $petWithSkills->getBrawl()->getTotal() + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal();

        $roll = mt_rand(1, $skill);

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, $roll >= 20);

        $isRanged = $pet->getTool() && $pet->getTool()->getItem()->getTool()->getIsRanged() && $pet->getTool()->getItem()->getTool()->getBrawl() > 0;

        $defeated = $isRanged ? 'drew their ' . $pet->getTool()->getItem()->getName() . ' faster' : 'pounced on it before it could fire';

        if($roll >= 20)
        {
            $prize = ArrayFunctions::pick_one([
                'Alien Tissue', 'Plastic', 'Silver Bar'
            ]);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);
            $pet
                ->increaseEsteem(3)
                ->increaseSafety(3)
            ;
            $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% encountered an Abandondero! It whipped out a laser gun, but ' . $pet->getName() . ' ' . $defeated . ', defeated it, and claimed its ' . $prize . '!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
            ;
            $this->inventoryService->petCollectsItem($prize, $pet, $pet->getName() . ' defeated an Abandondero, and took this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);
            $pet->increaseSafety(-4);
            return $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% encountered an Abandondero! It whipped out a laser gun, and took a few shots at ' . $pet->getName() . ', who made a hasty retreat.', '');
        }
    }

    private function foundCursedGarden(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $loot = [
            'Eggplant', 'Grandparoot'
        ];

        $didWhat = 'harvested this from a Cursed Garden in the Umbra';

        if(mt_rand(1, 20 + $petWithSkills->getStealth()->getTotal() + $petWithSkills->getDexterity()->getTotal()) < 15)
        {
            $pet->increaseFood(-1);

            if(mt_rand(1, 20) + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getBrawl()->getTotal() + $petWithSkills->getUmbra()->getTotal() >= 20)
            {
                $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::UMBRA, true);

                if(mt_rand(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getUmbra()->getTotal()) >= 15)
                    $loot[] = 'Quintessence';

                $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::STEALTH, PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);
                $pet->increaseEsteem(mt_rand(1, 2));
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a Cursed Garden, but while looking for food, was attacked by an Angry Spirit. ' . $pet->getName() . ' defeated the Angry Spirit, and took its ' . ArrayFunctions::list_nice($loot) . '.', '');
                $didWhat = 'defeated an Angry Spirit in the Umbra, and got this';
            }
            else
            {
                $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::UMBRA, false);

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::STEALTH, PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);
                $pet
                    ->increaseEsteem(-2)
                    ->increaseSafety(-4)
                ;
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a Cursed Garden, but, while looking for food, was attacked and routed by an Angry Spirit.', '');

                return $activityLog;
            }
        }
        else
        {
            if(mt_rand(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 25)
                $loot[] = ArrayFunctions::pick_one([ 'Nutmeg', 'Eggplant', 'Silica Grounds' ]);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::STEALTH, PetSkillEnum::UMBRA ]);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a Cursed Garden, and harvested ' . ArrayFunctions::list_nice($loot) . '.', '');

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::UMBRA, true);
        }

        foreach($loot as $itemName)
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' ' . $didWhat . '.', $activityLog);

        return $activityLog;
    }
}
