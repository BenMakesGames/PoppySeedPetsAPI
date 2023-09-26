<?php
namespace App\Service\PetActivity;

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
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\GuildRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\StatusEffectServiceHelpers;
use Doctrine\ORM\EntityManagerInterface;

class GuildService
{
    private GuildRepository $guildRepository;
    private EntityManagerInterface $em;
    private ResponseService $responseService;
    private InventoryService $inventoryService;
    private PetExperienceService $petExperienceService;
    private GizubisGardenService $gizubisGardenService;
    private IRandom $squirrel3;
    private PetActivityLogTagRepository $petActivityLogTagRepository;

    public function __construct(
        GuildRepository $guildRepository, EntityManagerInterface $em, ResponseService $responseService,
        InventoryService $inventoryService, PetExperienceService $petExperienceService,
        GizubisGardenService $gizubisGardenService, IRandom $squirrel3,
        PetActivityLogTagRepository $petActivityLogTagRepository
    )
    {
        $this->guildRepository = $guildRepository;
        $this->em = $em;
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->gizubisGardenService = $gizubisGardenService;
        $this->squirrel3 = $squirrel3;
        $this->petActivityLogTagRepository = $petActivityLogTagRepository;
    }

    public function joinGuildProjectE(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, false);

        $activityLog = $this->joinGuild(
            $pet,
            [
                GuildEnum::TIMES_ARROW => $pet->getSkills()->getIntelligence() + $pet->getSkills()->getPerception() + $pet->getSkills()->getScience() + $this->squirrel3->rngNextInt(0, 10),
                GuildEnum::TAPESTRIES => $pet->getSkills()->getIntelligence() + $pet->getSkills()->getDexterity() + ($pet->getSkills()->getUmbra() + $pet->getSkills()->getCrafts()) / 2 + $this->squirrel3->rngNextInt(0, 10),
                GuildEnum::INNER_SANCTUM => $pet->getSkills()->getIntelligence() * 2 + $pet->getSkills()->getPerception() + $this->squirrel3->rngNextInt(0, 10),
                GuildEnum::DWARFCRAFT => $pet->getSkills()->getStrength() + $pet->getSkills()->getStamina() + $pet->getSkills()->getCrafts() + $this->squirrel3->rngNextInt(0, 10),
                GuildEnum::HIGH_IMPACT => ($pet->getSkills()->getStrength() + $pet->getSkills()->getDexterity() + $pet->getSkills()->getIntelligence() + $pet->getSkills()->getStamina() + $pet->getSkills()->getBrawl() + $pet->getSkills()->getScience()) / 2 + $this->squirrel3->rngNextInt(0, 10),
                GuildEnum::THE_UNIVERSE_FORGETS => $pet->getSkills()->getPerception() + $pet->getSkills()->getIntelligence() + ((1 - $pet->getExtroverted()) * 2 + 1 + $pet->getSkills()->getUmbra()) / 2 + $this->squirrel3->rngNextInt(0, 10),
                GuildEnum::CORRESPONDENCE => $pet->getSkills()->getStamina() + $pet->getSkills()->getStrength() + ($pet->getSkills()->getUmbra() + $pet->getSkills()->getStealth() + $pet->getSkills()->getScience()) / 3 + $this->squirrel3->rngNextInt(0, 10),
            ],
            $pet->getName() . ' accessed Project-E, and stumbled upon The Hall of Nine - a meeting place for members of nine major Guilds.'
        );

        $activityLog->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Project-E', 'Guild' ]));

