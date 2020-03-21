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
use App\Functions\NumberFunctions;
use App\Model\PetChanges;
use App\Repository\GuildRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;

class Protocol7Service
{
    private $responseService;
    private $petExperienceService;
    private $inventoryService;
    private $transactionService;
    private $guildService;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetExperienceService $petExperienceService,
        TransactionService $transactionService, GuildService $guildService
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->transactionService = $transactionService;
        $this->guildService = $guildService;
    }

    public function adventure(Pet $pet)
    {
        $maxSkill = 10 + $pet->getIntelligence() + $pet->getScience() - $pet->getAlcohol();

        $maxSkill = NumberFunctions::constrain($maxSkill, 1, 18);

        $roll = mt_rand(1, $maxSkill);

        $activityLog = null;
        $changes = new PetChanges($pet);

        switch($roll)
        {
            case 1:
            case 2:
            case 3:
            case 4:
                if(!$pet->getGuildMembership() && mt_rand(1, 5) === 1)
                    $activityLog = $this->guildService->joinGuild($pet);
                else if($pet->getGuildMembership() && $pet->getGuildMembership()->getRank() < 4)
                    $activityLog = $this->guildService->doGuildTraining($pet);
                else
                    $activityLog = $this->foundNothing($pet, $roll);
                break;
            case 5:
            case 6:
            case 7:
                $activityLog = $this->encounterGarbageCollector($pet);
                break;
            case 8:
            case 9:
            case 10:
                $activityLog = $this->foundLayer02($pet);
                break;
            case 11:
            case 12:
            case 13:
                $activityLog = $this->foundProtectedSector($pet);
                break;
            case 14:
            case 15:
            case 16:
                $activityLog = $this->exploreInsecurePort($pet);
                break;
            case 17:
                $activityLog = $this->foundNothing($pet, $roll);
                break;
            case 18:
                $activityLog = $this->repairShortedCircuit($pet);
                break;
            case 19:
                $activityLog = $this->exploreWalledGarden($pet);
                break;
        }

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        if(mt_rand(1, 75) === 1)
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function foundNothing(Pet $pet, int $roll): PetActivityLog
    {
        $exp = ceil($roll / 10);

        $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::SCIENCE ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROTOCOL_7, false);

        return $this->responseService->createActivityLog($pet, $pet->getName() . ' accessed Project-E, but got lost.', 'icons/activity-logs/confused');
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function encounterGarbageCollector(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getDexterity() + $pet->getIntelligence() + $pet->getScience());

        $success = $roll >= 10;

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROTOCOL_7, $success);

        if($success)
        {
            $pet->increaseEsteem(1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' saw a Garbage Collector in Project-E, and took one of the Pointers it was discarding.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
            ;
            $this->inventoryService->petCollectsItem('Pointer', $pet, $pet->getName() . ' took this from a Garbage Collector in Project-E.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' saw a Garbage Collector passing by in Project-E, but couldn\'t catch up to it.', '');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function foundLayer02(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getScience());

        $monster = ArrayFunctions::pick_one([
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
        $loot = ArrayFunctions::pick_one($monster['loot']);
        $success = $roll >= 12;

        $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::PROTOCOL_7, $success);

        if($success)
        {
            $pet->increaseEsteem(1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' was assaulted by ' . $baddie . ' in Layer 02 of Project-E, but defeated it, and took its ' . $loot . '!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 12)
            ;
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' defeated ' . $baddie . ', and took this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' accessed Layer 02 of Project-E, but ' . $baddie . ' hijacked their session.', '');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function foundProtectedSector(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getScience());

        $monster = ArrayFunctions::pick_one([
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
        $loot = ArrayFunctions::pick_one($monster['loot']);
        $success = $roll >= 15;

        $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::PROTOCOL_7, $success);

        if($success)
        {
            $pet->increaseSafety(2);
            $pet->increaseEsteem(2);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' was assaulted by ' . $baddie . ' in a protected sector of Project-E, but defeated it, and took its ' . $loot . '!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' defeated ' . $baddie . ', and took this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $pet->increaseSafety(-1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to access a protected sector of Project-E, but couldn\'t get elevated permissions.', '');
        }
    }

    private function exploreInsecurePort(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getScience());

        $monster = ArrayFunctions::pick_one([
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
        $loot = ArrayFunctions::pick_one($monster['loot']);
        $success = $roll >= 17;

        $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::PROTOCOL_7, $success);

        if($success)
        {
            $pet->increaseSafety(2);
            $pet->increaseEsteem(2);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' was assaulted by ' . $baddie . ' on an insecure port in Project-E, but defeated it, and took its ' . $loot . '!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 17)
            ;
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' defeated ' . $baddie . ', and took this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $pet->increaseSafety(-1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' accessed an insecure port in Project-E, but their service was disrupted by ' . $baddie . '.', '');
        }
    }

    private function repairShortedCircuit(Pet $pet): PetActivityLog
    {
        $check = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getScience());

        if($check < 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROTOCOL_7, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . '\'s line was suddenly shorted while they were exploring Project-E!', 'icons/activity-logs/confused');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
        }
        else if(mt_rand(1, max(10, 50 - $pet->getSkills()->getIntelligence())) === 1)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::PROTOCOL_7, true);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . '\'s line was suddenly shorted while they were exploring Project-E. ' . $pet->getName() . ' managed to capture some Lightning in a Bottle before being forcefully disconnected!', '');

            $this->inventoryService->petCollectsItem('Lightning in a Bottle', $pet, $pet->getName() . ' captured this on a shorted line of Project-E!', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . '\'s line was suddenly shorted while they were exploring Project-E. ' . $pet->getName() . ' managed to grab a couple Pointers before being forcefully disconnected,', '');

            $this->inventoryService->petCollectsItem('Pointer', $pet, $pet->getName() . ' captured this on a shorted line of Project-E!', $activityLog);
            $this->inventoryService->petCollectsItem('Pointer', $pet, $pet->getName() . ' captured this on a shorted line of Project-E!', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
        }

        if(mt_rand(1, 10 + $pet->getStamina()) < 8)
        {
            if($pet->hasProtectionFromHeat())
            {
                $activityLog->setEntry($activityLog->getEntry() . ' Their ' . $pet->getTool()->getItem()->getName() . ' protected them from the sudden burst of energy.')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;
            }
            else
            {
                $pet->increaseFood(-1);
                $pet->increaseSafety(-mt_rand(2, 3));

                $activityLog->setEntry($activityLog->getEntry() . ' ' . $pet->getName() . ' was unprotected from the sudden burst of energy, and received a minor singe.');
            }
        }

        return $activityLog;
    }

    private function exploreWalledGarden(Pet $pet): PetActivityLog
    {
        $check = mt_rand(1, 20 + $pet->getIntelligence() + min($pet->getScience(), $pet->getStealth()));

        if($check < 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROTOCOL_7, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to sneak into a Walled Garden within Project-E, but was kicked out.', 'icons/activity-logs/confused');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::STEALTH ]);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' snuck into a Walled Garden within Project-E, and plucked a Macintosh that was growing there.', '');

            $this->inventoryService->petCollectsItem('Macintosh', $pet, $pet->getName() . ' found this growing in a Walled Garden within Project-E!', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::STEALTH ]);
        }

        return $activityLog;
    }
}
