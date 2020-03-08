<?php
namespace App\Service\PetActivity;

use App\Entity\GuildMembership;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\EnumInvalidValueException;
use App\Enum\GuildEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Repository\GuildRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

class GuildService
{
    private $guildRepository;
    private $em;
    private $responseService;
    private $inventoryService;
    private $petExperienceService;

    public function __construct(
        GuildRepository $guildRepository, EntityManagerInterface $em, ResponseService $responseService,
        InventoryService $inventoryService, PetExperienceService $petExperienceService
    )
    {
        $this->guildRepository = $guildRepository;
        $this->em = $em;
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
    }

    public function joinGuild(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROTOCOL_7, false);

        $preferredGuild = [
            GuildEnum::TIMES_ARROW => $pet->getSkills()->getIntelligence() + $pet->getSkills()->getPerception() + $pet->getSkills()->getScience() + mt_rand(-20, 20) / 10,
            GuildEnum::LIGHT_AND_SHADOW => $pet->getSkills()->getPerception() + $pet->getSkills()->getUmbra() + $pet->getSkills()->getIntelligence() + mt_rand(-20, 20) / 10,
            GuildEnum::TAPESTRIES => $pet->getSkills()->getIntelligence() + $pet->getSkills()->getDexterity() + ($pet->getSkills()->getUmbra() + $pet->getSkills()->getCrafts()) / 2 + mt_rand(-20, 20) / 10,
            GuildEnum::INNER_SANCTUM => $pet->getSkills()->getIntelligence() * 2 + $pet->getSkills()->getPerception() + mt_rand(-20, 20) / 10,
            GuildEnum::DWARFCRAFT => $pet->getStrength() + $pet->getStamina() + $pet->getSkills()->getCrafts() + mt_rand(-20, 20) / 10,
            GuildEnum::GIZUBIS_GARDEN => ($pet->getExtroverted() + $pet->getSexDrive() + $pet->getPoly() + 3) * 2 + $pet->getSkills()->getNature() + 1 + mt_rand(-20, 20) / 10,
            GuildEnum::HIGH_IMPACT => ($pet->getStrength() + $pet->getDexterity() + $pet->getIntelligence() + $pet->getStamina() + $pet->getSkills()->getBrawl() + $pet->getSkills()->getScience()) / 2 + mt_rand(-20, 20) / 10,
            GuildEnum::THE_UNIVERSE_FORGETS => $pet->getPerception() + $pet->getIntelligence() + ((1 - $pet->getExtroverted()) * 2 + 1 + $pet->getUmbra()) / 2 + mt_rand(-20, 20) / 10,
            GuildEnum::CORRESPONDENCE => $pet->getStamina() + $pet->getStrength() + ($pet->getSkills()->getUmbra() + $pet->getSkills()->getStealth() + $pet->getSkills()->getScience()) / 3 + mt_rand(-20, 20) / 10,
        ];

        arsort($preferredGuild);

        $guildName = array_key_first($preferredGuild);

        $guild = $this->guildRepository->findOneBy([ 'name' => $guildName ]);

        $membership = (new GuildMembership())->setGuild($guild);
        $pet->setGuildMembership($membership);

        $this->em->persist($membership);

