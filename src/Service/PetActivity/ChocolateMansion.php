<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetQuest;
use App\Entity\UserQuest;
use App\Enum\MeritEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\ItemRepository;
use App\Repository\PetQuestRepository;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;

class ChocolateMansion
{
    private $userQuestRepository;
    private $rng;
    private $itemRepository;
    private $inventoryService;
    private $petExperienceService;
    private $responseService;
    private $petQuestRepository;
    private $em;

    public function __construct(
        UserQuestRepository $userQuestRepository, Squirrel3 $squirrel3, ItemRepository $itemRepository,
        InventoryService $inventoryService, PetExperienceService $petExperienceService, ResponseService $responseService,
        PetQuestRepository $petQuestRepository, EntityManagerInterface $em
    )
    {
        $this->userQuestRepository = $userQuestRepository;
        $this->rng = $squirrel3;
        $this->itemRepository = $itemRepository;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->responseService = $responseService;
        $this->petQuestRepository = $petQuestRepository;
        $this->em = $em;
    }

    public function adventure(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();

        $this->em->remove($pet->getTool());
        $pet->setTool(null);

        $roomsAvailableQuest = $this->userQuestRepository->findOrCreate($pet->getOwner(), 'Chocolate Mansion Rooms', 1);

        $petFurthestRoom = $this->petQuestRepository->findOrCreate($pet, 'Chocolate Mansion Furthest Room', 3);

        $maxRoom = min($roomsAvailableQuest->getValue(), $petFurthestRoom);

        $activityLog = null;
        $changes = new PetChanges($pet);

        switch($this->rng->rngNextInt(1, $maxRoom))
        {
            case 1:
                $activityLog = $this->explorePatio($petWithSkills, $roomsAvailableQuest);
                break;
            case 2:
                $activityLog = $this->exploreGardens($petWithSkills, $roomsAvailableQuest);
                break;
            case 3:
                $activityLog = $this->exploreFoyer($petWithSkills, $petFurthestRoom);
                break;
            case 4:
                $activityLog = $this->exploreParlor($petWithSkills, $roomsAvailableQuest);
                break;
            case 5:
                $activityLog = $this->exploreMasterBedroom($petWithSkills, $roomsAvailableQuest);
                break;
            case 6:
                $activityLog = $this->exploreStudy($petWithSkills, $roomsAvailableQuest);
                break;
            case 7:
                $activityLog = $this->exploreCellar($petWithSkills);
                break;
            case 8:
                $activityLog = $this->exploreAttic($petWithSkills);
                break;
        }


        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        if($this->rng->rngNextInt(1, 75) === 1)
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    private function getEntryDescription(Pet $pet): string
    {
        return '%pet:' . $pet->getId() . '.name% used their Chocolate Key to open the gates of le Manoir de Chocolate. ';
    }

    private function exploreAttic(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $description = $this->getEntryDescription($pet);

        // TODO: fight chocolate spectre
        // loot: Quintessence, Chocolate-stained Cloth, Chocolate Feather Bonnet
    }

    private function exploreCellar(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $description = $this->getEntryDescription($pet);

        // TODO: fight chocolate vampire
        // loot: Blood Wine, Chocolate Wine, Chocolate Top Hat
    }

    private function exploreStudy(ComputedPetSkills $petWithSkills, UserQuest $quest): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $description = $this->getEntryDescription($pet);

        // TODO: possible to solve chemistry puzzle, unlocking Cellar and Attic
        //$quest->setValue(8);

        // loot: Tiny Scroll of Resources, Le Chocolat (chocolate recipe book), Scroll of Chocolate
    }

    private function exploreMasterBedroom(ComputedPetSkills $petWithSkills, UserQuest $quest): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $description = $this->getEntryDescription($pet);

        // TODO: possible to find secret passage, unlocking Cellar and Attic
        //$quest->setValue(8);

