<?php
namespace App\Service\PetActivity;

use App\Entity\GuildMembership;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\EnumInvalidValueException;
use App\Enum\GuildEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Repository\GuildRepository;
use App\Service\InventoryService;
use App\Service\PetActivity\Guild\CorrespondenceService;
use App\Service\PetActivity\Guild\DwarfCraftService;
use App\Service\PetActivity\Guild\GizubisGardenService;
use App\Service\PetActivity\Guild\HighImpactService;
use App\Service\PetActivity\Guild\InnerSanctumService;
use App\Service\PetActivity\Guild\LightAndShadowService;
use App\Service\PetActivity\Guild\TapestriesService;
use App\Service\PetActivity\Guild\TheUniverseForgetsService;
use App\Service\PetActivity\Guild\TimesArrowService;
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
    private $correspondenceService;
    private $dwarfCraftService;
    private $gizubisGardenService;
    private $highImpactService;
    private $innerSanctumService;
    private $lightAndShadowService;
    private $tapestriesService;
    private $theUniverseForgetsService;
    private $timesArrowService;

    public function __construct(
        GuildRepository $guildRepository, EntityManagerInterface $em, ResponseService $responseService,
        InventoryService $inventoryService, PetExperienceService $petExperienceService,

        CorrespondenceService $correspondenceService, DwarfCraftService $dwarfCraftService,
        GizubisGardenService $gizubisGardenService, HighImpactService $highImpactService,
        InnerSanctumService $innerSanctumService, LightAndShadowService $lightAndShadowService,
        TapestriesService $tapestriesService, TheUniverseForgetsService $theUniverseForgetsService,
        TimesArrowService $timesArrowService
    )
    {
        $this->guildRepository = $guildRepository;
        $this->em = $em;
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;

        $this->correspondenceService = $correspondenceService;
        $this->dwarfCraftService = $dwarfCraftService;
        $this->gizubisGardenService = $gizubisGardenService;
        $this->highImpactService = $highImpactService;
        $this->innerSanctumService = $innerSanctumService;
        $this->lightAndShadowService = $lightAndShadowService;
        $this->tapestriesService = $tapestriesService;
        $this->theUniverseForgetsService = $theUniverseForgetsService;
        $this->timesArrowService = $timesArrowService;
    }

    public function joinGuildProjectE(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROTOCOL_7, false);

        return $this->joinGuild(
            $pet,
            [
                GuildEnum::TIMES_ARROW => $pet->getSkills()->getIntelligence() + $pet->getSkills()->getPerception() + $pet->getSkills()->getScience() + mt_rand(0, 10),
                GuildEnum::TAPESTRIES => $pet->getSkills()->getIntelligence() + $pet->getSkills()->getDexterity() + ($pet->getSkills()->getUmbra() + $pet->getSkills()->getCrafts()) / 2 + mt_rand(0, 10),
                GuildEnum::INNER_SANCTUM => $pet->getSkills()->getIntelligence() * 2 + $pet->getSkills()->getPerception() + mt_rand(0, 10),
                GuildEnum::DWARFCRAFT => $pet->getStrength() + $pet->getStamina() + $pet->getSkills()->getCrafts() + mt_rand(0, 10),
                GuildEnum::HIGH_IMPACT => ($pet->getStrength() + $pet->getDexterity() + $pet->getIntelligence() + $pet->getStamina() + $pet->getSkills()->getBrawl() + $pet->getSkills()->getScience()) / 2 + mt_rand(0, 10),
                GuildEnum::THE_UNIVERSE_FORGETS => $pet->getPerception() + $pet->getIntelligence() + ((1 - $pet->getExtroverted()) * 2 + 1 + $pet->getUmbra()) / 2 + mt_rand(0, 10),
                GuildEnum::CORRESPONDENCE => $pet->getStamina() + $pet->getStrength() + ($pet->getSkills()->getUmbra() + $pet->getSkills()->getStealth() + $pet->getSkills()->getScience()) / 3 + mt_rand(0, 10),
            ],
            $pet->getName() . ' accessed Project-E, and stumbled upon The Hall of Nine - a meeting place for members of nine major Guilds.'
        );
    }

    public function joinGuildUmbra(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::UMBRA, false);

        return $this->joinGuild(
            $pet,
            [
                GuildEnum::LIGHT_AND_SHADOW => $pet->getSkills()->getPerception() + $pet->getSkills()->getUmbra() + $pet->getSkills()->getIntelligence() + mt_rand(0, 10),
                GuildEnum::TAPESTRIES => $pet->getSkills()->getIntelligence() + $pet->getSkills()->getDexterity() + ($pet->getSkills()->getUmbra() + $pet->getSkills()->getCrafts()) / 2 + mt_rand(0, 10),
                GuildEnum::INNER_SANCTUM => $pet->getSkills()->getIntelligence() * 2 + $pet->getSkills()->getPerception() + mt_rand(0, 10),
                GuildEnum::GIZUBIS_GARDEN => ($pet->getExtroverted() + $pet->getSexDrive()) * 3 + $pet->getSkills()->getNature() / 2 + mt_rand(0, 10),
                GuildEnum::THE_UNIVERSE_FORGETS => $pet->getPerception() + $pet->getIntelligence() + ((1 - $pet->getExtroverted()) * 2 + 1 + $pet->getUmbra()) / 2 + mt_rand(0, 10),
                GuildEnum::CORRESPONDENCE => $pet->getStamina() + $pet->getStrength() + ($pet->getSkills()->getUmbra() + $pet->getSkills()->getStealth() + $pet->getSkills()->getScience()) / 3 + mt_rand(0, 10),
            ],
            $pet->getName() . ' visited the Library of Fire, and stumbled upon a meeting between members from the nine major Guilds.'
        );
    }

    private function joinGuild(Pet $pet, array $possibilities, string $message): PetActivityLog
    {
        arsort($possibilities);

        $guildName = array_key_first($possibilities);

        $guild = $this->guildRepository->findOneBy([ 'name' => $guildName ]);

        $membership = (new GuildMembership())->setGuild($guild);
        $pet->setGuildMembership($membership);

        $this->em->persist($membership);

        return $this->responseService->createActivityLog($pet, $message . ' After chatting with a member of ' . $guildName . ' for a while, ' . $pet->getName() . ' decided to join!', '')
            ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
        ;
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function doGuildActivity(Pet $pet): PetActivityLog
    {
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
            case GuildEnum::GIZUBIS_GARDEN: return $this->gizubisGardenService->doAdventure($pet);
            case GuildEnum::HIGH_IMPACT: return $this->doHighImpactMission($pet);
            case GuildEnum::THE_UNIVERSE_FORGETS: return $this->doTheUniverseForgetsMission($pet);
            case GuildEnum::CORRESPONDENCE: return $this->doCorrespondenceMission($pet);

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
                $message = $pet->getName() . ' was introduced to some of the more-important important NPCs in ' . $member->getGuild()->getName() . '.';
                break;
            case 1:
                $message = $pet->getName() . ' visited ' . $member->getGuild()->getName() . ', and received a guild-issued ' . $member->getGuild()->getStarterTool()->getName() . '.';
                $this->inventoryService->petCollectsItem($member->getGuild()->getStarterTool(), $pet, $pet->getName() . ' was given this by their guild, ' . $member->getGuild()->getName() . '.', null);
                break;
            case 2:
                $message = $pet->getName() . ' explored the ' . $member->getGuild()->getName() . ' guild house for a while.';
                break;
            default:
                throw new \Exception('Ben forgot to code stuff for a ' . $member->getRank() . ' in ' . $member->getGuild()->getName() . ' to do! (Way to go, _Ben_!)');
        }

        $member->increaseReputation();

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $this->responseService->createActivityLog($pet, $message, '');
    }

    private function doTimesArrowMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $message = ArrayFunctions::pick_one([
            $pet->getName() . ' ' . ArrayFunctions::pick_one([ 'picked up a book from', 'returned a book to' ]).  ' the Library of Fire for one of their ' . $member->getGuild()->getName() . ' seniors.',
            $pet->getName() . ' practiced using one of ' . $member->getGuild()->getName() . '\'s Timescrawlers. (Supervised, of course!)',
            $pet->getName() . ' shadowed a ' . $member->getGuild()->getName() . ' senior for a little bit, to watch them work.'
        ]);

        $member->increaseReputation();

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $this->responseService->createActivityLog($pet, $message, '');
    }

    private function doLightAndShadowMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $message = ArrayFunctions::pick_one([
            $pet->getName() . ' ' . ArrayFunctions::pick_one([ 'picked up a book from', 'returned a book to' ]).  ' the Library of Fire for one of their ' . $member->getGuild()->getName() . ' seniors.',
            $pet->getName() . ' practiced peering into the Umbra without having to actually go there.',
            $pet->getName() . ' shadowed a ' . $member->getGuild()->getName() . ' senior for a little bit, to watch them work.'
        ]);

        $member->increaseReputation();

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $this->responseService->createActivityLog($pet, $message, '');
    }

    private function doTapestriesMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $message = ArrayFunctions::pick_one([
            $pet->getName() . ' ' . ArrayFunctions::pick_one([ 'picked up a book from', 'returned a book to' ]).  ' the Library of Fire for one of their ' . $member->getGuild()->getName() . ' seniors.',
            $pet->getName() . ' practiced peering into the Umbra without having to actually go there.',
            $pet->getName() . ' watched a ' . $member->getGuild()->getName() . ' senior sew a tear in the fabric of reality...'
        ]);

        $member->increaseReputation();

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $this->responseService->createActivityLog($pet, $message, '');
    }

    private function doInnerSanctumMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $message = ArrayFunctions::pick_one([
            $pet->getName() . ' ' . ArrayFunctions::pick_one([ 'picked up a book from', 'returned a book to' ]).  ' the Library of Fire for one of their ' . $member->getGuild()->getName() . ' seniors.',
            $pet->getName() . ' joined a ' . $member->getGuild()->getName() . ' session of group meditation.',
            $pet->getName() . ' had a minor philosophical debate with a senior ' . $member->getGuild()->getName() . ' member.'
        ]);

        $member->increaseReputation();

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::getRandomValue() ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $this->responseService->createActivityLog($pet, $message, '');
    }

    private function doDwarfcraftMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $message = ArrayFunctions::pick_one([
            $pet->getName() . ' picked up some Liquid-hot Magma for one of their ' . $member->getGuild()->getName() . ' seniors.',
            $pet->getName() . ' delivered Lightning in a Bottle to one of their ' . $member->getGuild()->getName() . ' seniors.',
            $pet->getName() . ' shadowed a ' . $member->getGuild()->getName() . ' senior for a little bit, to watch them work.'
        ]);

        $member->increaseReputation();

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $this->responseService->createActivityLog($pet, $message, '');
    }

    private function doHighImpactMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        // High Impact members do other cool stuff, high up a magic bean stalks, and deep undersea
        if($member->getTitle() >= 3)
            return null;

        $message = ArrayFunctions::pick_one([
            $pet->getName() . ' delivered Lightning in a Bottle to one of their ' . $member->getGuild()->getName() . ' seniors.',
            $pet->getName() . ' shadowed a ' . $member->getGuild()->getName() . ' senior for a little bit, to watch them work.',
            $pet->getName() . ' participated in a ' . $member->getGuild()->getName() . ' obstacle course competition.',
        ]);

        $member->increaseReputation();

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $this->responseService->createActivityLog($pet, $message, '');
    }

    private function doTheUniverseForgetsMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $message = ArrayFunctions::pick_one([
            $pet->getName() . ' ' . ArrayFunctions::pick_one([ 'picked up a book from', 'returned a book to' ]).  ' the Library of Fire for one of their ' . $member->getGuild()->getName() . ' seniors.',
            $pet->getName() . ' practiced peering into the Umbra without having to actually go there.',
            $pet->getName() . ' followed a ' . $member->getGuild()->getName() . ' through unfamiliar regions of the Umbra, looking for any unusual changes.'
        ]);

        $member->increaseReputation();

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $this->responseService->createActivityLog($pet, $message, '');
    }

    private function doCorrespondenceMission(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        // there are delivery messages that Correspondence members can do, during normal pet activities
        if(mt_rand(0, 2) < $member->getTitle())
            return null;

        $message = ArrayFunctions::pick_one([
            $pet->getName() . ' ' . ArrayFunctions::pick_one([ 'picked up a book from', 'returned a book to' ]).  ' the Library of Fire for one of their ' . $member->getGuild()->getName() . ' seniors.',
            $pet->getName() . ' participated in a ' . $member->getGuild()->getName() . ' race.',
            $pet->getName() . ' followed a ' . $member->getGuild()->getName() . ' through unfamiliar regions of Project-E, to deliver a message.'
        ]);

        $member->increaseReputation();

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $this->responseService->createActivityLog($pet, $message, '');
    }
}