        return $activityLog;
    }

    public function joinGuildUmbra(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, false);

        $activityLog = $this->joinGuild(
            $pet,
            [
                GuildEnum::LIGHT_AND_SHADOW => $pet->getSkills()->getPerception() + $pet->getSkills()->getUmbra() + $pet->getSkills()->getIntelligence() + $this->squirrel3->rngNextInt(0, 10),
                GuildEnum::TAPESTRIES => $pet->getSkills()->getIntelligence() + $pet->getSkills()->getDexterity() + ($pet->getSkills()->getUmbra() + $pet->getSkills()->getCrafts()) / 2 + $this->squirrel3->rngNextInt(0, 10),
                GuildEnum::INNER_SANCTUM => $pet->getSkills()->getIntelligence() * 2 + $pet->getSkills()->getPerception() + $this->squirrel3->rngNextInt(0, 10),
                GuildEnum::GIZUBIS_GARDEN => ($pet->getExtroverted() + $petWithSkills->getSexDrive()->getTotal()) * 3 + $pet->getSkills()->getNature() / 2 + $this->squirrel3->rngNextInt(0, 10),
                GuildEnum::THE_UNIVERSE_FORGETS => $pet->getSkills()->getPerception() + $pet->getSkills()->getIntelligence() + ((1 - $pet->getExtroverted()) * 2 + 1 + $pet->getSkills()->getUmbra()) / 2 + $this->squirrel3->rngNextInt(0, 10),
                GuildEnum::CORRESPONDENCE => $pet->getSkills()->getStamina() + $pet->getSkills()->getStrength() + ($pet->getSkills()->getUmbra() + $pet->getSkills()->getStealth() + $pet->getSkills()->getScience()) / 3 + $this->squirrel3->rngNextInt(0, 10),
            ],
            $pet->getName() . ' visited the Library of Fire, and stumbled upon a meeting between members from the nine major Guilds.'
        );

        $activityLog->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'The Umbra', 'Guild' ]));

        return $activityLog;
    }

    private function joinGuild(Pet $pet, array $possibilities, string $message): PetActivityLog
    {
        if($pet->hasMerit(MeritEnum::AFFECTIONLESS))
            throw new \InvalidArgumentException('Pet cannot join a Guild if they have the Affectionless Merit.');

        arsort($possibilities);

        $guildName = array_key_first($possibilities);

        $guild = $this->guildRepository->findOneBy([ 'name' => $guildName ]);

        $membership = (new GuildMembership())->setGuild($guild);
        $pet->setGuildMembership($membership);

        $this->em->persist($membership);

        return $this->responseService->createActivityLog($pet, $message . ' After chatting with a member of ' . $guildName . ' for a while, %pet:' . $pet->getId() . '.name% decided to join!', '')
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
                ->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => 'Guild' ]))
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

        switch($pet->getGuildMembership()->getGuild()->getName())
        {
            case GuildEnum::TIMES_ARROW: return $this->doTimesArrowMission($pet);
            case GuildEnum::LIGHT_AND_SHADOW: return $this->doLightAndShadowMission($pet);
            case GuildEnum::TAPESTRIES: return $this->doTapestriesMission($pet);
            case GuildEnum::INNER_SANCTUM: return $this->doInnerSanctumMission($pet);
            case GuildEnum::DWARFCRAFT: return $this->doDwarfcraftMission($pet);
            case GuildEnum::GIZUBIS_GARDEN: return $this->gizubisGardenService->adventure($petWithSkills);
            case GuildEnum::HIGH_IMPACT: return $this->doHighImpactMission($pet);
            case GuildEnum::THE_UNIVERSE_FORGETS: return $this->doTheUniverseForgetsMission($pet);
            case GuildEnum::CORRESPONDENCE: return $this->doCorrespondenceMission($pet);

            default:
                throw new EnumInvalidValueException('GuildEnum', $pet->getGuildMembership()->getGuild()->getName());
        }
    }

    private function doGuildIntroductions(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        switch($member->getReputation())
        {
            case 0:
                $message = '%pet:' . $pet->getId() . '.name% was introduced to some of the more-important important NPCs in ' . $member->getGuild()->getName() . '.';
                break;
            case 1:
                $message = '%pet:' . $pet->getId() . '.name% visited ' . $member->getGuild()->getName() . ', and received a guild-issued ' . $member->getGuild()->getStarterTool()->getName() . '.';
                $this->inventoryService->petCollectsItem($member->getGuild()->getStarterTool(), $pet, $pet->getName() . ' was given this by their guild, ' . $member->getGuild()->getName() . '.', null);
                break;
            case 2:
                $message = '%pet:' . $pet->getId() . '.name% explored the ' . $member->getGuild()->getName() . ' guild house for a while.';
                break;
            default:
                throw new \Exception('Ben forgot to code stuff for a ' . $member->getRank() . ' in ' . $member->getGuild()->getName() . ' to do! (Way to go, _Ben!_)');
        }

        $member->increaseReputation();

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $this->responseService->createActivityLog($pet, $message, '');
    }

    private function doTimesArrowMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $message = $this->squirrel3->rngNextFromArray([
            '%pet:' . $pet->getId() . '.name% ' . $this->squirrel3->rngNextFromArray([ 'picked up a book from', 'returned a book to' ]).  ' the Library of Fire for one of their ' . $member->getGuild()->getName() . ' seniors.',
            '%pet:' . $pet->getId() . '.name% practiced using one of ' . $member->getGuild()->getName() . '\'s Timescrawlers. (Supervised, of course!)',
            '%pet:' . $pet->getId() . '.name% shadowed a ' . $member->getGuild()->getName() . ' senior for a little bit, to watch them work.'
        ]);

        $member->increaseReputation();

        $activityLog = $this->responseService->createActivityLog($pet, $message, '');

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $activityLog;
    }

    private function doLightAndShadowMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $message = $this->squirrel3->rngNextFromArray([
            $pet->getName() . ' ' . $this->squirrel3->rngNextFromArray([ 'picked up a book from', 'returned a book to' ]).  ' the Library of Fire for one of their ' . $member->getGuild()->getName() . ' seniors.',
            $pet->getName() . ' practiced peering into the Umbra without having to actually go there.',
            $pet->getName() . ' shadowed a ' . $member->getGuild()->getName() . ' senior for a little bit, to watch them work.'
        ]);

        $member->increaseReputation();

        $activityLog = $this->responseService->createActivityLog($pet, $message, '');

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $activityLog;
    }

    private function doTapestriesMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $message = $this->squirrel3->rngNextFromArray([
            $pet->getName() . ' ' . $this->squirrel3->rngNextFromArray([ 'picked up a book from', 'returned a book to' ]).  ' the Library of Fire for one of their ' . $member->getGuild()->getName() . ' seniors.',
            $pet->getName() . ' practiced peering into the Umbra without having to actually go there.',
            $pet->getName() . ' watched a ' . $member->getGuild()->getName() . ' senior sew a tear in the fabric of reality...'
        ]);

        $member->increaseReputation();

        $activityLog = $this->responseService->createActivityLog($pet, $message, '');

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $activityLog;
    }

    private function doInnerSanctumMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        if($this->squirrel3->rngNextInt(1, 3) === 1)
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
                $effectToGive = $this->squirrel3->rngNextFromArray($availableEffects);

                if(array_key_exists('removeIt', $effectToGive))
                {
                    $message .= ' %pet:' . $pet->getId() . '.name%\'s ' . $effectToGive['effect'] . '-ness was washed away!';
                    $pet->removeStatusEffect($pet->getStatusEffect($effectToGive['effect']));
                }
                else
                {
                    $message .= ' %pet:' . $pet->getId() . '.name% started feeling ' . $effectToGive['effect'] . '!';
                    StatusEffectServiceHelpers::applyStatusEffect($this->em, $pet, $effectToGive['effect'], $effectToGive['duration']);
                }
            }
        }
        else
        {
            $message = $this->squirrel3->rngNextFromArray([
                '%pet:' . $pet->getId() . '.name% ' . $this->squirrel3->rngNextFromArray([ 'picked up a book from', 'returned a book to' ]).  ' the Library of Fire for one of their ' . $member->getGuild()->getName() . ' seniors.',
                '%pet:' . $pet->getId() . '.name% had a minor philosophical debate with a senior ' . $member->getGuild()->getName() . ' member.'
            ]);
        }

        $member->increaseReputation();

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        $activityLog = $this->responseService->createActivityLog($pet, $message, '');

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::getRandomValue($this->squirrel3) ], $activityLog);

        return $activityLog;
    }

    private function doDwarfcraftMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $message = $this->squirrel3->rngNextFromArray([
            $pet->getName() . ' picked up some Liquid-hot Magma for one of their ' . $member->getGuild()->getName() . ' seniors.',
            $pet->getName() . ' delivered Lightning in a Bottle to one of their ' . $member->getGuild()->getName() . ' seniors.',
            $pet->getName() . ' shadowed a ' . $member->getGuild()->getName() . ' senior for a little bit, to watch them work.'
        ]);

        $member->increaseReputation();

        $activityLog = $this->responseService->createActivityLog($pet, $message, '');

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $activityLog;
    }

    private function doHighImpactMission(Pet $pet): ?PetActivityLog
    {
        $member = $pet->getGuildMembership();

        // High Impact members do other cool stuff, high up a magic bean stalks, and deep undersea
        if($member->getTitle() >= 3)
            return null;

        $message = $this->squirrel3->rngNextFromArray([
            $pet->getName() . ' delivered Lightning in a Bottle to one of their ' . $member->getGuild()->getName() . ' seniors.',
            $pet->getName() . ' shadowed a ' . $member->getGuild()->getName() . ' senior for a little bit, to watch them work.',
            $pet->getName() . ' participated in a ' . $member->getGuild()->getName() . ' obstacle course competition.',
        ]);

        $member->increaseReputation();

        $activityLog = $this->responseService->createActivityLog($pet, $message, '');

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $activityLog;
    }

    private function doTheUniverseForgetsMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $message = $this->squirrel3->rngNextFromArray([
            $pet->getName() . ' ' . $this->squirrel3->rngNextFromArray([ 'picked up a book from', 'returned a book to' ]).  ' the Library of Fire for one of their ' . $member->getGuild()->getName() . ' seniors.',
            $pet->getName() . ' practiced peering into the Umbra without having to actually go there.',
            $pet->getName() . ' went with a senior member of ' . $member->getGuild()->getName() . ' through unfamiliar regions of the Umbra, looking for any unusual changes.'
        ]);

        $member->increaseReputation();

        $activityLog = $this->responseService->createActivityLog($pet, $message, '');

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $activityLog;
    }

    private function doCorrespondenceMission(Pet $pet): ?PetActivityLog
    {
        $member = $pet->getGuildMembership();

        // there are delivery messages that Correspondence members can do, during normal pet activities
        if($this->squirrel3->rngNextInt(0, 2) < $member->getTitle())
            return null;

        $message = $this->squirrel3->rngNextFromArray([
            $pet->getName() . ' ' . $this->squirrel3->rngNextFromArray([ 'picked up a book from', 'returned a book to' ]) . ' the Library of Fire for one of their ' . $member->getGuild()->getName() . ' seniors.',
            $pet->getName() . ' participated in a ' . $member->getGuild()->getName() . ' race.',
            $pet->getName() . ' followed a senior member of ' . $member->getGuild()->getName() . ' through unfamiliar regions of Project-E, to deliver a message.'
        ]);

        $member->increaseReputation();

        $activityLog = $this->responseService->createActivityLog($pet, $message, '');

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $activityLog;
    }
}
