<?php

namespace App\Service;

use App\Entity\MonthlyStoryAdventureStep;
use App\Entity\Pet;
use App\Entity\User;
use App\Entity\UserMonthlyStoryAdventureStepCompleted;
use App\Enum\LocationEnum;
use App\Enum\StoryAdventureTypeEnum;
use App\Functions\ArrayFunctions;
use App\Model\ComputedPetSkills;
use App\Model\MonthlyStoryAdventure\AdventureResult;
use App\Repository\MonthlyStoryAdventureStepRepository;
use App\Repository\UserMonthlyStoryAdventureStepCompletedRepository;
use Doctrine\ORM\EntityManagerInterface;

class MonthlyStoryAdventureService
{
    private MonthlyStoryAdventureStepRepository $monthlyStoryAdventureStepRepository;
    private UserMonthlyStoryAdventureStepCompletedRepository $userMonthlyStoryAdventureStepCompletedRepository;
    private InventoryService $inventoryService;
    private EntityManagerInterface $em;

    public function __construct(
        MonthlyStoryAdventureStepRepository $monthlyStoryAdventureStepRepository,
        UserMonthlyStoryAdventureStepCompletedRepository $userMonthlyStoryAdventureStepCompletedRepository,
        InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        $this->monthlyStoryAdventureStepRepository = $monthlyStoryAdventureStepRepository;
        $this->userMonthlyStoryAdventureStepCompletedRepository = $userMonthlyStoryAdventureStepCompletedRepository;
        $this->inventoryService = $inventoryService;
        $this->em = $em;
    }

    public function isStepCompleted(User $user, MonthlyStoryAdventureStep $step): bool
    {
        $completedStep = $this->userMonthlyStoryAdventureStepCompletedRepository->createQueryBuilder('c')
            ->andWhere('c.user=:user', $user->getId())
            ->andWhere('c.adventureStep=:adventureStep', $step->getId())
            ->getQuery()
            ->getSingleResult();

        return $completedStep != null;
    }

    public function isPreviousStepCompleted(User $user, MonthlyStoryAdventureStep $step): bool
    {
        $previousStep = $this->monthlyStoryAdventureStepRepository->createQueryBuilder('s')
            ->andWhere('s.step=:step')
            ->andWhere('s.adventure=:adventure')
            ->setParameter('step', $step->getPreviousStep())
            ->setParameter('adventure', $step->getAdventure()->getId())
            ->getQuery()
            ->getSingleResult()
        ;

        if(!$previousStep)
            throw new \Exception('Ben has made a terrible error: one of the story adventure steps could not be found. And it totally should have been.');

        return $this->isStepCompleted($user, $previousStep);
    }

    /**
     * @param Pet[] $pets
     */
    public function completeStep(User $user, MonthlyStoryAdventureStep $step, array $pets)
    {
        $petSkills = array_map(fn(Pet $pet) => $pet->getComputedSkills(), $pets);

        switch($step->getType())
        {
            case StoryAdventureTypeEnum::COLLECT_STONE:
                $results = $this->doCollectStone($user, $step, $petSkills);
                break;

            case StoryAdventureTypeEnum::GATHER:
                $results = $this->doGather($user, $step, $petSkills);
                break;

            case StoryAdventureTypeEnum::HUNT:
                $results = $this->doHunt($user, $step, $petSkills);
                break;

            case StoryAdventureTypeEnum::MINE_GOLD:
                $results = $this->doMineGold($user, $step, $petSkills);
                break;

            case StoryAdventureTypeEnum::RANDOM_RECRUIT:
                $results = $this->doRandomRecruit($user, $step, $petSkills);
                break;

            case StoryAdventureTypeEnum::STORY:
                $results = $this->doStory($user, $step, $petSkills);
                break;

            case StoryAdventureTypeEnum::TREASURE_HUNT:
                $results = $this->doTreasureHunt($user, $step, $petSkills);
                break;

            case StoryAdventureTypeEnum::WANDERING_MONSTER:
                $results = $this->doWanderingMonster($user, $step, $petSkills);
                break;

            default:
                throw new \Exception('Oh, dang: Ben forgot to implement this story adventure type! :(');
        }

        foreach($results->loot as $item)
            $this->inventoryService->receiveItem($item, $user, $user, '%user:' . $user->getId() . '.Name% gave this to their pets during a game of ★Kindred.', LocationEnum::HOME);

        $this->markStepComplete($user, $step);

        return $results->text;
    }

