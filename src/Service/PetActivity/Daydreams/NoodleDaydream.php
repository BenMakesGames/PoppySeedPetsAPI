<?php

namespace App\Service\PetActivity\Daydreams;

use App\Entity\PetActivityLog;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ActivityHelpers;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\SpiceRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class NoodleDaydream
{
    private EntityManagerInterface $em;
    private IRandom $rng;
    private PetExperienceService $petExperienceService;
    private InventoryService $inventoryService;

    public function __construct(
        EntityManagerInterface $em, IRandom $rng, PetExperienceService $petExperienceService,
        InventoryService $inventoryService
    )
    {
        $this->em = $em;
        $this->rng = $rng;
        $this->petExperienceService = $petExperienceService;
        $this->inventoryService = $inventoryService;
    }

    public function doAdventure(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $changes = new PetChanges($pet);

        $adventures = [
            [ $this, 'doNoodleKraken' ],
            [ $this, 'doGoldenLifeNoodles' ],
            [ $this, 'doNoodlePuppetShow' ],
            [ $this, 'doTarzan' ],
        ];

        $log = $this->rng->rngNextFromArray($adventures)($petWithSkills);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);

        $log
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Dream' ]))
            ->setIcon('icons/status-effect/daydream-noodles')
            ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ->setChanges($changes->compare($pet));

        return $log;
    }

    private function doNoodlePuppetShow(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $log = PetActivityLogFactory::createUnreadLog(
            $this->em,
            $petWithSkills->getPet(),
            ActivityHelpers::PetName($pet) . ' daydreamed they were in a theater of shadows, where they encountered a puppet master whose marionettes danced on strings of spaghetti, each movement telling tales of feasts and famines. At the end of the show, they snapped back to reality, a scroll in their hands...'
        );

        $this->inventoryService->petCollectsItem('Farmer\'s Scroll', $pet, $pet->getName() . ' received this after watching a puppet show in a daydream.', $log);

        return $log;
    }

    private function doNoodleKraken(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl(false)->getTotal());

        if($roll >= 20)
        {
            $log = PetActivityLogFactory::createUnreadLog(
                $this->em,
                $petWithSkills->getPet(),
                ActivityHelpers::PetName($pet) . ' daydreamed they were in a ship on a stormy sea, when a creature attacked from the depths! Enormous, noodle tentacles wrapped around the ship, and began attacking the crew! ' . ActivityHelpers::PetName($pet) . ' fought their hardest, and with help from their crew mates were able to beat the creature back! When they snapped back to reality, they were covered in the monster\'s remains, and other detritus...'
            );

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::BRAWL ], $log);

            $remains = $this->rng->rngNextSubsetFromArray([
                'Noodles', 'Noodles', 'Chili Calamari', 'Tomato "Sushi"',
                'Super-simple Spaghet', 'Seaweed',
            ], 5);

            $spices = $this->rng->rngNextSubsetFromArray([
                'Buttery', 'Cosmic', 'Ducky', 'Fishy', 'Spicy', 'Sweet & Spicy',
                'Tropical', 'with Ketchup', 'with Ponzu',
            ], 5);

            for($i = 0; $i < 4; $i++)
            {
                $spice = SpiceRepository::findOneByName($this->em, $spices[$i]);
                $this->inventoryService->petCollectsEnhancedItem($remains[$i], null, $spice, $pet, $pet->getName() . ' was attacked by a noodle-kraken in a daydream. After defeating it, this was left behind.', $log);
            }
        }
        else
        {
            $log = PetActivityLogFactory::createUnreadLog(
                $this->em,
                $petWithSkills->getPet(),
                ActivityHelpers::PetName($pet) . ' daydreamed they were in a ship on a stormy sea, when a creature attacked from the depths! Enormous, noodle tentacles wrapped around the ship, and began attacking the crew! ' . ActivityHelpers::PetName($pet) . ' fought their hardest, but was no match for the monster. When they snapped back to reality, they were covered in noodles...'
            );

            $this->inventoryService->petCollectsItem('Noodles', $pet, $pet->getName() . ' was attacked by a noodle-kraken in a daydream; these are some of its tentacles. Apparently.', $log);
            $this->inventoryService->petCollectsItem('Noodles', $pet, $pet->getName() . ' was attacked by a noodle-kraken in a daydream; these are some of its tentacles. Apparently.', $log);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $log);
        }

        return $log;
    }

    private function doGoldenLifeNoodles(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $log = PetActivityLogFactory::createUnreadLog(
            $this->em,
            $petWithSkills->getPet(),
            ActivityHelpers::PetName($pet) . ' daydreamed they were in a city suspended in the clouds, where they navigated pathways made of golden noodles, each strand representing a different life\'s journey. The followed the story of one strand in particular, but then snapped back to reality, and found they couldn\'t remember what the story was about...'
        );

        // 10 exp in a random skill:
        $this->petExperienceService->gainExp($pet, 10, PetSkillEnum::getValues(), $log);

        return $log;
    }

    private function doTarzan(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getStamina()->getTotal());

        if($roll >= 15)
        {
            $log = PetActivityLogFactory::createUnreadLog(
                $this->em,
                $petWithSkills->getPet(),
                ActivityHelpers::PetName($pet) . ' daydreamed they were swinging through a magical forest on vermicelli vines, searching for something. They finally reached a clearing, landed on the ground, and began digging at the roots of a giant tree. They grabbed hold of something in the earth, and then snapped back to reality!'
            );

            $this->inventoryService->petCollectsItem('Dirt-covered... Something', $pet, $pet->getName() . ' found this buried at the base of a tree in a daydream.', $log);

            return $log;
        }
        else
        {
            return PetActivityLogFactory::createUnreadLog(
                $this->em,
                $petWithSkills->getPet(),
                ActivityHelpers::PetName($pet) . ' daydreamed they were swinging through a magical forest on vermicelli vines, searching for something... but they lost their grip, fell, and snapped back to reality.'
            );
        }
    }
}