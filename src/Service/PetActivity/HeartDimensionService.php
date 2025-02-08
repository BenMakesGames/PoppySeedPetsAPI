<?php
declare(strict_types=1);

namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetBadgeEnum;
use App\Enum\PetSkillEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ActivityHelpers;
use App\Functions\AdventureMath;
use App\Functions\ArrayFunctions;
use App\Functions\EquipmentFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Functions\StatusEffectHelpers;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;

class HeartDimensionService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly PetExperienceService $petExperienceService,
        private readonly IRandom $rng,
        private readonly EntityManagerInterface $em,
        private readonly UserStatsService $userStatsRepository
    )
    {
    }

    public function canAdventure(Pet $pet): bool
    {
        return $pet->getAffectionAdventures() < $pet->getAffectionLevel();
    }

    public function chanceOfHeartDimensionAdventure(Pet $pet): bool
    {
        if($pet->getAffectionAdventures() + 1 < 6)
            return 80;
        else
            return 10;
    }

    public function notEnoughAffectionAdventure(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(15, 30), PetActivityStatEnum::OTHER, null);

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name%\'s Affection Level must be increased before they can venture into the Heart Dimension again.')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Heart Dimension', 'Adventure!' ]))
        ;

        $this->unequipHeartstone($pet, $activityLog);

        return $activityLog;
    }

    public function adventure(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $changes = new PetChanges($pet);

        $adventure = $pet->getAffectionAdventures() + 1;

        $activityLog = match ($adventure)
        {
            1 => $this->fightAngrySpirit($petWithSkills),
            2 => $this->beInspired($petWithSkills),
            3 => $this->defeatNightmare($petWithSkills),
            4 => $this->haveDivineVision($petWithSkills),
            5 => $this->defeatShadow($petWithSkills),
            6 => $this->unlockTransformingAHeartstone($pet),
            7 => $this->randomAdventure($pet),
            default => throw new \Exception('Ben made a bad error! There is no Heart Dimension adventure that ' . $pet->getName() . ' can go on!'),
        };

        if($adventure < 7)
        {
            $activityLog->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY);
            $bugChance1InX = 10;
        }
        else
            $bugChance1InX = 40;

        $activityLog
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Adventure!' ]))
            ->setChanges($changes->compare($pet))
        ;

        if(AdventureMath::petAttractsBug($this->rng, $pet, $bugChance1InX))
            $this->inventoryService->petAttractsRandomBug($pet, 'Heart Beetle');

        return $activityLog;
    }

    private function unequipHeartstone(Pet $pet, PetActivityLog $activityLog)
    {
        $activityLog->setEntry($activityLog->getEntry() . ' %pet:' . $pet->getId() . '.name% put the Heartstone down.');
        EquipmentFunctions::unequipPet($pet);
    }

    public function fightAngrySpirit(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);

        if($pet->getFood() <= 4)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to fight a Demon of Turmoil in the Heart Dimension, but was too hungry.')
                ->setIcon('icons/activity-logs/heart-dimension')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Heart Dimension' ]))
            ;
            $this->unequipHeartstone($pet, $activityLog);
            return $activityLog;
        }
        else if($pet->getSafety() <= 4)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to fight a Demon of Turmoil in the Heart Dimension, but was too afraid.')
                ->setIcon('icons/activity-logs/heart-dimension')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Heart Dimension' ]))
            ;
            $this->unequipHeartstone($pet, $activityLog);
            return $activityLog;
        }
        else if($pet->getLove() <= 4)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to fight a Demon of Turmoil in the Heart Dimension, but was too lonely.')
                ->setIcon('icons/activity-logs/heart-dimension')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Heart Dimension' ]))
            ;
            $this->unequipHeartstone($pet, $activityLog);
            return $activityLog;
        }
        else if($pet->getEsteem() <= 4)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to fight a Demon of Turmoil in the Heart Dimension, but doubted their self.')
                ->setIcon('icons/activity-logs/heart-dimension')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Heart Dimension' ]))
            ;
            $this->unequipHeartstone($pet, $activityLog);
            return $activityLog;
        }

        $pet->incrementAffectionAdventures();
        $pet
            ->increaseSafety(999)
            ->increaseLove(999)
            ->increaseEsteem(999)
        ;

        return PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% defeated a Demon of Turmoil in the Heart Dimension.')
            ->setIcon('icons/activity-logs/heart-dimension')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Heart Dimension' ]))
        ;
    }

    public function beInspired(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);

        if($pet->getFood() <= 4)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to relax in the Heart Dimension, but was too hungry.')
                ->setIcon('icons/activity-logs/heart-dimension')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Heart Dimension' ]))
            ;
            $this->unequipHeartstone($pet, $activityLog);
            return $activityLog;
        }
        else if($pet->getSafety() <= 4)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to relax in the Heart Dimension, but was too afraid.')
                ->setIcon('icons/activity-logs/heart-dimension')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Heart Dimension' ]))
            ;
            $this->unequipHeartstone($pet, $activityLog);
            return $activityLog;
        }
        else if($pet->getLove() <= 4)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to relax in the Heart Dimension, but was too lonely.')
                ->setIcon('icons/activity-logs/heart-dimension')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Heart Dimension' ]))
            ;
            $this->unequipHeartstone($pet, $activityLog);
            return $activityLog;
        }
        else if($pet->getEsteem() <= 4)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to relax in the Heart Dimension, but doubted their self.')
                ->setIcon('icons/activity-logs/heart-dimension')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Heart Dimension' ]))
            ;
            $this->unequipHeartstone($pet, $activityLog);
            return $activityLog;
        }

        $pet->incrementAffectionAdventures();

        StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::INSPIRED, 24 * 60);

        return PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% relaxed for a while in the Heart Dimension, and became Inspired.')
            ->setIcon('icons/activity-logs/heart-dimension')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Heart Dimension' ]))
        ;
    }

    public function defeatNightmare(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);

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

        return PetActivityLogFactory::createUnreadLog($this->em, $pet, $message)
            ->setIcon('icons/activity-logs/heart-dimension')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Heart Dimension' ]))
        ;
    }

    public function haveDivineVision(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);

        $pet->incrementAffectionAdventures();

        StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::INSPIRED, 24 * 60);

        $figure = $this->rng->rngNextFromArray([
            [ 'the First Vampire', [ '; it was really scary!', ', but it was oddly calming...' ]],
            [ 'Gizubi and Kaera', [ '. They were angry at one another...', '. They looked happy...' ] ],
            [ 'Kundrav and Keresaspa', [ '. They were fighting, and it was really scary!', '. They were fighting, and it was really cool!' ] ],
            [ 'a jumbled picture of Hahanu', [ '. It seemed angry, somehow...', '. It seemed happy, somehow...' ] ],
            [ 'the Fairy Kingdom', [ ' shrouded in darkness.', ' shining beautifully!' ] ],
            [ 'Sharuminyinka and Tig', [ '. It was really sad...', '. It was really hopeful!' ] ],
            [ 'a cavern filled with gold and gems', [ ', and something dangerous lurking in the shadows...', '! So much treasure waiting to be found!' ] ],
        ]);

        $goodOrBad = $this->rng->rngNextInt(0, 1);

        $description = $figure[1][$goodOrBad];

        if($goodOrBad === 0)
            $pet->increaseSafety(-8);
        else
            $pet->increaseSafety(8);

        $message = 'In the Heart Dimension, ' . $pet->getName() . ' saw a vision of ' . $figure[0] . $description;

        return PetActivityLogFactory::createUnreadLog($this->em, $pet, $message)
            ->setIcon('icons/activity-logs/heart-dimension')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Heart Dimension' ]))
        ;
    }

    public function defeatShadow(ComputedPetSkills $petWithSkills): PetActivityLog
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

        $doIt = ArrayFunctions::max($stats, fn($v) => $v['value']);

        $message = $pet->getName() . ' saw their cursed reflection in a cracked mirror! ' . $doIt['message'];

        $pet
            ->increaseSelfReflectionPoint(1)
            ->incrementAffectionAdventures()
        ;

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $message)
            ->setIcon('icons/activity-logs/heart-dimension')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Heart Dimension' ]))
        ;

        $this->petExperienceService->gainExp($pet, 3, [ $doIt['stat'] ], $activityLog);

        return $activityLog;
    }

    private function unlockTransformingAHeartstone(Pet $pet): PetActivityLog
    {
        $pet->incrementAffectionAdventures();

        $message = ActivityHelpers::PetName($pet) . ' made one last trip to the Heart Dimensions, navigating the maze surrounding its core, and reaching the center. The maze shattered, and ' . ActivityHelpers::PetName($pet) . ' awoke. (You can now transform an additional Heartstone!)';

        $this->userStatsRepository->incrementStat($pet->getOwner(), 'Pet Completed the Heartstone Dimension');

        EquipmentFunctions::unequipPet($pet);

        $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, $message)
            ->setIcon('icons/activity-logs/heart-dimension')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Heart Dimension' ]))
        ;

        PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::COMPLETED_HEART_DIMENSION, $log);

        return $log;
    }

    private function randomAdventure(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);

        return match($this->rng->rngNextInt(1, 3))
        {
            1 => $this->doCelestePlusShortHike($pet),
            2 => $this->doBalatro($pet),
            3 => $this->doEverything($pet),
        };
    }

    private function doCelestePlusShortHike(Pet $pet): PetActivityLog
    {
        $message = ActivityHelpers::PetName($pet) . ' dreamed about the Heart Dimensions. They were climbing a twisted mountain surrounded by fierce winds. But they were helped by friendly animals that lived there, and eventually reached the top.';

        $pet->increaseEsteem(4);

        return PetActivityLogFactory::createUnreadLog($this->em, $pet, $message)
            ->setIcon('icons/activity-logs/heart-dimension')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Heart Dimension' ]))
        ;
    }

    private function doBalatro(Pet $pet): PetActivityLog
    {
        $message = ActivityHelpers::PetName($pet) . ' dreamed about the Heart Dimensions. They played a strange game of cards against an unseen opponent, and barely managed to win. When they awoke, ' . ActivityHelpers::PetName($pet) . ' was holding an Ace of Hearts.';

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $message)
            ->setIcon('icons/activity-logs/heart-dimension')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Heart Dimension' ]));

        $this->inventoryService->petCollectsItem('Ace of Hearts', $pet, $pet->getName() . ' dreamed they defeated an unseen opponent in the Heart Dimensions at a game of cards, and awoke holding this.', $activityLog);

        return $activityLog;
    }

    private function doEverything(Pet $pet): PetActivityLog
    {
        $message = ActivityHelpers::PetName($pet) . ' dreamed about the Heart Dimensions. They dreamed of being a flower, a forest, a grain of sand carried by the wind, a moose, a moon, a galaxy, an atom of oxygen... when they awoke, they felt simultaneously Tired, and Inspired.';

        StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::INSPIRED, 3 * 60);
        StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::TIRED, 3 * 60);

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $message)
            ->setIcon('icons/activity-logs/heart-dimension')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Heart Dimension' ]));

        return $activityLog;
    }

}
