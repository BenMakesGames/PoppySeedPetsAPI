<?php
namespace App\Service\PetActivity;

use App\Entity\PetActivityLog;
use App\Enum\EnumInvalidValueException;
use App\Enum\GatheringHolidayEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\AdventureMath;
use App\Functions\NumberFunctions;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\UserQuestRepository;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

class GatheringHolidayAdventureService
{
    private InventoryService $inventoryService;
    private ResponseService $responseService;
    private PetExperienceService $petExperienceService;
    private IRandom $squirrel3;
    private EntityManagerInterface $em;

    public function __construct(
        InventoryService $inventoryService, ResponseService $responseService, PetExperienceService $petExperienceService,
        IRandom $squirrel3, EntityManagerInterface $em
    )
    {
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->petExperienceService = $petExperienceService;
        $this->squirrel3 = $squirrel3;
        $this->em = $em;
    }

    private const HOLIDAY_TAGS = [
        GatheringHolidayEnum::EASTER => 'Easter',
        GatheringHolidayEnum::SAINT_PATRICKS => 'St. Patrick\'s',
        GatheringHolidayEnum::CHINESE_NEW_YEAR => 'Chinese New Year'
    ];

    public function adventure(ComputedPetSkills $petWithSkills, string $holiday): PetActivityLog
    {
        if(!GatheringHolidayEnum::isAValue($holiday))
            throw new EnumInvalidValueException(GatheringHolidayEnum::class, $holiday);

        $pet = $petWithSkills->getPet();
        $maxSkill = 10 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal() - $pet->getAlcohol() - $pet->getPsychedelic();

        $maxSkill = NumberFunctions::clamp($maxSkill, 1, 21);

        $roll = $this->squirrel3->rngNextInt(1, $maxSkill);

        $activityLog = null;
        $changes = new PetChanges($pet);

        switch($roll)
        {
            case 1:
            case 2:
            case 3:
                $activityLog = $this->goSearching($petWithSkills, $holiday, 'just outside the house', 0, 1, 0, 1);
                break;
            case 4:
            case 5:
                $activityLog = $this->goSearching($petWithSkills, $holiday, 'on the bank of a small stream', 0, 2, 10, 1);
                break;
            case 6:
            case 7:
            case 8:
                $activityLog = $this->goSearching($petWithSkills, $holiday, 'in an Abandoned Quarry', 0, 2, 20, 1);
                break;
            case 9:
                $activityLog = $this->goSearching($petWithSkills, $holiday, 'near a Waterfall Basin', 0, 2, 10, 1);
                break;
            case 10:
                $activityLog = $this->goSearching($petWithSkills, $holiday, 'near the Plaza fountain', 0, 0, 10, 2);
                break;
            case 11:
            case 12:
            case 13:
                $activityLog = $this->goSearching($petWithSkills, $holiday, 'at the beach', 1, 2, 20, 2);
                break;
            case 14:
                $activityLog = $this->goSearching($petWithSkills, $holiday, 'in an Overgrown Garden', 0, 4, 30, 2);
                break;
            case 15:
            case 16:
                $activityLog = $this->goSearching($petWithSkills, $holiday, 'in an Old Iron Mine', 0, 2, 50, 2, true);
                break;
            case 17:
            case 18:
                $activityLog = $this->goSearching($petWithSkills, $holiday, 'in the Micro-Jungle', 1, 3, 40, 2, false, true);
                break;
            case 19:
            case 20:
                $activityLog = $this->goSearching($petWithSkills, $holiday, 'around the island\'s Volcano', 1, 5, 50, 3, false, true);
                break;
            case 21:
                $activityLog = $this->goSearching($petWithSkills, $holiday, 'in a huge cave', 1, 3, 60, 3, true);
                break;
        }

        if($activityLog)
        {
            $activityLog
                ->setChanges($changes->compare($pet))
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Special Event', self::HOLIDAY_TAGS[$holiday] ]))
            ;
        }

        if(AdventureMath::petAttractsBug($this->squirrel3, $pet, 75))
            $this->inventoryService->petAttractsRandomBug($pet);

        return $activityLog;
    }

    private static function searchingFor(string $holiday, bool $plural): string
    {
        switch($holiday)
        {
            case GatheringHolidayEnum::EASTER: return $plural ? 'plastic eggs' : 'plastic egg';
            case GatheringHolidayEnum::SAINT_PATRICKS: return $plural ? 'clovers' : 'clover';
            case GatheringHolidayEnum::CHINESE_NEW_YEAR: return $plural ? 'moneys envelopes' : 'moneys envelope';
            default: throw new EnumInvalidValueException(GatheringHolidayEnum::class, $holiday);
        }
    }

