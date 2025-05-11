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

use App\Entity\Guild;
use App\Entity\GuildMembership;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\EnumInvalidValueException;
use App\Enum\GuildEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\GrammarFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\StatusEffectHelpers;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class GuildService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly InventoryService $inventoryService,
        private readonly PetExperienceService $petExperienceService,
        private readonly GizubisGardenService $gizubisGardenService,
        private readonly IRandom $rng
    )
    {
    }

    public function joinGuildProjectE(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, false);

        $activityLog = $this->joinGuild(
            $pet,
            [
                GuildEnum::TIMES_ARROW => $pet->getSkills()->getIntelligence() + $pet->getSkills()->getPerception() + $pet->getSkills()->getScience() + $this->rng->rngNextInt(0, 10),
                GuildEnum::TAPESTRIES => $pet->getSkills()->getIntelligence() + $pet->getSkills()->getDexterity() + ($pet->getSkills()->getArcana() + $pet->getSkills()->getCrafts()) / 2 + $this->rng->rngNextInt(0, 10),
                GuildEnum::INNER_SANCTUM => $pet->getSkills()->getIntelligence() * 2 + $pet->getSkills()->getPerception() + $this->rng->rngNextInt(0, 10),
                GuildEnum::DWARFCRAFT => $pet->getSkills()->getStrength() + $pet->getSkills()->getStamina() + $pet->getSkills()->getCrafts() + $this->rng->rngNextInt(0, 10),
                GuildEnum::HIGH_IMPACT => ($pet->getSkills()->getStrength() + $pet->getSkills()->getDexterity() + $pet->getSkills()->getIntelligence() + $pet->getSkills()->getStamina() + $pet->getSkills()->getBrawl() + $pet->getSkills()->getScience()) / 2 + $this->rng->rngNextInt(0, 10),
                GuildEnum::THE_UNIVERSE_FORGETS => $pet->getSkills()->getPerception() + $pet->getSkills()->getIntelligence() + ((1 - $pet->getExtroverted()) * 2 + 1 + $pet->getSkills()->getArcana()) / 2 + $this->rng->rngNextInt(0, 10),
                GuildEnum::CORRESPONDENCE => $pet->getSkills()->getStamina() + $pet->getSkills()->getStrength() + ($pet->getSkills()->getArcana() + $pet->getSkills()->getStealth() + $pet->getSkills()->getScience()) / 3 + $this->rng->rngNextInt(0, 10),
            ],
            $pet->getName() . ' accessed Project-E, and stumbled upon The Hall of Nine - a meeting place for members of nine major Guilds.'
        );

        $activityLog->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E', 'Guild' ]));

        return $activityLog;
    }

    public function joinGuildUmbra(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, false);

        $activityLog = $this->joinGuild(
            $pet,
            [
                GuildEnum::LIGHT_AND_SHADOW => $pet->getSkills()->getPerception() + $pet->getSkills()->getArcana() + $pet->getSkills()->getIntelligence() + $this->rng->rngNextInt(0, 10),
                GuildEnum::TAPESTRIES => $pet->getSkills()->getIntelligence() + $pet->getSkills()->getDexterity() + ($pet->getSkills()->getArcana() + $pet->getSkills()->getCrafts()) / 2 + $this->rng->rngNextInt(0, 10),
                GuildEnum::INNER_SANCTUM => $pet->getSkills()->getIntelligence() * 2 + $pet->getSkills()->getPerception() + $this->rng->rngNextInt(0, 10),
                GuildEnum::GIZUBIS_GARDEN => ($pet->getExtroverted() + $petWithSkills->getSexDrive()->getTotal()) * 3 + $pet->getSkills()->getNature() / 2 + $this->rng->rngNextInt(0, 10),
                GuildEnum::THE_UNIVERSE_FORGETS => $pet->getSkills()->getPerception() + $pet->getSkills()->getIntelligence() + ((1 - $pet->getExtroverted()) * 2 + 1 + $pet->getSkills()->getArcana()) / 2 + $this->rng->rngNextInt(0, 10),
                GuildEnum::CORRESPONDENCE => $pet->getSkills()->getStamina() + $pet->getSkills()->getStrength() + ($pet->getSkills()->getArcana() + $pet->getSkills()->getStealth() + $pet->getSkills()->getScience()) / 3 + $this->rng->rngNextInt(0, 10),
            ],
            $pet->getName() . ' visited the Library of Fire, and stumbled upon a meeting between members from the nine major Guilds.'
        );

        $activityLog->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra', 'Guild' ]));

        return $activityLog;
    }

    private function joinGuild(Pet $pet, array $possibilities, string $message): PetActivityLog
    {
        if($pet->hasMerit(MeritEnum::AFFECTIONLESS))
            throw new \InvalidArgumentException('Pet cannot join a Guild if they have the Affectionless Merit.');

        arsort($possibilities);

        $guildName = array_key_first($possibilities);

        $guild = $this->em->getRepository(Guild::class)->findOneBy([ 'name' => $guildName ]);

        $membership = new GuildMembership($pet, $guild);
        $pet->setGuildMembership($membership);

        $this->em->persist($membership);

        return PetActivityLogFactory::createUnreadLog($this->em, $pet, $message . ' After chatting with a member of ' . $guildName . ' for a while, %pet:' . $pet->getId() . '.name% decided to join!')
            ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
        ;
    }

    public function doGuildActivity(ComputedPetSkills $petWithSkills): ?PetActivityLog
    {
        $changes = new PetChanges($petWithSkills->getPet());

        $activityLog = $this->pickGuildActivity($petWithSkills);

        if($activityLog !== null)
        {
            $activityLog
                ->setChanges($changes->compare($petWithSkills->getPet()))
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Guild' ]))
            ;
        }

        return $activityLog;
    }

    private function pickGuildActivity(ComputedPetSkills $petWithSkills): ?PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($pet->getGuildMembership()->getLevel() === 0)
        {
            return $this->doGuildIntroductions($pet);
        }

        return match ($pet->getGuildMembership()->getGuild()->getName())
        {
            GuildEnum::TIMES_ARROW => $this->doTimesArrowMission($pet),
            GuildEnum::LIGHT_AND_SHADOW => $this->doLightAndShadowMission($pet),
            GuildEnum::TAPESTRIES => $this->doTapestriesMission($pet),
            GuildEnum::INNER_SANCTUM => $this->doInnerSanctumMission($pet),
            GuildEnum::DWARFCRAFT => $this->doDwarfcraftMission($pet),
            GuildEnum::GIZUBIS_GARDEN => $this->gizubisGardenService->adventure($petWithSkills),
            GuildEnum::HIGH_IMPACT => $this->doHighImpactMission($pet),
            GuildEnum::THE_UNIVERSE_FORGETS => $this->doTheUniverseForgetsMission($pet),
            GuildEnum::CORRESPONDENCE => $this->doCorrespondenceMission($pet),
            default => throw new EnumInvalidValueException('GuildEnum', $pet->getGuildMembership()->getGuild()->getName()),
        };
    }

    private function doGuildIntroductions(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();
        $collectStarterTool = false;

        switch($member->getReputation())
        {
            case 0:
                $message = '%pet:' . $pet->getId() . '.name% was introduced to some of the more-important important NPCs in ' . $member->getGuild()->getName() . '.';
                break;
            case 1:
                $message = '%pet:' . $pet->getId() . '.name% visited ' . $member->getGuild()->getName() . ', and received a guild-issued ' . $member->getGuild()->getStarterTool()->getName() . '.';
                $collectStarterTool = true;
                break;
            case 2:
                $message = '%pet:' . $pet->getId() . '.name% explored the ' . $member->getGuild()->getName() . ' guild house for a while.';
                break;
            default:
                throw new \Exception('Ben forgot to code stuff for a ' . $member->getRank() . ' in ' . $member->getGuild()->getName() . ' to do! (Way to go, _Ben!_)');
        }

        $member->increaseReputation();

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, $message);

        if($collectStarterTool)
            $this->inventoryService->petCollectsItem($member->getGuild()->getStarterTool(), $pet, $pet->getName() . ' was given this by their guild, ' . $member->getGuild()->getName() . '.', $log);

        return $log;
    }

    private function doTimesArrowMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $message = $this->rng->rngNextFromArray([
            '%pet:' . $pet->getId() . '.name% ' . $this->rng->rngNextFromArray([ 'picked up a book from', 'returned a book to' ]).  ' the Library of Fire for one of their ' . $member->getGuild()->getName() . ' seniors.',
            '%pet:' . $pet->getId() . '.name% practiced using one of ' . $member->getGuild()->getName() . '\'s Timescrawlers. (Supervised, of course!)',
            '%pet:' . $pet->getId() . '.name% shadowed a ' . $member->getGuild()->getName() . ' senior for a little bit, to watch them work.'
        ]);

        $member->increaseReputation();

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $message);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $activityLog;
    }

    private function doLightAndShadowMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $message = $this->rng->rngNextFromArray([
            $pet->getName() . ' ' . $this->rng->rngNextFromArray([ 'picked up a book from', 'returned a book to' ]).  ' the Library of Fire for one of their ' . $member->getGuild()->getName() . ' seniors.',
            $pet->getName() . ' practiced peering into the Umbra without having to actually go there.',
            $pet->getName() . ' shadowed a ' . $member->getGuild()->getName() . ' senior for a little bit, to watch them work.'
        ]);

        $member->increaseReputation();

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $message);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::ARCANA ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $activityLog;
    }

    private function doTapestriesMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $message = $this->rng->rngNextFromArray([
            $pet->getName() . ' ' . $this->rng->rngNextFromArray([ 'picked up a book from', 'returned a book to' ]).  ' the Library of Fire for one of their ' . $member->getGuild()->getName() . ' seniors.',
            $pet->getName() . ' practiced peering into the Umbra without having to actually go there.',
            $pet->getName() . ' watched a ' . $member->getGuild()->getName() . ' senior sew a tear in the fabric of reality...'
        ]);

        $member->increaseReputation();

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $message);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::ARCANA ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $activityLog;
    }

    private function doInnerSanctumMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        if($this->rng->rngNextInt(1, 3) === 1)
        {
            $guildNameArticle = GrammarFunctions::indefiniteArticle($member->getGuild()->getName());
            $message = '%pet:' . $pet->getId() . '.name% joined ' . $guildNameArticle . ' ' . $member->getGuild()->getName() . ' session of group meditation.';

            $availableEffects = [];

            $inspired = $pet->getStatusEffect(StatusEffectEnum::INSPIRED);

            if(!$inspired || $inspired->getTimeRemaining() < 20 * 60)
                $availableEffects[] = [ 'effect' => StatusEffectEnum::INSPIRED, 'duration' => 8 * 60 ];

            if(!$pet->hasStatusEffect(StatusEffectEnum::ONEIRIC))
                $availableEffects[] = [ 'effect' => StatusEffectEnum::ONEIRIC, 'duration' => 1 ];

            if($pet->hasStatusEffect(StatusEffectEnum::TIRED))
                $availableEffects[] = [ 'effect' => StatusEffectEnum::TIRED, 'removeIt' => true ];

            if(count($availableEffects) > 0)
            {
                $effectToGive = $this->rng->rngNextFromArray($availableEffects);

                if(array_key_exists('removeIt', $effectToGive))
                {
                    $message .= ' %pet:' . $pet->getId() . '.name%\'s ' . $effectToGive['effect'] . '-ness was washed away!';
                    $pet->removeStatusEffect($pet->getStatusEffect($effectToGive['effect']));
                }
                else
                {
                    $message .= ' %pet:' . $pet->getId() . '.name% started feeling ' . $effectToGive['effect'] . '!';
                    StatusEffectHelpers::applyStatusEffect($this->em, $pet, $effectToGive['effect'], $effectToGive['duration']);
                }
            }
        }
        else
        {
            $message = $this->rng->rngNextFromArray([
                '%pet:' . $pet->getId() . '.name% ' . $this->rng->rngNextFromArray([ 'picked up a book from', 'returned a book to' ]).  ' the Library of Fire for one of their ' . $member->getGuild()->getName() . ' seniors.',
                '%pet:' . $pet->getId() . '.name% had a minor philosophical debate with a senior ' . $member->getGuild()->getName() . ' member.'
            ]);
        }

        $member->increaseReputation();

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $message);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::getRandomValue($this->rng) ], $activityLog);

        return $activityLog;
    }

    private function doDwarfcraftMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $message = $this->rng->rngNextFromArray([
            $pet->getName() . ' picked up some Liquid-hot Magma for one of their ' . $member->getGuild()->getName() . ' seniors.',
            $pet->getName() . ' delivered Lightning in a Bottle to one of their ' . $member->getGuild()->getName() . ' seniors.',
            $pet->getName() . ' shadowed a ' . $member->getGuild()->getName() . ' senior for a little bit, to watch them work.'
        ]);

        $member->increaseReputation();

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $message);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $activityLog;
    }

    private function doHighImpactMission(Pet $pet): ?PetActivityLog
    {
        $member = $pet->getGuildMembership();

        // High Impact members do other cool stuff, high up a magic bean stalks, and deep undersea
        if($member->getTitle() >= 3)
            return null;

        $message = $this->rng->rngNextFromArray([
            $pet->getName() . ' delivered Lightning in a Bottle to one of their ' . $member->getGuild()->getName() . ' seniors.',
            $pet->getName() . ' shadowed a ' . $member->getGuild()->getName() . ' senior for a little bit, to watch them work.',
            $pet->getName() . ' participated in a ' . $member->getGuild()->getName() . ' obstacle course competition.',
        ]);

        $member->increaseReputation();

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $message);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $activityLog;
    }

    private function doTheUniverseForgetsMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $message = $this->rng->rngNextFromArray([
            $pet->getName() . ' ' . $this->rng->rngNextFromArray([ 'picked up a book from', 'returned a book to' ]).  ' the Library of Fire for one of their ' . $member->getGuild()->getName() . ' seniors.',
            $pet->getName() . ' practiced peering into the Umbra without having to actually go there.',
            $pet->getName() . ' went with a senior member of ' . $member->getGuild()->getName() . ' through unfamiliar regions of the Umbra, looking for any unusual changes.'
        ]);

        $member->increaseReputation();

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $message);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::ARCANA ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $activityLog;
    }

    private function doCorrespondenceMission(Pet $pet): ?PetActivityLog
    {
        $member = $pet->getGuildMembership();

        // there are delivery messages that Correspondence members can do, during normal pet activities
        if($this->rng->rngNextInt(0, 2) < $member->getTitle())
            return null;

        $message = $this->rng->rngNextFromArray([
            $pet->getName() . ' ' . $this->rng->rngNextFromArray([ 'picked up a book from', 'returned a book to' ]) . ' the Library of Fire for one of their ' . $member->getGuild()->getName() . ' seniors.',
            $pet->getName() . ' participated in a ' . $member->getGuild()->getName() . ' race.',
            $pet->getName() . ' followed a senior member of ' . $member->getGuild()->getName() . ' through unfamiliar regions of Project-E, to deliver a message.'
        ]);

        $member->increaseReputation();

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $message);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $activityLog;
    }
}