        // loot: Chocolate Chest (no key needed; a pet helps by eating off the lock), Music Notes (from a musicbox)
    }

    private function exploreParlor(ComputedPetSkills $petWithSkills, UserQuest $quest): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $description = $this->getEntryDescription($pet);

        // TODO: possible to play correct tune on piano, unlocking Cellar and Attic
        //$quest->setValue(8);

        // loot: Chocolate Wine, Chocolate Cue Stick, Chocolate Cue Ball, and/or Chocolate LP
    }

    private function exploreFoyer(ComputedPetSkills $petWithSkills, PetQuest $petFurthestRoom): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $description = $this->getEntryDescription($pet);

        if($petFurthestRoom->getValue() === 3)
        {
            $petFurthestRoom->setValue(8);
            $description .= 'They stepped into the mansion for the first time, and took a moment marvel at the grand foyer before snooping around. While there, ';
        }
        else
        {
            $description .= 'They spent some time snooping around the foyer; while there, ';
        }

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStealth()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());
        $difficulty = 16;
        $loot = null;

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, $roll >= $difficulty);

        if($roll <= 2)
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH ]);
            $pet->increaseSafety(-$this->rng->rngNextInt(3, 6));
            $description .= 'a chocolate chandelier fell, almost hitting %pet:' . $pet->getId() . '.name%! They grabbed a piece of its remains, and hightailed it out of there.';
        }
        else if($roll >= $difficulty)
        {
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::STEALTH ]);

            if($this->rng->rngNextBool())
            {
                $loot = $this->itemRepository->findOneByName($this->rng->rngNextFromArray([
                    'Minor Scroll of Riches', 'Piece of Cetgueli\'s Map', 'Wings', 'Cast Net',
                    'Glowing Six-sided Die', 'Glowing Six-sided Die',
                ]));
                $description .= '%pet:' . $pet->getId() . '.name% opened the visor of a chocolate suit of armor, and found ' . $loot->getNameWithArticle() . ' inside!';
                $comment = $pet->getName() . ' found this in a chocolate suit of armor.';
            }
            else
            {
                $loot = $this->itemRepository->findOneByName($this->rng->rngNextFromArray([
                    'Pepperbox', 'Gold Bar', 'Warping Wand', 'XOR',
                    'Glowing Six-sided Die', 'Glowing Six-sided Die',
                ]));
                $description .= '%pet:' . $pet->getId() . '.name% noticed a chocolate grandfather clock had the wrong time, and fixed it. While they had it open, they found ' . $loot->getNameWithArticle() . ' inside!';
                $comment = $pet->getName() . ' found this in a chocolate grandfather clock.';
            }

        }
        else
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH ]);
            $pet->increaseEsteem(-2);

            $loot = 'Chocolate Bar';

            if($this->rng->rngNextBool())
            {
                $description .= '%pet:' . $pet->getId() . '.name% tried to open the visor of a chocolate suit of armor, but accidentally broke a piece off, instead!';
                $comment = 'A piece broken off of a chocolate suit of armor.';
            }
            else
            {
                $description .= '%pet:' . $pet->getId() . '.name% noticed a chocolate grandfather clock had the wrong time, and tried to fix it, but accidentally broke a piece off, instead!';
                $comment = 'A piece broken off of a chocolate grandfather clock.';
            }
        }

        $activityLog = $this->responseService->createActivityLog($pet, $description, '');

        if($loot)
            $this->inventoryService->petCollectsItem($loot, $pet, $comment, $activityLog);

        return $activityLog;
    }

    private function exploreGardens(ComputedPetSkills $petWithSkills, UserQuest $quest): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $description = $this->getEntryDescription($pet);

        if($petWithSkills->getClimbingBonus()->getTotal() > 0)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(15, 30), PetActivityStatEnum::GATHER, true);

            $description .= 'They explored the mansion\'s chocolate hedge maze, climbing over its walls and making directly for the center! ';
            $success = true;
        }
        else if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(15, 30), PetActivityStatEnum::GATHER, true);

            $description .= 'They explored the mansion\'s chocolate hedge maze, which was super-easy thanks to their Eidetic Memory! ';
            $success = true;
        }
        else if($pet->hasMerit(MeritEnum::GOURMAND) && $pet->getFood() <= $pet->getStomachSize() * 3 / 4)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::GATHER, true);

            $pet->increaseFood($this->rng->rngNextInt(4, 8));

            $description .= 'They explored the mansion\'s chocolate hedge maze, eating their way to the center! ';
            $success = true;
        }
        else
        {
            $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getIntelligence()->getTotal());
            $success = $roll > 15;

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, $success);

            if($success)
            {
                $description .= 'They explored the mansion\'s chocolate hedge maze, eventually finding their way to its center! ';
            }
            else
            {
                $description .= 'They explored the mansion\'s chocolate hedge maze, but got hopelessly lost...';
            }
        }

        if($success)
        {
            if($quest->getValue() === 2)
            {
                $quest->setValue(6); // the inside of the mansion
                $description .= 'There was a chocolate fountain in the center; %pet:' . $pet->getId() . '.name% bottled some of the liquid. While they were doing so, they spotted a lever. Pulling it, a large \\*CLANK\\* was heard coming from the front of the house!';
            }
            else
            {
                $description .= 'There was a chocolate fountain in the center; %pet:' . $pet->getId() . '.name% bottled some of the liquid, and brought it home.';
            }

            $activityLog = $this->responseService->createActivityLog($pet, $description, '');

            $this->inventoryService->petCollectsItem('Giant Bottle of Chocolate Syrup', $pet, $pet->getName() . ' collected this from a chocolate fountain in the center of le Manoir de Chocolat\'s chocolate hedge maze.', $activityLog);

            return $activityLog;
        }
        else
        {
            return $this->responseService->createActivityLog($pet, $description, '');
        }
    }

    private function explorePatio(ComputedPetSkills $petWithSkills, UserQuest $quest): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $description = $this->getEntryDescription($pet);

        if($quest->getValue() === 1)
        {
            $quest->setValue(2);
            $description .= 'They tried to enter the mansion\'s front door, but two, giant steel bars blocked entry... ';
        }
        else
            $description .= 'They explored the mansion\'s front patio; ';

        $description .= 'while they were poking around, ';

        if($this->rng->rngNextBool())
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

            $loot = $this->itemRepository->findOneByName($this->rng->rngNextFromArray([
                'Cocoa Powder', 'Sugar',
            ]));

            $description .= 'they kicked up a pile of finely-ground ' . $loot->getName() . '. They came home covered in the stuff, and shook it off in the kitchen. It\'s... probably still good?';

            $activityLog = $this->responseService->createActivityLog($pet, $description, '');

            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' got dusted with this while exploring the front patio of le Manoir de Chocolat.', $activityLog);

            if($this->rng->rngNextInt(1, 3) === 1)
                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' got dusted with this while exploring the front patio of le Manoir de Chocolat.', $activityLog);

            if($this->rng->rngNextInt(1, 4) === 1)
                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' got dusted with this while exploring the front patio of le Manoir de Chocolat.', $activityLog);
        }
        else
        {
            $combatRoll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getBrawl()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStamina()->getTotal());
            $success = $combatRoll > 15;

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

            if($success)
            {
                $description .= 'a Chocolate Mastiff attacked them! %pet:' . $pet->getId() . '.name% fought back, and took a chunk out of the creature, forcing it to flee!';

                $loot = $this->rng->rngNextFromArray([
                    'Chocolate Bar',
                    'Orange Chocolate Bar', 'Orange Chocolate Bar',
                    'Spicy Chocolate Bar', 'Spicy Chocolate Bar'
                ]);

                $activityLog = $this->responseService->createActivityLog($pet, $description, '');

                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' broke this off of a Chocolate Mastiff at le Manoir de Chocolat.', $activityLog);

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ]);
            }
            else
            {
                $description .= 'a Chocolate Mastiff spotted them and gave chase! %pet:' . $pet->getId() . '.name% was forced to flee!';

                $activityLog = $this->responseService->createActivityLog($pet, $description, '');

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
            }
        }

        return $activityLog;
    }
}