    private function goSearching(ComputedPetSkills $petWithSkills, string $holiday, string $where, int $minEggs, int $maxEggs, int $encounterChance, int $experience, bool $dark = false, bool $hot = false): PetActivityLog
    {
        if($holiday === GatheringHolidayEnum::EASTER)
        {
            if($this->squirrel3->rngNextInt(1, 100) <= $encounterChance && date('l') !== 'Friday')
                return $this->getAttacked($petWithSkills, $maxEggs);
        }

        $pet = $petWithSkills->getPet();

        if($hot)
        {
            if(!$petWithSkills->getHasProtectionFromHeat()->getTotal() > 0 && $this->squirrel3->rngNextInt(1, 10) > $petWithSkills->getStamina()->getTotal())
            {
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, false);

                $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 4));
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went looking for '. GatheringHolidayAdventureService::searchingFor($holiday, true) . ' ' . $where . ', but it was way too hot; they couldn\'t find anything before they had to leave :(', '');
            }
        }

        $message = $pet->getName() . ' went looking for '. GatheringHolidayAdventureService::searchingFor($holiday, true) . ' ' . $where;

        if($dark)
        {
            if($petWithSkills->getCanSeeInTheDark()->getTotal() > 0)
            {
                $numItems = $this->squirrel3->rngNextInt($minEggs + 1, $maxEggs + $this->squirrel3->rngNextInt(1, 2));

                $message .= '. It was really dark, but that didn\'t pose a problem for ' . $pet->getName() . ', who found ' . $numItems . ' ' . GatheringHolidayAdventureService::searchingFor($holiday, $numItems !== 1) . '!';
            }
            else
            {
                $numItems = $this->squirrel3->rngNextInt($minEggs, $maxEggs);

                if($numItems === 0)
                    $message .= ', but it was really dark, and they weren\'t able to find any :(';
                else
                    $message .= '. It was really dark, but they were still able to find ' . $numItems . '!';
            }
        }
        else
        {
            $numItems = $this->squirrel3->rngNextInt($minEggs, $maxEggs);

            if($numItems === 0)
                $message .= ', but wasn\'t able to find any :(';
            else
                $message .= ', and found ' . $numItems . '!';
        }

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, $numItems > 0);

        $activityLog = $this->responseService->createActivityLog($pet, $message, '');

        $this->petExperienceService->gainExp($pet, $experience, [ PetSkillEnum::NATURE ], $activityLog);

        if($holiday === GatheringHolidayEnum::EASTER)
        {
            for($i = 0; $i < $numItems; $i++)
            {
                $r = $this->squirrel3->rngNextInt(1, 100);

                if($r <= 2)
                    $egg = 'Pink Plastic Egg';
                else if($r <= 10)
                    $egg = 'Yellow Plastic Egg';
                else
                    $egg = 'Blue Plastic Egg';

                $this->inventoryService->petCollectsItem($egg, $pet, $pet->getName() . ' found this ' . $where . '!', $activityLog)
                    ->setLockedToOwner($egg !== 'Blue Plastic Egg')
                ;
            }
        }
        else if($holiday === GatheringHolidayEnum::SAINT_PATRICKS)
        {
            for($i = 0; $i < $numItems; $i++)
            {
                $item = $this->inventoryService->petCollectsItem('1-leaf Clover', $pet, $pet->getName() . ' found this ' . $where . '!', $activityLog);

                // it might get eaten immediately
                if($item) $item->setLockedToOwner(true);
            }
        }
        else if($holiday === GatheringHolidayEnum::CHINESE_NEW_YEAR)
        {
            for($i = 0; $i < $numItems; $i++)
            {
                $item = $this->inventoryService->petCollectsItem('Red Envelope', $pet, $pet->getName() . ' found this ' . $where . '!', $activityLog)
                    ->setLockedToOwner(true);
            }
        }
        else
            throw new \Exception("Oops! Ben forgot to code holiday adventure logic for {$holiday}!");

        return $activityLog;
    }

    private function getAttacked(ComputedPetSkills $petWithSkills, int $level)
    {
        $pet = $petWithSkills->getPet();

        $difficulty = 10 + $level * 3;

        $gotBehattingScrollThisEaster = UserQuestRepository::findOrCreate($this->em, $pet->getOwner(), 'Easter ' . date('Y') . ' Behatting Scroll', false);

        if($gotBehattingScrollThisEaster->getValue() === false && $level >= 2)
        {
            $loot = 'Behatting Scroll';
        }
        else
        {
            $loot = $this->squirrel3->rngNextFromArray([
                'Blue Plastic Egg',
                'Yellow Plastic Egg',
                $this->squirrel3->rngNextFromArray([ 'Quintessence', 'Dark Scales' ]),
                $this->squirrel3->rngNextFromArray([ 'Fluff', 'Talon' ]),
                $this->squirrel3->rngNextFromArray([ 'Matzah Bread', 'Fish' ]),
            ]);
        }

        $skillCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl()->getTotal());

        $adjective = $this->squirrel3->rngNextFromArray([
            'horrible', 'crazy', 'mutant', 'disturbing', 'frickin\' weird', 'bananas'
        ]);

        if($skillCheck >= $difficulty)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);

            $pet->increaseEsteem($level);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was attacked by some kind of ' . $adjective . ', fish-rabbit hybrid thing, but was able to defeat it!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + $level * 3)
            ;

            $this->petExperienceService->gainExp($pet, $level, [ PetSkillEnum::BRAWL ], $activityLog);

            $newItem = $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' defeated some kind of ' . $adjective . ', fish-rabbit hybrid thing, and got this!', $activityLog);

            // it might get eaten by the pet, immediately!
            if($newItem)
                $newItem->setLockedToOwner($loot === 'Behatting Scroll' || $loot === 'Yellow Plastic Egg');

            if($loot === 'Behatting Scroll')
            {
                $gotBehattingScrollThisEaster->setValue(true);
                $activityLog->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY);
            }

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);

            $pet->increaseSafety(-$level);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was attacked by some kind of ' . $adjective . ', fish-rabbit hybrid thing! ' . $pet->getName() . ' couldn\'t land a single attack, and ran away!', '');

            $this->petExperienceService->gainExp($pet, ceil($level / 2), [ PetSkillEnum::BRAWL ], $activityLog);

            return $activityLog;
        }
    }
}