        return $this->responseService->createActivityLog($pet, $pet->getName() . ' accessed Project-E, and stumbled upon The Hall of Nine - a meeting place for members of nine major guilds of Project-E. After chatting with a member of ' . $guildName . ' for a while, ' . $pet->getName() . ' decided to join!', '');
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function doGuildTraining(Pet $pet): PetActivityLog
    {
        if($pet->getGuildMembership()->getLevel() === 0)
        {
            return $this->doGuildIntroductions($pet);
        }

        switch($pet->getGuildMembership()->getGuild()->getName())
        {
            case GuildEnum::TIMES_ARROW:
                return $this->doTimesArrowMission($pet);
                break;

            case GuildEnum::LIGHT_AND_SHADOW:
                return $this->doLightAndShadowMission($pet);
                break;

            case GuildEnum::TAPESTRIES:
                return $this->doTapestriesMission($pet);
                break;

            case GuildEnum::INNER_SANCTUM:
                return $this->doInnerSanctumMission($pet);
                break;

            case GuildEnum::DWARFCRAFT:
                return $this->doDwarfcraftMission($pet);
                break;

            case GuildEnum::GIZUBIS_GARDEN:
                return $this->doGizubisGardenMission($pet);
                break;

            case GuildEnum::HIGH_IMPACT:
                return $this->doHighImpactMission($pet);
                break;

            case GuildEnum::THE_UNIVERSE_FORGETS:
                return $this->doTheUniverseForgetsMission($pet);
                break;

            case GuildEnum::CORRESPONDENCE:
                return $this->doCorrespondenceMission($pet);
                break;

            default:
                throw new EnumInvalidValueException('GuildEnum', $pet->getGuildMembership()->getGuild()->getName());
                break;
        }
    }

    private function doGuildIntroductions(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        switch($member->getReputation())
        {
            case 0:
                $message = $pet->getName() . ' was introduced to some of the more-important important NPCs in ' . $member->getGuildName() . '.';
                break;
            case 1:
                $message = $pet->getName() . ' visited ' . $member->getGuildName() . ', and received a guild-issued ' . $member->getGuild()->getStarterTool()->getName() . '.';
                $this->inventoryService->petCollectsItem($member->getGuild()->getStarterTool(), $pet, $pet->getName() . ' was given this by their guild, ' . $member->getGuildName() . '.', null);
                break;
            case 2:
                $message = $pet->getName() . ' explored the ' . $member->getGuildName() . ' guild house for a while.';
                break;
            default:
                throw new \Exception('Ben forgot to code stuff for a ' . $member->getRank() . ' in ' . $member->getGuildName() . ' to do! (Way to go, _Ben_!)');
        }

        $member->increaseReputation();

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GROUP_ACTIVITY, false);

