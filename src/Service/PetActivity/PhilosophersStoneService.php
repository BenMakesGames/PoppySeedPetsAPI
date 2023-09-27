<?php

namespace App\Service\PetActivity;

// see /notes/ElementQuest.md
use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ActivityHelpers;
use App\Functions\EquipmentFunctions;
use App\Functions\PetActivityLogFactory;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\ItemRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\PetQuestRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;

class PhilosophersStoneService
{
    private IRandom $rng;
    private PetQuestRepository $petQuestRepository;
    private InventoryService $inventoryService;
    private PetExperienceService $petExperienceService;
    private EntityManagerInterface $em;

    public function __construct(
        Squirrel3 $rng, PetQuestRepository $petQuestRepository, InventoryService $inventoryService,
        EntityManagerInterface $em, PetExperienceService $petExperienceService
    )
    {
        $this->rng = $rng;
        $this->petQuestRepository = $petQuestRepository;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->em = $em;
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
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(15, 30), PetActivityStatEnum::HUNT, false);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em,
                $pet,
                ActivityHelpers::PetName($pet) . ' found the ' . $monster . ' near the island\'s volcano, but realized they were completely outmatched. They returned home, and put away their ' . $pet->getTool()->getFullItemName() . '...'
            );

            EquipmentFunctions::unequipPet($pet);
        }
        else
        {
            $roll = $this->rng->rngNextInt(1, $skill);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(50, 70), PetActivityStatEnum::HUNT, $roll >= 20);

            if($roll >= 20)
            {
                $activityLogMessage = $useDex
                    ? ActivityHelpers::PetName($pet) . ' took on the ' . $monster . ' near the island\'s volcano, and danced around its attacks before delivering a fatal blow'
                    : ActivityHelpers::PetName($pet) . ' took on the ' . $monster . ' near the island\'s volcano, and deflected its attacks before delivering a fatal blow'
                ;

                if($gotTheThing->getValue() == 1)
                {
                    $activityLogMessage .= '. The spirit evaporated, leaving behind Quintessence, and Liquid-hot Magma.';

                    $activityLog = PetActivityLogFactory::createUnreadLog($this->em,
                        $pet,
                        $activityLogMessage
                    );

                    $activityLog->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20);

                    $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' got this from the remains of the Lava Giant\'s Spirit!', $activityLog);
                    $this->inventoryService->petCollectsItem('Liquid-hot Magma', $pet, $pet->getName() . ' got this from the remains of the Lava Giant\'s Spirit!', $activityLog);
                }
                else
                {
                    $activityLogMessage .= ' that shattered ' . ActivityHelpers::PetName($pet) . '\'s ' . $pet->getTool()->getFullItemName() . ' in a flash of light and gust of hot wind! When the dust settled, all that remained of the giant was Metatron\'s Fire!';

                    $gotTheThing->setValue(1);

                    $activityLog = PetActivityLogFactory::createUnreadLog($this->em,
                        $pet,
                        $activityLogMessage
                    );

                    $activityLog->addInterestingness(PetActivityLogInterestingnessEnum::ONE_TIME_QUEST_ACTIVITY);

                    $pet->increaseEsteem(12);

                    $this->em->remove($pet->getTool());
                    $pet->setTool(null);

                    $this->inventoryService->petCollectsItem('Metatron\'s Fire', $pet, $pet->getName() . ' found this after defeating the Lava Giant!', $activityLog);
                }
            }
            else
            {
                $activityLogMessage = $useDex
                    ? ActivityHelpers::PetName($pet) . ' took on the ' . $monster . ' near the island\'s volcano, but was unable to outmaneuver its attacks'
                    : ActivityHelpers::PetName($pet) . ' took on the ' . $monster . ' near the island\'s volcano, but wasn\'t strong enough to counter its attacks'
                ;

                $activityLogMessage .= ', and was eventually forced to retreat.';

                $activityLog = PetActivityLogFactory::createUnreadLog($this->em,
                    $pet,
                    $activityLogMessage
                );
            }

            $this->petExperienceService->gainExp($pet, $roll >= 20 ? 3 : 2, [ PetSkillEnum::BRAWL ], $activityLog);
        }

        $activityLog
            ->setChanges($changes->compare($pet))
            ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Adventure!' ]))
        ;

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

        $skill = 10 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getUmbra()->getTotal();

        $gotTheThing = $this->petQuestRepository->findOrCreate($pet, 'Got Vesica Hydrargyrum', 0);

        if(!$pet->hasMerit(MeritEnum::NATURAL_CHANNEL))
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(5, 10), PetActivityStatEnum::HUNT, false);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em,
                $pet,
                'The Ceremony of Fire tried to lead ' . ActivityHelpers::PetName($pet) . ' somewhere, but after following for a short distance, ' . ActivityHelpers::PetName($pet) . ' suddenly felt ill, as if something was tugging on the threads of their very existence. Confused, and unsettled, ' . ActivityHelpers::PetName($pet) . ' returned home and put down the trident... (The Natural Channel Merit is needed.)'
            );

            $pet->increaseSafety(-4);

            EquipmentFunctions::unequipPet($pet);
        }
        else if($skill < 20)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(15, 30), PetActivityStatEnum::HUNT, false);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em,
                $pet,
                'The Ceremony of Fire lead ' . ActivityHelpers::PetName($pet) . ' to an ice cave in the frozen quag in the Umbra, blocked by huge, Everice icicles. The Ceremony of Fire quivered in ' . ActivityHelpers::PetName($pet) . '\'s hands, but they had no idea how to use it, so returned home and put it away. (Perhaps more Umbra skill would help?)'
            );

            EquipmentFunctions::unequipPet($pet);
        }
        else
        {
            $roll = $this->rng->rngNextInt(1, $skill);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(50, 70), PetActivityStatEnum::UMBRA, $roll >= 20);

            if($roll >= 20)
            {
                $activityLogMessage = 'The Ceremony of Fire lead ' . ActivityHelpers::PetName($pet) . ' to an ice cave in the frozen quag in the Umbra, and used their Ceremony of Fire to melt the huge Everice icicles that stood in their way.';

                if($gotTheThing->getValue() == 1)
                {
                    $activityLogMessage .= '. The Ceremony of Fire made short work of the icicles, freeing the Quintessence locked inside.';

                    $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $activityLogMessage)
                        ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20);

                    $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' got this from the ice cave in the frozen quag of the Umbra.', $activityLog);
                    $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' got this from the ice cave in the frozen quag in the Umbra.', $activityLog);
                }
                else
                {
                    $activityLogMessage .= ' They reached the heart of the cave, where a strange jewel was encased in pure Everice. The Ceremony of Fire\'s magic had to be completely spent to melt through it, but in the end, ' . ActivityHelpers::PetName($pet) . ' retrieved the jewel: a Vesica Hydrargyrum!';

                    $gotTheThing->setValue(1);

                    $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $activityLogMessage);

                    $pet->getTool()->changeItem(ItemRepository::findOneByName($this->em, 'Ceremonial Trident'));

                    $pet->increaseEsteem(12);

                    $activityLog->addInterestingness(PetActivityLogInterestingnessEnum::ONE_TIME_QUEST_ACTIVITY);

                    $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' got this from the ice cave in the frozen quag in the Umbra.', $activityLog);
                    $this->inventoryService->petCollectsItem('Vesica Hydrargyrum', $pet, $pet->getName() . ' found this in the heart of the ice cave in the frozen quag in the Umbra!', $activityLog);
                }
            }
            else
            {
                $activityLogMessage = ActivityHelpers::PetName($pet) . ' went into an ice cave the frozen quag in the Umbra, but the Ceremony of Fire proved difficult to control, and ' . ActivityHelpers::PetName($pet) . ' had to leave before getting very far.';

                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $activityLogMessage);
            }

            $this->petExperienceService->gainExp($pet, $roll >= 20 ? 3 : 2, [ PetSkillEnum::UMBRA ], $activityLog);
        }

        $activityLog
            ->setChanges($changes->compare($pet))
            ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Adventure!' ]))
        ;

        return $activityLog;
    }

    public function seekEarthsEgg(ComputedPetSkills $petWithSkills)
    {
        // go to forest, and fight one of some random Jabberwock:
        // if win, and pet has never won before, defeat the Manxome Jabberwock
        //    sword is shattered, receive Earth's Egg
        // if win, and pet has won before, defeat one of:
        //    Burbling Jabberwock - mermaid egg
        //    Uffish Jabberwock - 3x Egg
        //    Whiffling Jabberwock - egg custard

        $pet = $petWithSkills->getPet();

        $changes = new PetChanges($pet);

        $pet->increaseFood(-1);

        $skill = 10 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl(true)->getTotal();

        $gotTheThing = $this->petQuestRepository->findOrCreate($pet, 'Got Earth\'s Egg', 0);

        $aMonsterType = $gotTheThing->getValue() == 0 ? 'the Manxome' : $this->rng->rngNextFromArray([
            'a Burbling',
            'an Uffish',
            'a Whiffling'
        ]);

        if($skill < 20)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(15, 30), PetActivityStatEnum::HUNT, false);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em,
                $pet,
                ActivityHelpers::PetName($pet) . ' found ' . $aMonsterType . ' Jabberwock in the tulgey wood, but realized they were completely outmatched. They returned home, and put away their ' . $pet->getTool()->getFullItemName() . '...'
            );

            EquipmentFunctions::unequipPet($pet);
        }
        else
        {
            $roll = $this->rng->rngNextInt(1, $skill);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(50, 70), PetActivityStatEnum::HUNT, $roll >= 20);

            if($roll >= 20)
            {
                $activityLogMessage =
                    ActivityHelpers::PetName($pet) . ' took on ' . $aMonsterType . ' Jabberwock in the tulgey wood! The two fought for a while before ' . ActivityHelpers::PetName($pet) . ' delivered the final blow with a swift snicker-snack!'
                ;

                if($aMonsterType == 'the Manxome')
                {
                    $activityLogMessage .= ' The Snickerblade shattered as the jabberwock fell, and a small pouch dropped to the ground. After taking a moment to recover, ' . ActivityHelpers::PetName($pet) . ' opened the pouch, revealing a jewel of preternatural beauty: the Earth\'s Egg!';

                    $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $activityLogMessage);

                    $activityLog->addInterestingness(PetActivityLogInterestingnessEnum::ONE_TIME_QUEST_ACTIVITY);

                    $gotTheThing->setValue(1);

                    $pet->increaseEsteem(12);

                    $this->em->remove($pet->getTool());
                    $pet->setTool(null);

                    $this->inventoryService->petCollectsItem('Earth\'s Egg', $pet, $pet->getName() . ' got this from ' . $aMonsterType . ' Jabberwock!', $activityLog);
                }
                else
                {
                    $loot = [
                        'a Burbling' => [ 'description' => 'a Mermaid Egg', 'items' => [ 'Mermaid Egg' ] ],
                        'an Uffish' => [ 'description' => 'three ordinary Eggs', 'items' => [ 'Egg', 'Egg', 'Egg' ] ],
                        'a Whiffling' => [ 'description' => 'an Egg Custard', 'items' => [ 'Egg Custard' ] ]
                    ][$aMonsterType];

                    $activityLogMessage .= ' The jabberwock fell, dropping ' . $loot['description'] . '.';

                    $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $activityLogMessage);

                    $activityLog->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20);

                    foreach($loot['items'] as $item)
                        $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' got this by defeating ' . $aMonsterType . ' Jabberwock!', $activityLog);
                }
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em,
                    $pet,
                    ActivityHelpers::PetName($pet) . ' took on ' . $aMonsterType . ' Jabberwock in the tulgey wood, but was overpowered, and forced to retreat!'
                );
            }

            $this->petExperienceService->gainExp($pet, $roll >= 20 ? 3 : 2, [ PetSkillEnum::BRAWL ], $activityLog);
        }

        $activityLog
            ->setChanges($changes->compare($pet))
            ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Adventure!' ]))
        ;

        return $activityLog;
    }

    public function seekMerkabaOfAir(ComputedPetSkills $petWithSkills): ?PetActivityLog
    {
        // go to top of volcano, and split a bolt of lightning in two
        $pet = $petWithSkills->getPet();

        $gotTheThing = $this->petQuestRepository->findOrCreate($pet, 'Got Merkaba of Air', 0);

        if($gotTheThing->getValue() == 1)
            return null;

        $changes = new PetChanges($pet);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 120), PetActivityStatEnum::OTHER, false);

        $pet
            ->increaseEsteem(12)
            ->increaseSafety(-24)
        ;

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em,
            $petWithSkills->getPet(),
            ActivityHelpers::PetName($pet) . ' went to the top of the island\'s volcano, and waited for a bolt of lightning. When they sensed one was finally coming, they held their ' . $pet->getTool()->getFullItemName() . ' up into the air! In an explosion of light and sound, ' . ActivityHelpers::PetName($pet) . ' was knocked to the ground, dizzy, unable to see, or hear, and feeling as though on fire! After several minutes, their senses started to return. ' . ActivityHelpers::PetName($pet) . ' slowly stood up, and moved toward a glowing gem among the rocks: a Merkaba of Air! They picked it up, and returned home, vowing to never do this again...'
        );

        $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::SCIENCE ], $activityLog);

        $this->inventoryService->petCollectsItem('Merkaba of Air', $pet, $pet->getName() . ' got this by splitting a bolt of lightning in two!', $activityLog);

        $pet->getTool()->setEnchantment(null);

        $gotTheThing->setValue(1);

        $activityLog
            ->addInterestingness(PetActivityLogInterestingnessEnum::ONE_TIME_QUEST_ACTIVITY)
            ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Adventure!' ]))
            ->setChanges($changes->compare($pet))
        ;

        return $activityLog;
    }
}