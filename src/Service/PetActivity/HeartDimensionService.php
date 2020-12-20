<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ArrayFunctions;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;

class HeartDimensionService
{
    private $responseService;
    private $petExperienceService;
    private $inventoryService;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetExperienceService $petExperienceService
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
    }

    public function canAdventure(Pet $pet)
    {
        return
            $pet->getAffectionAdventures() < $pet->getAffectionLevel() &&
            $pet->getAffectionAdventures() < 5
        ;
    }

    public function noAdventuresRemaining(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, mt_rand(15, 30), PetActivityStatEnum::OTHER, null);

        $this->inventoryService->unequipPet($pet);

        return $this->responseService->createActivityLog($pet, 'There being nothing more ' . '%pet:' . $pet->getId() . '.name% can do in the Heart Dimension right now, they put the Heartstone down.', '');
    }

    public function adventure(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $changes = new PetChanges($pet);

        $adventure = $pet->getAffectionAdventures() + 1;

        switch($adventure)
        {
            case 1:
                $activityLog = $this->fightAngrySpirit($petWithSkills);
                break;
            case 2:
                $activityLog = $this->beInspired($petWithSkills);
                break;
            case 3:
                $activityLog = $this->defeatNightmare($petWithSkills);
                break;
            case 4:
                $activityLog = $this->haveDivineVision($petWithSkills);
                break;
            case 5:
                $activityLog = $this->defeatShadow($petWithSkills);
                break;
            default:
                throw new \Exception('Ben made a bad error! There is no Heart Dimension adventure that ' . $pet->getName() . ' can go on!');
        }

        $activityLog
            ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
            ->setChanges($changes->compare($pet))
        ;

        if(mt_rand(1, 10) === 1)
            $this->inventoryService->petAttractsRandomBug($pet, 'Heart Beetle');

        return $activityLog;
    }

    private function unequipHeartstone(Pet $pet, PetActivityLog $activityLog)
    {
        $activityLog->setEntry($activityLog->getEntry() . ' ' . $pet->getName() . ' put the Heartstone down.');
        $this->inventoryService->unequipPet($pet);
    }

    public function fightAngrySpirit(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::OTHER, null);

        if($pet->getFood() <= 4)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to fight a Demon of Turmoil in the Heart Dimension, but was too hungry.', 'icons/activity-logs/heart-dimension');
            $this->unequipHeartstone($pet, $activityLog);
            return $activityLog;
        }
        else if($pet->getSafety() <= 4)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to fight a Demon of Turmoil in the Heart Dimension, but was too afraid.', 'icons/activity-logs/heart-dimension');
            $this->unequipHeartstone($pet, $activityLog);
            return $activityLog;
        }
        else if($pet->getLove() <= 4)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to fight a Demon of Turmoil in the Heart Dimension, but was too lonely.', 'icons/activity-logs/heart-dimension');
            $this->unequipHeartstone($pet, $activityLog);
            return $activityLog;
        }
        else if($pet->getEsteem() <= 4)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to fight a Demon of Turmoil in the Heart Dimension, but doubted their self.', 'icons/activity-logs/heart-dimension');
            $this->unequipHeartstone($pet, $activityLog);
            return $activityLog;
        }

        $pet->incrementAffectionAdventures();
        $pet
            ->increaseSafety(999)
            ->increaseLove(999)
            ->increaseEsteem(999)
        ;

        return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% defeated a Demon of Turmoil in the Heart Dimension.', 'icons/activity-logs/heart-dimension');
    }

    public function beInspired(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::OTHER, null);

        if($pet->getFood() <= 4)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to relax in the Heart Dimension, but was too hungry.', 'icons/activity-logs/heart-dimension');
            $this->unequipHeartstone($pet, $activityLog);
            return $activityLog;
        }
        else if($pet->getSafety() <= 4)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to relax in the Heart Dimension, but was too afraid.', 'icons/activity-logs/heart-dimension');
            $this->unequipHeartstone($pet, $activityLog);
            return $activityLog;
        }
        else if($pet->getLove() <= 4)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to relax in the Heart Dimension, but was too lonely.', 'icons/activity-logs/heart-dimension');
            $this->unequipHeartstone($pet, $activityLog);
            return $activityLog;
        }
        else if($pet->getEsteem() <= 4)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to relax in the Heart Dimension, but doubted their self.', 'icons/activity-logs/heart-dimension');
            $this->unequipHeartstone($pet, $activityLog);
            return $activityLog;
        }

        $pet->incrementAffectionAdventures();

        $this->inventoryService->applyStatusEffect($pet, StatusEffectEnum::INSPIRED, 24 * 60);

        return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% relaxed for a while in the Heart Dimension, and became Inspired.', 'icons/activity-logs/heart-dimension');
    }

    public function defeatNightmare(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::OTHER, null);

        if($pet->getFood() <= 4)
        {
            $message = $pet->getName() . ' fought a Nightmare in the Heart Dimension. They overcame their hunger, and defeated it!';
            $pet->increaseFood(999);
        }
        else if($pet->getSafety() <= 4)
        {
            $message = $pet->getName() . ' fought a Nightmare in the Heart Dimension. They overcame their fear, and defeated it!';
            $pet->increaseSafety(999);
        }
        else if($pet->getLove() <= 4)
        {
            $message = $pet->getName() . ' fought a Nightmare in the Heart Dimension. They overcame their loneliness, and defeated it!';
            $pet->increaseLove(999);
        }
        else if($pet->getEsteem() <= 4)
        {
            $message = $pet->getName() . ' fought a Nightmare in the Heart Dimension. They overcame their self-doubt, and defeated it!';
            $pet->increaseEsteem(999);
        }
        else
            $message = $pet->getName() . ' fought a Nightmare in the Heart Dimension, and defeated it easily!';

        $pet->incrementAffectionAdventures();

        return $this->responseService->createActivityLog($pet, $message, 'icons/activity-logs/heart-dimension');
    }

    public function haveDivineVision(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::OTHER, null);

        $pet->incrementAffectionAdventures();

        $this->inventoryService->applyStatusEffect($pet, StatusEffectEnum::INSPIRED, 24 * 60);

        $figure = ArrayFunctions::pick_one([
            [ 'the First Vampire', [ '; it was really scary!', ', but it was oddly calming...' ]],
            [ 'Gizubi and Kaera', [ '. They were angry at one another...', '. They looked happy...' ] ],
            [ 'Kundrav and Keresaspa', [ '. They were fighting, and it was really scary!', '. They were fighting, and it was really cool!' ] ],
            [ 'a jumbled picture of Hahanu', [ '. It seemed angry, somehow...', '. It seemed happy, somehow...' ] ],
            [ 'the Fairy Kingdom', [ ' shrouded in darkness.', ' shining beautifully!' ] ],
            [ 'Sharuminyinka and Tig', [ '. It was really sad...', '. It was really hopeful!' ] ],
            [ 'a cavern filled with gold and gems', [ ', and something dangerous lurking in the shadows...', '! So much treasure waiting to be found!' ] ],
        ]);

        $goodOrBad = mt_rand(0, 1);

        $description = $figure[1][$goodOrBad];

        if($goodOrBad === 0)
            $pet->increaseSafety(-8);
        else
            $pet->increaseSafety(8);

        $message = 'In the Heart Dimension, ' . $pet->getName() . ' saw a vision of ' . $figure[0] . $description;

        if($pet->getNote())
            $pet->setNote($pet->getNote() . "\n\n" . $message);
        else
            $pet->setNote($message);

        return $this->responseService->createActivityLog($pet, $message, 'icons/activity-logs/heart-dimension');
    }

    public function defeatShadow(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();

        $stats = [
            [
                'stat' => PetSkillEnum::CRAFTS,
                'value' => $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal(),
                'message' => 'The shadow drew a sword, but ' . $pet->getName() . ' patched up the mirror before the shadow could escape!',
            ],
            [
                'stat' => PetSkillEnum::BRAWL,
                'value' => $petWithSkills->getBrawl()->getTotal() + $petWithSkills->getStrength()->getTotal(),
                'message' => 'The shadow drew a sword, and leaped out of the mirror! But ' . $pet->getName() . ' struck first, and the shadow dissipated!',
            ],
            [
                'stat' => PetSkillEnum::MUSIC,
                'value' => $petWithSkills->getMusic()->getTotal() + $petWithSkills->getIntelligence()->getTotal(),
                'message' => 'The shadow drew a sword, and leaped out of the mirror! But ' . $pet->getName() . ' sung a song of power, and the shadow dissipated!'
            ],
        ];

        $doIt = ArrayFunctions::max($stats, function($v) {
            return $v['value'];
        });

        $this->petExperienceService->gainExp($pet, 3, [ $doIt['stat'] ]);

        $message = $pet->getName() . ' saw their cursed reflection in a cracked mirror! ' . $doIt['message'];

        $pet
            ->increaseSelfReflectionPoint(1)
            ->incrementAffectionAdventures()
        ;

        return $this->responseService->createActivityLog($pet, $message, 'icons/activity-logs/heart-dimension');
    }
}