    private function markStepComplete(User $user, MonthlyStoryAdventureStep $step)
    {
        $completedStep = (new UserMonthlyStoryAdventureStepCompleted())
            ->setUser($user)
            ->setAdventureStep($step)
            ->setCompletedOn(new \DateTimeImmutable())
        ;

        $this->em->persist($completedStep);
    }

    private function getFixedLoot(MonthlyStoryAdventureStep $step): array
    {
        if(!$step->getTreasure())
            return [];

        switch($step->getTreasure())
        {
            case 'GoldChest': return []; // TODO
            case 'BigBasicChest': return []; // TODO
            case 'CupOfLife': return []; // TODO
            case 'TwilightChest': return []; // TODO
            case 'TreasureMap': return [ 'Piece of Cetgueli\'s Map' ];
            case 'WrappedSword': return [ 'Wrapped Sword' ];
            case 'RubyChest': return []; // TODO
            case 'BoxOfOres': return [ 'Box of Ores' ];
            case 'CrystallizedQuint': return []; // TODO
            case 'Ship': return [ 'Paper Boat' ];
            case 'SkeletalRemains': return[]; // TODO
            case 'BlackFlag': return [ 'Black Flag' ];
            case 'ShalurianLighthouse': return []; // TODO
            case 'Rainbow': return [ 'Rainbow' ]; // TODO

            case 'SmallMushrooms':
            case 'LargeMushroom':
                return [ 'Toadstool' ];
        }
    }


    /**
     * @param ComputedPetSkills[] $pets
     */
    private function doCollectStone(User $user, MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        $text = $step->getNarrative() ?? '';
        $loot = $this->getFixedLoot($step);

        $totalSkill = ArrayFunctions::sum($pets, fn(ComputedPetSkills $pet) => $pet->getStrength() + $pet->getStamina() + $pet->getPerception() + $pet->getGatheringBonus());
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    private function doGather(User $user, MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        $text = $step->getNarrative() ?? '';
        $loot = $this->getFixedLoot($step);

        $totalSkill = ArrayFunctions::sum($pets, fn(ComputedPetSkills $pet) => $pet->getDexterity() + $pet->getNature() + $pet->getGatheringBonus());
        // TODO: get basic fruits & veggies & wheat & stuff based on pet skills

    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    private function doHunt(User $user, MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        $text = $step->getNarrative() ?? '';
        $loot = $this->getFixedLoot($step);

        $loot[] = 'Monster Box';
        // TODO: get animal products based on pet skills

        $totalSkill = ArrayFunctions::sum($pets, fn(ComputedPetSkills $pet) => ceil(($pet->getStrength() + $pet->getDexterity()) / 2) + $pet->getBrawl());
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    private function doMineGold(User $user, MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        $text = $step->getNarrative() ?? '';
        $loot = $this->getFixedLoot($step);

        // TODO: get gold (and a few other metals) based on pet skills
        $totalSkill = ArrayFunctions::sum($pets, fn(ComputedPetSkills $pet) => ceil(($pet->getStrength() + $pet->getStamina()) / 2) + $pet->getNature() + $pet->getGatheringBonus());

    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    private function doRandomRecruit(User $user, MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        $text = $step->getNarrative() ?? '';
        $loot = $this->getFixedLoot($step);

        // TODO: get a random plushy (give it a name in the adventure text - FOR CUTENESS! :P)

    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    private function doStory(User $user, MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        return new AdventureResult($step->getNarrative() ?? '', $this->getFixedLoot($step));
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    private function doTreasureHunt(User $user, MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        return new AdventureResult($step->getNarrative() ?? '', $this->getFixedLoot($step));
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    private function doWanderingMonster(User $user, MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        $text = $step->getNarrative() ?? '';
        $loot = $this->getFixedLoot($step);
        $loot[] = 'Monster Box';

        $totalSkill = ArrayFunctions::sum($pets, fn(ComputedPetSkills $pet) => ceil(($pet->getStrength() + $pet->getDexterity()) / 2) + $pet->getBrawl());
        // TODO: get animal products based on pet skills
    }
}