        return $this->responseService->createActivityLog($pet, $message, '');
    }

    private function doTimesArrowMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $message = ArrayFunctions::pick_one([
            $pet->getName() . ' ' . ArrayFunctions::pick_one([ 'picked up a book from', 'returned a book to' ]).  ' the Library of Fire for one of their ' . $member->getGuildName() . ' seniors.',
            $pet->getName() . ' practiced using one of ' . $member->getGuildName() . '\'s Timescrawlers. (Supervised, of course!)',
            $pet->getName() . ' shadowed a ' . $member->getGuildName() . ' senior for a little bit, to watch them work.'
        ]);

        $member->increaseReputation();

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GROUP_ACTIVITY, false);

        return $this->responseService->createActivityLog($pet, $message, '');
    }

    private function doLightAndShadowMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $message = ArrayFunctions::pick_one([
            $pet->getName() . ' ' . ArrayFunctions::pick_one([ 'picked up a book from', 'returned a book to' ]).  ' the Library of Fire for one of their ' . $member->getGuildName() . ' seniors.',
            $pet->getName() . ' practiced peering into the Umbra without having to actually go there.',
            $pet->getName() . ' shadowed a ' . $member->getGuildName() . ' senior for a little bit, to watch them work.'
        ]);

        $member->increaseReputation();

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GROUP_ACTIVITY, false);

        return $this->responseService->createActivityLog($pet, $message, '');
    }

    private function doTapestriesMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $message = ArrayFunctions::pick_one([
            $pet->getName() . ' ' . ArrayFunctions::pick_one([ 'picked up a book from', 'returned a book to' ]).  ' the Library of Fire for one of their ' . $member->getGuildName() . ' seniors.',
            $pet->getName() . ' practiced peering into the Umbra without having to actually go there.',
            $pet->getName() . ' watched a ' . $member->getGuildName() . ' senior sew a tear in the fabric of reality...'
        ]);

        $member->increaseReputation();

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GROUP_ACTIVITY, false);

        return $this->responseService->createActivityLog($pet, $message, '');
    }

    private function doInnerSanctumMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $message = ArrayFunctions::pick_one([
            $pet->getName() . ' ' . ArrayFunctions::pick_one([ 'picked up a book from', 'returned a book to' ]).  ' the Library of Fire for one of their ' . $member->getGuildName() . ' seniors.',
            $pet->getName() . ' joined a ' . $member->getGuildName() . ' session of group meditation.',
            $pet->getName() . ' had a minor philosophical debate with a senior ' . $member->getGuildName() . ' member.'
        ]);

        $member->increaseReputation();

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::getRandomValue() ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GROUP_ACTIVITY, false);

        return $this->responseService->createActivityLog($pet, $message, '');
    }

    private function doDwarfcraftMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $message = ArrayFunctions::pick_one([
            $pet->getName() . ' picked up some Liquid-hot Magma for one of their ' . $member->getGuildName() . ' seniors.',
            $pet->getName() . ' delivered Lightning in a Bottle to one of their ' . $member->getGuildName() . ' seniors.',
            $pet->getName() . ' shadowed a ' . $member->getGuildName() . ' senior for a little bit, to watch them work.'
        ]);

        $member->increaseReputation();

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GROUP_ACTIVITY, false);

        return $this->responseService->createActivityLog($pet, $message, '');
    }

    private function doGizubisGardenMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        switch(mt_rand(1, 3))
        {
            case 1:
                $message = $pet->getName() . ' helped one of their seniors tend to ' . $member->getGuildName() . ' gardens.';
                $skill = PetSkillEnum::NATURE;
                break;
            case 2:
                $message = $pet->getName() . ' helped cook for a ' . $member->getGuildName() . ' feast.';
                $skill = PetSkillEnum::CRAFTS;
                break;
            case 3:
                $message = $pet->getName() . ' participated in an impromptu ' . $member->getGuildName() . ' jam session.';
                $skill = PetSkillEnum::MUSIC;
                break;
            default:
                throw new \Exception('Ben poorly-coded a switch statement in a Gizbui\'s Garden guild activity!');
        }

        $member->increaseReputation();

        $this->petExperienceService->gainExp($pet, 1, [ $skill ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GROUP_ACTIVITY, false);

        return $this->responseService->createActivityLog($pet, $message, '');
    }

    private function doHighImpactMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $message = ArrayFunctions::pick_one([
            $pet->getName() . ' delivered Lightning in a Bottle to one of their ' . $member->getGuildName() . ' seniors.',
            $pet->getName() . ' shadowed a ' . $member->getGuildName() . ' senior for a little bit, to watch them work.',
            $pet->getName() . ' participated in a ' . $member->getGuildName() . ' obstacle course competition.',
        ]);

        $member->increaseReputation();

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GROUP_ACTIVITY, false);

        return $this->responseService->createActivityLog($pet, $message, '');
    }

    private function doTheUniverseForgetsMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $message = ArrayFunctions::pick_one([
            $pet->getName() . ' ' . ArrayFunctions::pick_one([ 'picked up a book from', 'returned a book to' ]).  ' the Library of Fire for one of their ' . $member->getGuildName() . ' seniors.',
            $pet->getName() . ' practiced peering into the Umbra without having to actually go there.',
            $pet->getName() . ' followed a ' . $member->getGuildName() . ' through unfamiliar regions of the Umbra, looking for any unusual changes.'
        ]);

        $member->increaseReputation();

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GROUP_ACTIVITY, false);

        return $this->responseService->createActivityLog($pet, $message, '');
    }

    private function doCorrespondenceMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $message = ArrayFunctions::pick_one([
            $pet->getName() . ' ' . ArrayFunctions::pick_one([ 'picked up a book from', 'returned a book to' ]).  ' the Library of Fire for one of their ' . $member->getGuildName() . ' seniors.',
            $pet->getName() . ' participated in a ' . $member->getGuildName() . ' race.',
            $pet->getName() . ' followed a ' . $member->getGuildName() . ' through unfamiliar regions of Project-E, to deliver a message.'
        ]);

        $member->increaseReputation();

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GROUP_ACTIVITY, false);

        return $this->responseService->createActivityLog($pet, $message, '');
    }
}
