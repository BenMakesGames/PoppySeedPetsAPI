<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Functions\NumberFunctions;
use App\Model\PetChanges;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;

class EasterEggHuntingService
{
    private $inventoryService;
    private $responseService;
    private $petExperienceService;
    private $userQuestRepository;

    public function __construct(
        InventoryService $inventoryService, ResponseService $responseService, PetExperienceService $petExperienceService,
        UserQuestRepository $userQuestRepository
    )
    {
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->petExperienceService = $petExperienceService;
        $this->userQuestRepository = $userQuestRepository;
    }

    public function adventure(Pet $pet): PetActivityLog
    {
        $maxSkill = 10 + $pet->getPerception() + $pet->getNature() + $pet->getGathering() - $pet->getAlcohol() - $pet->getPsychedelic();

        $maxSkill = NumberFunctions::constrain($maxSkill, 1, 21);

        $roll = mt_rand(1, $maxSkill);

        $activityLog = null;
        $changes = new PetChanges($pet);

        switch($roll)
        {
            case 1:
            case 2:
            case 3:
                $activityLog = $this->goSearching($pet, 'just outside the house', 0, 1, 0, 1);
                break;
            case 4:
            case 5:
                $activityLog = $this->goSearching($pet, 'on the bank of a small stream', 0, 2, 10, 1);
                break;
            case 6:
            case 7:
            case 8:
                $activityLog = $this->goSearching($pet, 'in an abandoned quarry', 0, 2, 20, 1);
                break;
            case 9:
                $activityLog = $this->goSearching($pet, 'near a waterfall basin', 0, 2, 10, 1);
                break;
            case 10:
                $activityLog = $this->goSearching($pet, 'near the Plaza fountain', 0, 0, 10, 2);
                break;
            case 11:
            case 12:
            case 13:
                $activityLog = $this->goSearching($pet, 'at the beach', 1, 2, 20, 2);
                break;
            case 14:
                $activityLog = $this->goSearching($pet, 'in an overgrown garden', 0, 4, 30, 2);
                break;
            case 15:
            case 16:
                $activityLog = $this->goSearching($pet, 'in an iron mine', 0, 2, 50, 2, true);
                break;
            case 17:
            case 18:
                $activityLog = $this->goSearching($pet, 'in the microjungle', 1, 3, 40, 2, false, true);
                break;
            case 19:
            case 20:
                $activityLog = $this->goSearching($pet, 'around the volcano', 1, 5, 50, 3, false, true);
                break;
            case 21:
                $activityLog = $this->goSearching($pet, 'in an old mine', 1, 3, 60, 3, true);
                break;
        }

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        if(mt_rand(1, 75) === 1)
            $this->inventoryService->petAttractsRandomBug($pet);

        return $activityLog;
    }

    private function goSearching(Pet $pet, string $where, int $minEggs, int $maxEggs, int $encounterChance, int $experience, bool $dark = false, bool $hot = false): PetActivityLog
    {
        if(mt_rand(1, 100) <= $encounterChance && date('l') !== 'Friday')
            return $this->getAttacked($pet, $maxEggs);

        if($hot)
        {
            if(!$pet->hasProtectionFromHeat() && mt_rand(1, 10) > $pet->getStamina())
            {
                $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GATHER, false);

                $pet->increaseSafety(-mt_rand(2, 4));
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' went looking for plastic eggs ' . $where . ', but it was way too hot; they couldn\'t find anything before they had to leave :(', '');
            }
        }

        $message = $pet->getName() . ' went looking for plastic eggs ' . $where;

        if($dark)
        {
            if($pet->canSeeInTheDark())
            {
                $numEggs = mt_rand($minEggs + 1, $maxEggs + mt_rand(1, 2));

                $message .= '. It was really dark, but that didn\'t pose a problem for ' . $pet->getName() . ', who found ' . $numEggs . ' egg' . ($numEggs === 1 ? '' : 's') . '!';
            }
            else
            {
                $numEggs = mt_rand($minEggs, $maxEggs);

                if($numEggs === 0)
                    $message .= ', but it was really dark, and they weren\'t able to find any :(';
                else
                    $message .= '. It was really dark, but they were still able to find ' . $numEggs . '!';
            }
        }
        else
        {
            $numEggs = mt_rand($minEggs, $maxEggs);

            if($numEggs === 0)
                $message .= ', but wasn\'t able to find any :(';
            else
                $message .= ', and found ' . $numEggs . '!';
        }

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GATHER, $numEggs > 0);
        $this->petExperienceService->gainExp($pet, $experience, [ PetSkillEnum::NATURE ]);

        $activityLog = $this->responseService->createActivityLog($pet, $message, '');

        for($i = 0; $i < $numEggs; $i++)
        {
            $r = mt_rand(1, 100);

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

        return $activityLog;
    }

    private function getAttacked(Pet $pet, int $level)
    {
        $difficulty = 10 + $level * 3;

        $gotBehattingScrollThisEaster = $this->userQuestRepository->findOrCreate($pet->getOwner(), 'Easter ' . date('Y') . ' Behatting Scroll', false);

        if($gotBehattingScrollThisEaster->getValue() === false && $level >= 2)
        {
            $loot = 'Behatting Scroll';
        }
        else
        {
            $loot = ArrayFunctions::pick_one([
                'Blue Plastic Egg',
                'Yellow Plastic Egg',
                ArrayFunctions::pick_one([ 'Quintessence', 'Dark Scales' ]),
                ArrayFunctions::pick_one([ 'Fluff', 'Talon' ]),
                ArrayFunctions::pick_one([ 'Matzah Bread', 'Fish' ]),
            ]);
        }

        $skillCheck = mt_rand(1, 20 + $pet->getStrength() + $pet->getDexterity() + $pet->getBrawl());

        $adjective = ArrayFunctions::pick_one([
            'horrible', 'crazy', 'mutant', 'disturbing', 'frickin\' weird', 'bananas'
        ]);

        if($skillCheck >= $difficulty)
        {
            $pet->increaseEsteem($level);
            $this->petExperienceService->gainExp($pet, $level, [ PetSkillEnum::BRAWL ]);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' was attacked by some kind of ' . $adjective . ', fish-rabbit hybrid thing, but was able to defeat it!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + $level * 3)
            ;

            $newItem = $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' defeated some kind of ' . $adjective . ', fish-rabbit hybrid thing, and got this!', $activityLog);

            // it might get eaten by the pet, immediately!
            if($newItem !== null)
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
            $pet->increaseSafety(-$level);
            $this->petExperienceService->gainExp($pet, ceil($level / 2), [ PetSkillEnum::BRAWL ]);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' was attacked by some kind of ' . $adjective . ', fish-rabbit hybrid thing! ' . $pet->getName() . ' couldn\'t land a single attack, and ran away!', '');

            return $activityLog;
        }
    }
}
