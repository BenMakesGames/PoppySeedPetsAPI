<?php

namespace App\Service\PetActivity;

// see /notes/ElementQuest.md
use App\Entity\PetActivityLog;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ActivityHelpers;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\PetQuestRepository;
use App\Service\EquipmentService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\Squirrel3;

class PhilosophersStoneService
{
    private IRandom $rng;
    private PetQuestRepository $petQuestRepository;
    private ResponseService $responseService;
    private EquipmentService $equipmentService;
    private InventoryService $inventoryService;

    public function __construct(
        Squirrel3 $rng, PetQuestRepository $petQuestRepository, ResponseService $responseService,
        EquipmentService $equipmentService, InventoryService $inventoryService
    )
    {
        $this->rng = $rng;
        $this->petQuestRepository = $petQuestRepository;
        $this->responseService = $responseService;
        $this->equipmentService = $equipmentService;
        $this->inventoryService = $inventoryService;
    }

    public function seekMetatronsFire(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        // fight a lava giant
        // if win, and pet has never won before:
        //    melt equipment, and receive Metatron's Fire
        // if win, and pet has won before:
        //    receive liquid-hot magma, rock, and maybe quint

        $pet = $petWithSkills->getPet();

        $changes = new PetChanges($pet);

        $pet->increaseFood(-1);

        $useDex = $petWithSkills->getDexterity()->getTotal() > $petWithSkills->getStrength()->getTotal();

        if($useDex)
        {
            $skill = 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getBrawl(false)->getTotal();
        }
        else
        {
            $skill = 10 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getBrawl(false)->getTotal();
        }

        $gotTheThing = $this->petQuestRepository->findOrCreate($pet, 'Got Metatron\'s Fire', 0);

        $monster = $gotTheThing->getValue() == 0 ? 'Lava Giant' : 'Lava Giant\'s Spirit';

        if($skill < 20)
        {

            $activityLog = $this->responseService->createActivityLog(
                $pet,
                ActivityHelpers::PetName($pet) . ' found the ' . $monster . ' near the Island\'s Volcano, but realized they were completely outmatched. They returned home, and put away their ' . $pet->getTool()->getFullItemName() . '...',
                ''
            );

            $this->equipmentService->unequipPet($pet);
        }
        else
        {
            $roll = $this->rng->rngNextInt(1, $skill);

            if($roll >= 20)
            {
                $activityLogMessage = $useDex
                    ? ActivityHelpers::PetName($pet) . ' took on the ' . $monster . ' near the Island\'s Volcano, and danced around its attacks before delivering a fatal blow'
                    : ActivityHelpers::PetName($pet) . ' took on the ' . $monster . ' near the Island\'s Volcano, and deflected its attacks before delivering a fatal blow'
                ;

                if($gotTheThing->getValue() == 1)
                {
                    $activityLogMessage .= '. The spirit evaporated, leaving behind Quintessence, and Liquid-hot Magma.';

                    $activityLog = $this->responseService->createActivityLog(
                        $pet,
                        $activityLogMessage,
                        ''
                    );

                    $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' got this from the remains of the Lava Giant\'s Spirit!', $activityLog);
                    $this->inventoryService->petCollectsItem('Liquid-hot Magma', $pet, $pet->getName() . ' got this from the remains of the Lava Giant\'s Spirit!', $activityLog);
                }
                else
                {
                    $activityLogMessage .= ' that shattered ' . ActivityHelpers::PetName($pet) . '\'s ' . $pet->getTool()->getFullItemName() . ' in a flash of light and gust of hot wind! When the dust settled, all that remained of the giant was Metatron\'s Fire!';

                    $gotTheThing->setValue(1);

                    $activityLog = $this->responseService->createActivityLog(
                        $pet,
                        $activityLogMessage,
                        ''
                    );

                    $this->inventoryService->petCollectsItem('Metatron\'s Fire', $pet, $pet->getName() . ' found this after defeating the Lava Giant!', $activityLog);
                }
            }
            else
            {
                $activityLogMessage = $useDex
                    ? ActivityHelpers::PetName($pet) . ' took on the ' . $monster . ' near the Island\'s Volcano, but was unable to outmaneuver its attacks'
                    : ActivityHelpers::PetName($pet) . ' took on the ' . $monster . ' near the Island\'s Volcano, but wasn\'t strong enough to counter its attacks'
                ;

                $activityLogMessage .= ', and was eventually forced to retreat.';

                $activityLog = $this->responseService->createActivityLog(
                    $pet,
                    $activityLogMessage,
                    ''
                );
            }
        }

