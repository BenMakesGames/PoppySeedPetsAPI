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


namespace App\Service\PetActivity;

use App\Entity\PetActivityLog;
use App\Enum\GuildEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetBadgeEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ActivityHelpers;
use App\Functions\AdventureMath;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\PetQuestRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;

class Protocol7Service
{
    public function __construct(
        private readonly ResponseService $responseService,
        private readonly InventoryService $inventoryService,
        private readonly PetExperienceService $petExperienceService,
        private readonly TransactionService $transactionService,
        private readonly GuildService $guildService,
        private readonly EntityManagerInterface $em,
        private readonly IRandom $rng,
        private readonly PetQuestRepository $petQuestRepository
    )
    {
    }

    public function adventure(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $maxSkill = 10 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() - $pet->getAlcohol();

        // protocol 7 is weird; we do a modulo here.
        // we don't do "distraction" encounters for protocol 7; instead, we rely on the modulo, which has the
        // effect of making lower-ranked encounters more common than higher ones for higher-level pets.
        $roll = $this->rng->rngNextInt(0, max(1, $maxSkill)) % 20;

        $activityLog = null;
        $changes = new PetChanges($pet);

        switch($roll)
        {
            case 0:
            case 1:
            case 2:
                if(!$pet->getGuildMembership() && $this->rng->rngNextInt(1, 5) === 1 && !$pet->hasMerit(MeritEnum::AFFECTIONLESS))
                    $activityLog = $this->guildService->joinGuildProjectE($pet);
                else
                    $activityLog = $this->foundNothing($petWithSkills, $roll);
                break;
            case 3:
            case 4:
            case 5:
                if($pet->hasMerit(MeritEnum::BEHATTED) && $this->rng->rngNextInt(1, 40) < $petWithSkills->getScience()->getTotal())
                    $activityLog = $this->encounterAnnabellastasia($petWithSkills);
                else
                    $activityLog = $this->encounterGarbageCollector($petWithSkills);
                break;
            case 6:
            case 7:
            case 8:
                $activityLog = $this->foundLayer02($petWithSkills);
                break;
            case 9:
                if($pet->isInGuild(GuildEnum::CORRESPONDENCE))
                    $activityLog = $this->deliverMessagesForCorrespondence($petWithSkills);
                else
                    $activityLog = $this->foundNothing($petWithSkills, $roll);
                break;
            case 10:
            case 11:
                $activityLog = $this->foundProtectedSector($petWithSkills);
                break;
            case 12:
            case 13:
                $activityLog = $this->watchOnlineVideo($petWithSkills);
                break;
            case 14:
            case 15:
                $activityLog = $this->exploreInsecurePort($petWithSkills);
                break;
            case 16:
            case 17:
                $activityLog = $this->repairShortedCircuit($petWithSkills);
                break;
            case 18:
                $activityLog = $this->exploreWalledGarden($petWithSkills);
                break;
            case 19:
                $activityLog = $this->foundCorruptSector($petWithSkills);
                break;
        }

        if($activityLog)
        {
            $activityLog->setChanges($changes->compare($pet));
        }

        if(AdventureMath::petAttractsBug($this->rng, $pet, 75))
            $this->inventoryService->petAttractsRandomBug($pet, 'Beta Bug');
    }

    private function foundNothing(ComputedPetSkills $petWithSkills, int $roll): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($pet->isInGuild(GuildEnum::DWARFCRAFT))
            return $this->doDwarfcraftDigging($petWithSkills);
        else if($pet->isInGuild(GuildEnum::TIMES_ARROW))
            return $this->doTimesArrow($petWithSkills);

        $exp = (int)ceil($roll / 10);