        $activityLog->setChanges($changes->compare($pet));

        return $activityLog;
    }

    public function seekVesicaHydrargyrum(ComputedPetSkills $petWithSkills)
    {
        // go to a cave in the frozen quag in the umbra
        // if win, and pet has never won before:
        //    ceremony of fire is reduced to a ceremonial trident, and receive Vesica Hydrargyrum
        // if win, and pet has won before:
        //    receive quint, everice, and fish?

        $pet = $petWithSkills->getPet();

        $changes = new PetChanges($pet);

        $pet->increaseFood(-1);

        $skill = 10 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getUmbra()->getTotal();

        $gotTheThing = $this->petQuestRepository->findOrCreate($pet, 'Got Vesica Hydrargyrum', 0);

        $canGetTheThing = $gotTheThing->getValue() == 0;

        if($skill < 20)
        {
            $activityLog = $this->responseService->createActivityLog(
                $pet,
                ActivityHelpers::PetName($pet) . ' found a ice cave in the frozen quag in the Umbra, blocked by huge, Everice icicles. The Ceremony of Fire quivered in ' . ActivityHelpers::PetName($pet) . '\'s hands, but they had no idea how to use it, so returned home and put it away.',
                ''
            );

            $this->equipmentService->unequipPet($pet);
        }
        else
        {
            $roll = $this->rng->rngNextInt(1, $skill);

            if($roll >= 20)
            {
                $activityLogMessage = ActivityHelpers::PetName($pet) . ' went into an ice cave the frozen quag in the Umbra, and used their Ceremony of Fire to melt the huge Everice icicles that stood in their way.';

                if($gotTheThing->getValue() == 1)
                {
                    $activityLogMessage .= '. The Ceremony of Fire made short work of the icicles, freeing the Quintessence locked inside.';

                    $activityLog = $this->responseService->createActivityLog(
                        $pet,
                        $activityLogMessage,
                        ''
                    );

                    $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' got this from the ice cave in the frozen quag of the Umbra.', $activityLog);
                    $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' got this from the ice cave in the frozen quag in the Umbra.', $activityLog);
                }
                else
                {
                    $activityLogMessage .= ' They reached the heart of the cave, where a strange jewel was encased in pure Everice. The Ceremony of Fire\'s magic had to be completely spent to melt through, but in the end, ' . ActivityHelpers::PetName($pet) . ' retrieved the jewel: Vesica Hydrargyrum!';

                    $gotTheThing->setValue(1);

                    $activityLog = $this->responseService->createActivityLog(
                        $pet,
                        $activityLogMessage,
                        ''
                    );

                    $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' got this from the ice cave in the frozen quag in the Umbra.', $activityLog);
                    $this->inventoryService->petCollectsItem('Vesica Hydrargyrum', $pet, $pet->getName() . ' found this in the heart of the ice cave in the frozen quag in the Umbra!', $activityLog);
                }
            }
            else
            {
                $activityLogMessage = ActivityHelpers::PetName($pet) . ' went into an ice cave the frozen quag in the Umbra, but the Ceremony of Fire proved difficult to control, and ' . ActivityHelpers::PetName($pet) . ' had to leave before getting very far.';

                $activityLog = $this->responseService->createActivityLog(
                    $pet,
                    $activityLogMessage,
                    ''
                );
            }
        }

        $activityLog->setChanges($changes->compare($pet));

        return $activityLog;
    }

    public function seekEarthsEgg(ComputedPetSkills $petWithSkills)
    {
        // go to forest, and fight one of some random Jabberwock:
        //    Manxome Jabberwock
        //    Burbling Jabberwock
        //    Uffish Jabberwock
        //    Whiffling Jabberwock
        // if win, and pet has never won before:
        //    sword is shattered, receive jabberwock goods + Earth's Egg
        // if win, and pet has won before:
        //    receive jabberwock goods
    }

    public function seekMerkabaOfAir(ComputedPetSkills $petWithSkills)
    {
        // go to top of volcano, and attempt to split a bolt of lightning in two
        // if win, and pet has never won before:
        //    remove tool bonus, and receive Merkaba of Air
        // if win, and pet has won before:
        //    receive quint, Photons, and Pointers
    }
}