        if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY) || $this->rng->rngNextInt(1, 3) === 1)
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% accessed Project-E, but got distracted playing a minigame!', 'icons/activity-logs/confused');
        else
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% accessed Project-E, but got lost.', 'icons/activity-logs/confused');

        $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::SCIENCE ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, false);

        $activityLog->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E' ]));

        return $activityLog;
    }

    private function doTimesArrow(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());

        if($roll >= 15)
        {
            $pet->getGuildMembership()->increaseReputation();

            $loot = $this->rng->rngNextFromArray([
                'Pointer',
                'NUL',
                'Music Note',
                'Recovered Archive',
            ]);

            $item = ItemRepository::findOneByName($this->em, $loot);

            [$locationAndAction, $actioning] = $this->rng->rngNextFromArray([
                [ 'an abandoned forum, and started rooting around old posts', 'rooting around in an abandoned forum' ],
                [ 'an old BBS still somehow online, and started digging through its logs', 'digging through the logs of an old BBS' ],
                [ 'a forgotten internet journal, and started combing through old posts and replies', 'digging through posts of a forgotten internet journal' ],
                [ 'a crazy-old MUD no one plays anymore, and started digging through its logs', 'digging through the logs of a forgotten MUD' ],
                [ 'an archive of ROMs from a forgotten computer system, and started trying to make sense of them', 'trying to make sense of old ROMs' ]
            ]);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% accessed Project-E, following some breadcrumbs left by other members of Time\'s Arrow. They reached ' . $locationAndAction . ', eventually piecing together ' . $item->getNameWithArticle() . '!', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E', 'Guild' ]))
            ;

            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' found this while ' . $actioning . ' in Project-E.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, false);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% accessed Project-E, following some breadcrumbs left by other members of Time\'s Arrow, but they lost the trail, and weren\'t able to find it again.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E', 'Guild' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, false);
        }

        return $activityLog;
    }

    private function doDwarfcraftDigging(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $roll = $this->rng->rngNextInt(1, 30 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());

        if($roll < 10)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% accessed Project-E, and went to a Dwarfcraft excavation site. They dug for a while, but didn\'t find anything interesting.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E', 'Guild' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, false);

            return $activityLog;
        }

        if($roll < 20)
        {
            $exp = 1;
            $loot = $this->rng->rngNextFromArray([ 'NUL', 'Pointer' ]);
            $exclaim = '...';
        }
        else if($roll < 30)
        {
            $exp = 1;
            $loot = $this->rng->rngNextFromArray([ 'String', 'Green Dye', 'Green Dye', 'Imaginary Number' ]);
            $exclaim = '.';
        }
        else if($roll < 40)
        {
            $exp = 2;
            $loot = $this->rng->rngNextFromArray([ 'Iron Ore', 'Silver Ore', 'Gold Ore', 'XOR' ]);
            $exclaim = '. Okay.';
        }
        else if($roll < 50)
        {
            $exp = 2;
            $loot = $this->rng->rngNextFromArray([ 'Liquid-hot Magma', 'Cryptocurrency Wallet', 'Magic Smoke' ]);
            $exclaim = '!';
        }
        else if($roll < 60)
        {
            $exp = 3;
            $loot = $this->rng->rngNextFromArray([ 'Fiberglass', 'Blackonite', 'Piece of Cetgueli\'s Map' ]);
            $exclaim = '! Neat!';
        }
        else
        {
            $exp = 4;
            $loot = $this->rng->rngNextFromArray([ 'Firestone', 'Gold Ring' ]);
            $exclaim = '! Whoa!';
        }

        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% accessed Project-E, and went to a Dwarfcraft excavation site. They dug for a while, and found ' . $loot . $exclaim, '')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E', 'Guild' ]))
        ;

        $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' found this while digging at a Dwarfcraft excavation site.', $activityLog);

        $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::SCIENCE ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(40, 55) + $exp * 5, PetActivityStatEnum::PROTOCOL_7, true);

        return $activityLog;
    }

    private function deliverMessagesForCorrespondence(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $effectiveScience = max(2, $petWithSkills->getScience()->getTotal());
        $minMons = min($effectiveScience, $petWithSkills->getIntelligence()->getTotal());

        $moneys = $this->rng->rngNextInt(1, $effectiveScience);

        if($moneys < $minMons)
            $moneys = $minMons;

        $this->transactionService->getMoney($pet->getOwner(), $moneys, $pet->getName() . ' received this for delivering messages for Correspondence.');

        $pet->getGuildMembership()->increaseReputation();

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::PROTOCOL_7, true);

        return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% accessed Project-E. Correspondence had some message-delivery jobs, so %pet:' . $pet->getId() . '.name% picked a couple up, earning ' . $moneys . '~~m~~ for their trouble.', '')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E', 'Guild', 'Moneys' ]))
        ;
    }

    private function encounterAnnabellastasia(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $now = new \DateTimeImmutable();

        $name = $this->rng->rngNextFromArray([
            'Annabellastasia',
            'Xerxeneea',
            'Jellybingus',
        ]);

        $petQuest = $this->petQuestRepository->findOrCreate($pet, 'Next Annabellastasia Encounter', $now->format('Y-m-d'));

        if($petQuest->getValue() > $now->format('Y-m-d'))
            return $this->encounterGarbageCollector($petWithSkills);

        $petQuest->setValue($now->modify('+' . $this->rng->rngNextInt(20, 40) . ' days')->format('Y-m-d'));

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, 'In Project-E, ' . ActivityHelpers::PetName($pet) . ' ran into a girl named ' . $name . ', who handed ' . ActivityHelpers::PetName($pet) . ' a Black Bow.')
            ->setIcon('items/hat/bow-black')
            ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E' ]))
        ;
        $this->inventoryService->petCollectsItem('Black Bow', $pet, $pet->getName() . ' received this from a girl named ' . $name . ' in Project-E.', $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::MET_A_FAMOUS_VIDEO_GAME_CHARACTER, $activityLog);

        return $activityLog;
    }

    private function encounterGarbageCollector(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($pet->isInGuild(GuildEnum::TIMES_ARROW))
        {
            $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() * 2 + $petWithSkills->getScience()->getTotal());

            if($pet->hasMerit(MeritEnum::SOOTHING_VOICE))
                $roll += 2;
        }
        else
            $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal());

        $success = $roll >= 10;

        if($success)
        {
            $pet->increaseEsteem(1);

            if($pet->isInGuild(GuildEnum::TIMES_ARROW))
            {
                $pet->getGuildMembership()->increaseReputation();

                if($pet->hasMerit(MeritEnum::SOOTHING_VOICE) && $this->rng->rngNextInt(1, 3) === 1)
                    $logMessage = '%pet:' . $pet->getId() . '.name% met with a Garbage Collector in Project-E. Happy to help a member of Time\'s Arrow - especially one with such a Soothing Voice! - it handed over a Pointer.';
                else
                    $logMessage = '%pet:' . $pet->getId() . '.name% met with a Garbage Collector in Project-E. Happy to help a member of Time\'s Arrow, it handed over a Pointer.';

                $activityLog = $this->responseService->createActivityLog($pet, $logMessage, 'items/resource/digital/pointer')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E', 'Guild' ]))
                ;
                $this->inventoryService->petCollectsItem('Pointer', $pet, $pet->getName() . ' received this from a Garbage Collector in Project-E.', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% saw a Garbage Collector in Project-E, and took one of the Pointers it was discarding.', 'items/resource/digital/pointer')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E' ]))
                ;
                $this->inventoryService->petCollectsItem('Pointer', $pet, $pet->getName() . ' took this from a Garbage Collector in Project-E.', $activityLog);
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);
        }
        else
        {
            if($pet->isInGuild(GuildEnum::TIMES_ARROW))
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% met with a Garbage Collector in Project-E. It was happy to help a member of Time\'s Arrow, but didn\'t have anything at the moment.', '')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E', 'Guild' ]))
                ;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% saw a Garbage Collector passing by in Project-E, but couldn\'t catch up to it.', '')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, false);
        }

        return $activityLog;
    }

    private function foundLayer02(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal());

        $monster = $this->rng->rngNextFromArray([
            [
                'name' => 'a Trojan Horse',
                'loot' => [ 'Plastic' ]
            ],
            [
                'name' => 'a Clickjacker',
                'loot' => [ 'Browser Cookie' ],
            ],
            [
                'name' => 'an SQL Injection',
                'loot' => [ 'Finite State Machine' ]
            ]
        ]);

        $baddie = $monster['name'];
        $loot = $this->rng->rngNextFromArray($monster['loot']);
        $success = $roll >= 12;

        if($success)
        {
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was assaulted by ' . $baddie . ' in Layer 02 of Project-E, but defeated it, and took its ' . $loot . '!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 12)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E' ]))
            ;
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' defeated ' . $baddie . ', and took this.', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% accessed Layer 02 of Project-E, but ' . $baddie . ' hijacked their session.', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E' ]))
            ;
        }

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::PROTOCOL_7, $success);

        return $activityLog;
    }

    private function foundProtectedSector(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal());

        $monster = $this->rng->rngNextFromArray([
            [
                'name' => 'a Keylogger',
                'loot' => [ 'Hash Table', 'Password' ]
            ],
            [
                'name' => 'a Rootkit',
                'loot' => [ 'Beans', 'Password' ],
            ],
            [
                'name' => 'a Boot Sector Virus',
                'loot' => [ 'Pointer', 'NUL' ]
            ]
        ]);

        $baddie = $monster['name'];
        $loot = $this->rng->rngNextFromArray($monster['loot']);
        $success = $roll >= 15;

        if($this->rng->rngNextInt(1, 200) == 1)
        {
            $pet->increaseSafety(2);
            $pet->increaseEsteem(8);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was assaulted by ' . $baddie . ' in a protected sector of Project-E, but defeated it, and took its Pynʞ! Whoa!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ], $activityLog);
            $this->inventoryService->petCollectsItem('Pynʞ', $pet, $pet->getName() . ' defeated ' . $baddie . ', and got _this!_', $activityLog);
        }
        else if($pet->hasMerit(MeritEnum::LUCKY) && $this->rng->rngNextInt(1, 200) == 1)
        {
            $pet->increaseSafety(2);
            $pet->increaseEsteem(8);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was assaulted by ' . $baddie . ' in a protected sector of Project-E, but defeated it, and took its Pynʞ! (Lucky~!)', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ], $activityLog);
            $this->inventoryService->petCollectsItem('Pynʞ', $pet, $pet->getName() . ' defeated ' . $baddie . ', and got _this!_ (Lucky~!)', $activityLog);
        }
        else if($success)
        {
            $pet->increaseSafety(2);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was assaulted by ' . $baddie . ' in a protected sector of Project-E, but defeated it, and took its ' . $loot . '!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ], $activityLog);
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' defeated ' . $baddie . ', and took this.', $activityLog);
        }
        else
        {
            $pet->increaseSafety(-1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to access a protected sector of Project-E, but couldn\'t get elevated permissions.', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ], $activityLog);
        }

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::PROTOCOL_7, $success);

        return $activityLog;
    }

    private function watchOnlineVideo(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $video = $this->rng->rngNextFromArray([
            [
                'subject' => 'about fractals',
                'loot' => [ 'Imaginary Number' ],
            ],
            [
                'subject' => 'about blockchains',
                'loot' => [ 'Password', 'Hash Table', 'Cryptocurrency Wallet' ],
            ]
        ]);

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal());

        if($roll >= 16)
        {
            $lootItem = ItemRepository::findOneByName($this->em, $this->rng->rngNextFromArray($video['loot']));

            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% watched a video ' . $video['subject'] . ' in Project-E, and got ' . $lootItem->getNameWithArticle() . ' out of it.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E' ]))
            ;
            $this->inventoryService->petCollectsItem($lootItem, $pet, $pet->getName() . ' got this by watching a video ' . $video['subject'] . ' in Project-E.' , $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% watched a video ' . $video['subject'] . ' in Project-E, but didn\'t really get anything out of it.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, false);
        }

        return $activityLog;
    }

    private function exploreInsecurePort(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal());

        $monster = $this->rng->rngNextFromArray([
            [
                'name' => 'a Slow Loris',
                'loot' => [ 'String', 'NUL' ]
            ],
            [
                'name' => 'a Man in the Middle',
                'loot' => [ 'Cryptocurrency Wallet', 'Cryptocurrency Wallet', 'Hash Table' ],
            ]
        ]);

        $baddie = $monster['name'];
        $loot = $this->rng->rngNextFromArray($monster['loot']);
        $success = $roll >= 17;

        if($this->rng->rngNextInt(1, 200) == 1)
        {
            $pet->increaseSafety(2);
            $pet->increaseEsteem(8);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was assaulted by ' . $baddie . ' on an insecure port in Project-E, but defeated it, and took its Pynʞ! Whoa!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ], $activityLog);
            $this->inventoryService->petCollectsItem('Pynʞ', $pet, $pet->getName() . ' defeated ' . $baddie . ', and got _this!_', $activityLog);
        }
        else if($pet->hasMerit(MeritEnum::LUCKY) && $this->rng->rngNextInt(1, 200) == 1)
        {
            $pet->increaseSafety(2);
            $pet->increaseEsteem(8);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was assaulted by ' . $baddie . ' on an insecure port in Project-E, but defeated it, and took its Pynʞ! (Lucky~!)', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ], $activityLog);
            $this->inventoryService->petCollectsItem('Pynʞ', $pet, $pet->getName() . ' defeated ' . $baddie . ', and got _this!_ (Lucky~!)', $activityLog);
        }
        else if($success)
        {
            $pet->increaseSafety(2);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was assaulted by ' . $baddie . ' on an insecure port in Project-E, but defeated it, and took its ' . $loot . '!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 17)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E' ]))
            ;
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' defeated ' . $baddie . ', and took this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ], $activityLog);
        }
        else
        {
            $pet->increaseSafety(-1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% accessed an insecure port in Project-E, but their service was disrupted by ' . $baddie . '.', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ], $activityLog);
        }

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::PROTOCOL_7, $success);

        return $activityLog;
    }

    private function repairShortedCircuit(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $check = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal());

        if($check < 15)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name%\'s line was suddenly shorted while they were exploring Project-E!')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E', 'Physics' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, false);
        }
        else if($this->rng->rngNextInt(1, max(10, 50 - $pet->getSkills()->getIntelligence())) === 1)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name%\'s line was suddenly shorted while they were exploring Project-E. %pet:' . $pet->getId() . '.name% managed to capture some Lightning in a Bottle before being forcefully disconnected!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E', 'Physics' ]))
            ;

            $this->inventoryService->petCollectsItem('Lightning in a Bottle', $pet, $pet->getName() . ' captured this on a shorted line of Project-E!', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::PROTOCOL_7, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name%\'s line was suddenly shorted while they were exploring Project-E. %pet:' . $pet->getId() . '.name% managed to grab a couple Pointers before being forcefully disconnected.')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E', 'Physics' ]))
            ;

            $this->inventoryService->petCollectsItem('Pointer', $pet, $pet->getName() . ' captured this on a shorted line of Project-E!', $activityLog);
            $this->inventoryService->petCollectsItem('Pointer', $pet, $pet->getName() . ' captured this on a shorted line of Project-E!', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);
        }

        if($this->rng->rngNextInt(1, 10 + $petWithSkills->getStamina()->getTotal()) < 8)
        {
            if($petWithSkills->getHasProtectionFromElectricity()->getTotal() > 0)
            {
                $activityLog->setEntry($activityLog->getEntry() . ' Their shock-resistance protected them from the sudden burst of energy.')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;
            }
            else
            {
                $pet->increaseFood(-1);
                $pet->increaseSafety(-$this->rng->rngNextInt(3, 6));

                $activityLog->setEntry($activityLog->getEntry() . ' %pet:' . $pet->getId() . '.name% was unprotected from the sudden burst of energy, and received a minor shock.');
            }
        }

        return $activityLog;
    }

    private function exploreWalledGarden(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $check = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + min($petWithSkills->getScience()->getTotal(), $petWithSkills->getStealth()->getTotal()) + $petWithSkills->getClimbingBonus()->getTotal());

        if($petWithSkills->getClimbingBonus()->getTotal() > 0)
        {
            $toSneak = 'to climb';
            $snuck = 'climbed';
        }
        else
        {
            $toSneak = 'to sneak';
            $snuck = 'snuck';
        }

        if($this->rng->rngNextInt(1, 100) === 1)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% ' . $snuck . ' into a Walled Garden, but ran into a Pirate doing the same! ' . $pet->getName() . ' defeated the Pirate, stole its Jolliest Roger, and ran off before the Walled Garden\'s security system detected them! Yarr!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E', 'Fighting', 'Stealth' ]))
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ;

            $pet->increaseEsteem($this->rng->rngNextInt(4, 8));

            $this->inventoryService->petCollectsItem('Jolliest Roger', $pet, $pet->getName() . ' fought off a Pirate in a Walled Garden within Project-E, and took this from it!', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::SCIENCE, PetSkillEnum::STEALTH, PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, false);
        }
        else if($check < 15)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried ' . $toSneak . ' into a Walled Garden within Project-E, but was kicked out.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E', 'Stealth' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::STEALTH ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, false);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% ' . $snuck . ' into a Walled Garden within Project-E, and plucked a Macintosh that was growing there.')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E', 'Stealth' ]))
            ;

            $this->inventoryService->petCollectsItem('Macintosh', $pet, $pet->getName() . ' found this growing in a Walled Garden within Project-E!', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::STEALTH ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);
        }

        return $activityLog;
    }

    private function foundCorruptSector(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($pet->isInGuild(GuildEnum::TAPESTRIES))
        {
            $check = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + max($petWithSkills->getArcana()->getTotal(), $petWithSkills->getScience()->getTotal()));
        }
        else
        {
            $check = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getScience()->getTotal());
        }

        $lucky = $pet->hasMerit(MeritEnum::LUCKY) && $this->rng->rngNextInt(1, 100) === 1;

        if($check < 20)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% found a corrupt sector, but wasn\'t able to recover any data.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, false);
        }
        else if($lucky)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% found a corrupt sector, and managed to recover a Lo-res Crown from it! Lucky~!')
                ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E', 'Lucky~!' ]))
            ;

            $this->inventoryService->petCollectsItem('Lo-res Crown', $pet, $pet->getName() . ' recovered this from a corrupt sector of Project-E! Lucky~!', $activityLog);

            $pet->increaseEsteem($this->rng->rngNextInt(4, 8));

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::SCIENCE ], $activityLog);
        }
        else if($this->rng->rngNextInt(1, 100) === 1)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% found a corrupt sector, and managed to recover a Lo-res Crown from it!')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E' ]))
            ;

            $this->inventoryService->petCollectsItem('Lo-res Crown', $pet, $pet->getName() . ' recovered this from a corrupt sector of Project-E!', $activityLog);

            $pet->increaseEsteem($this->rng->rngNextInt(4, 8));

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::SCIENCE ], $activityLog);
        }
        else
        {
            $loot = $this->rng->rngNextFromArray([
                'Password',
                'Cryptocurrency Wallet',
                'Egg Book Audiobook',
                'Lycanthropy Report'
            ]);

            $lucky = false;

            if($this->rng->rngNextInt(1, 100) === 1)
                $otherLoot = 'Recovered Archive';
            else if($this->rng->rngNextInt(1, 100) === 1 && $pet->hasMerit(MeritEnum::LUCKY))
            {
                $otherLoot = 'Recovered Archive';
                $lucky = true;
            }
            else
            {
                $otherLoot = $this->rng->rngNextFromArray([
                    'Hash Table',
                    'Finite State Machine',
                    'Browser Cookie'
                ]);
            }

            if($pet->isInGuild(GuildEnum::TAPESTRIES))
            {
                $pet->getGuildMembership()->increaseReputation();
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% found a corrupt sector in Project-E, but was able to repair it as they would repair the fabric of reality, and recover a ' . $otherLoot . ', and ' . $loot . ' from it!')
                    ->setIcon('guilds/tapestries')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E', 'Guild', 'The Umbra' ]))
                ;
                $itemComment = $pet->getName() . ' recovered this by repairing a corrupt sector of Project-E!';
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% found a corrupt sector, and managed to recover a ' . $otherLoot . ', and ' . $loot . ' from it!')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E' ]))
                ;
                $itemComment = $pet->getName() . ' recovered this from a corrupt sector of Project-E!';
            }

            if($lucky)
            {
                $activityLog->setEntry($activityLog->getEntry() . ' Lucky~!')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Lucky~!' ]));
                $itemComment .= ' Lucky~!';
            }

            $this->inventoryService->petCollectsItem($otherLoot, $pet, $itemComment, $activityLog);
            $this->inventoryService->petCollectsItem($loot, $pet, $itemComment, $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);
        }

        return $activityLog;
    }
}
