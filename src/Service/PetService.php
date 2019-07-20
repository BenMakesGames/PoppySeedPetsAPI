<?php
namespace App\Service;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\FlavorEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Model\PetChanges;
use App\Repository\InventoryRepository;
use App\Repository\UserStatsRepository;
use App\Service\PetActivity\CraftingService;
use App\Service\PetActivity\FishingService;
use App\Service\PetActivity\GatheringService;
use App\Service\PetActivity\GenericAdventureService;
use App\Service\PetActivity\HuntingService;
use App\Service\PetActivity\TreasureMapService;
use Doctrine\ORM\EntityManagerInterface;

class PetService
{
    private $em;
    private $randomService;
    private $responseService;
    private $fishingService;
    private $huntingService;
    private $gatheringService;
    private $craftingService;
    private $userStatsRepository;
    private $inventoryRepository;
    private $treasureMapService;
    private $genericAdventureService;

    public function __construct(
        EntityManagerInterface $em, RandomService $randomService, ResponseService $responseService,
        FishingService $fishingService, HuntingService $huntingService, GatheringService $gatheringService,
        CraftingService $craftingService, UserStatsRepository $userStatsRepository, InventoryRepository $inventoryRepository,
        TreasureMapService $treasureMapService, GenericAdventureService $genericAdventureService
    )
    {
        $this->em = $em;
        $this->randomService = $randomService;
        $this->responseService = $responseService;
        $this->fishingService = $fishingService;
        $this->huntingService = $huntingService;
        $this->gatheringService = $gatheringService;
        $this->craftingService = $craftingService;
        $this->userStatsRepository = $userStatsRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->treasureMapService = $treasureMapService;
        $this->genericAdventureService = $genericAdventureService;
    }

    /**
     * @param string[] $stats
     */
    public function gainExp(Pet $pet, int $exp, array $stats)
    {
        if($exp === 0) return;

        $possibleStats = array_filter($stats, function($stat) use($pet) {
            return ($pet->{'get' . $stat}() < 20);
        });

        if(count($possibleStats) === 0) return;

        $divideBy = 1;

        if($pet->getFood() + $pet->getWhack() - $pet->getJunk() < 0) $divideBy++;
        if($pet->getSafety() + $pet->getWhack() < 0) $divideBy++;
        if($pet->getLove() + $pet->getWhack() < 0) $divideBy++;
        if($pet->getEsteem() + $pet->getWhack() < 0) $divideBy++;

        $divideBy += $pet->getWhack() / $pet->getStomachSize();

        $exp = \ceil($exp / $divideBy);

        if($exp === 0) return;

        $pet->increaseExperience($exp);

        while($pet->getExperience() >= $pet->getExperienceToLevel())
        {
            $pet->decreaseExperience($pet->getExperienceToLevel());
            $pet->getSkills()->increaseStat(ArrayFunctions::pick_one($possibleStats));
        }
    }


    public function doPet(Pet $pet)
    {
        $now = new \DateTimeImmutable();

        if($pet->getIsDead())
            throw new \InvalidArgumentException($pet->getName() . ' is dead :|');

        if($pet->getLastInteracted() < $now->modify('-48 hours'))
            $pet->setLastInteracted($now->modify('-20 hours'));
        else if($pet->getLastInteracted() < $now->modify('-20 hours'))
            $pet->setLastInteracted($now->modify('-4 hours'));
        else if($pet->getLastInteracted() < $now->modify('-4 hours'))
            $pet->setLastInteracted($now);
        else
            throw new \InvalidArgumentException('You\'ve already interacted with this pet recently.');

        $changes = new PetChanges($pet);

        $pet->increaseSafety(7);
        $pet->increaseLove(7);

        $this->responseService->createActivityLog($pet, 'You pet ' . $pet->getName(). '.', $changes->compare($pet));
        $this->userStatsRepository->incrementStat($pet->getOwner(), UserStatEnum::PETTED_A_PET);
    }

    public function doPraise(Pet $pet)
    {
        $now = new \DateTimeImmutable();

        if($pet->getIsDead())
            throw new \InvalidArgumentException($pet->getName() . ' is dead :|');

        if($pet->getLastInteracted() < $now->modify('-48 hours'))
            $pet->setLastInteracted($now->modify('-20 hours'));
        else if($pet->getLastInteracted() < $now->modify('-20 hours'))
            $pet->setLastInteracted($now->modify('-4 hours'));
        else if($pet->getLastInteracted() < $now->modify('-4 hours'))
            $pet->setLastInteracted($now);
        else
            throw new \InvalidArgumentException('You\'ve already interacted with this pet recently.');

        $changes = new PetChanges($pet);

        $pet->increaseLove(7);
        $pet->increaseEsteem(7);

        $this->responseService->createActivityLog($pet, 'You praised ' . $pet->getName(). '.', $changes->compare($pet));
        $this->userStatsRepository->incrementStat($pet->getOwner(), UserStatEnum::PRAISED_A_PET);
    }

    /**
     * @param Inventory[] $inventory
     */
    public function doFeed(Pet $pet, array $inventory): PetActivityLog
    {
        if($pet->getIsDead())
            throw new \InvalidArgumentException($pet->getName() . ' is dead :|');

        if(ArrayFunctions::any($inventory, function(Inventory $i) { return $i->getItem()->getFood() === null; }))
            throw new \InvalidArgumentException('At least one of the items selected is not edible!');

        \shuffle($inventory);

        $petChanges = new PetChanges($pet);
        $foodsEaten = [];
        $favorites = [];

        foreach($inventory as $i)
        {
            $food = $i->getItem()->getFood();

            $pet->increaseWhack($food->getWhack());
            $pet->increaseFood($food->getFood());
            $pet->increaseJunk($food->getJunk());

            // consider favorite flavor:
            if(!FlavorEnum::isAValue($pet->getFavoriteFlavor()))
                throw new \Exception('pet\'s favorite flavor is invalid');

            $favoriteFlavorStrength = $food->{'get' . $pet->getFavoriteFlavor()}();

            if($favoriteFlavorStrength > 0)
            {
                $pet->increaseLove($favoriteFlavorStrength + $food->getLove());
                $pet->increaseEsteem($favoriteFlavorStrength + $food->getLove());
                $favorites[] = $i->getItem();
            }

            $this->em->remove($i);

            $foodsEaten[] = $i->getItem()->getName();

            if($pet->getJunk() + $pet->getWhack() + $pet->getFood() >= $pet->getStomachSize())
                break;
        }

        // gain love & safety equal to 1/8 food gained, when hand-fed
        $foodGained = $pet->getFood() - $petChanges->food;

        if($foodGained > 0)
        {
            $remainder = $foodGained % 8;
            $gain = floor($foodGained / 8);

            if ($remainder > 0 && \mt_rand(1, 8) <= $remainder)
                $gain++;

            $pet->increaseSafety($gain);
        }


        $this->userStatsRepository->incrementStat($pet->getOwner(), UserStatEnum::FOOD_HOURS_FED_TO_PETS, $foodGained);

        if(count($favorites) > 0)
            return $this->responseService->createActivityLog($pet, 'You fed ' . $pet->getName() . ' ' . ArrayFunctions::list_nice($foodsEaten) . '. ' . $pet->getName() . ' really liked the ' . ArrayFunctions::pick_one($favorites)->getName() . '!', $petChanges->compare($pet));
        else
            return $this->responseService->createActivityLog($pet, 'You fed ' . $pet->getName() . ' ' . ArrayFunctions::list_nice($foodsEaten) . '.', $petChanges->compare($pet));
    }

    public function doEat(Pet $pet, Item $item)
    {
        $petChanges = new PetChanges($pet);

        $food = $item->getFood();

        $pet->increaseWhack($food->getWhack());
        $pet->increaseFood($food->getFood());
        $pet->increaseJunk($food->getJunk());

        // consider favorite flavor:
        if(!FlavorEnum::isAValue($pet->getFavoriteFlavor()))
            throw new \Exception('pet\'s favorite flavor is invalid');

        $favoriteFlavorStrength = $food->{'get' . $pet->getFavoriteFlavor()}();

        $pet->increaseEsteem($favoriteFlavorStrength + $food->getLove());

        $this->responseService->createActivityLog($pet, $pet->getName() . ' immediately ate the ' . $item->getName() . '.', $petChanges->compare($pet));

    }

    public function runHour(Pet $pet)
    {
        if($pet->getTime() < 60)
            throw new \InvalidArgumentException('Pet does not have enough Time.');

        $pet->increaseFood(-1);

        if($pet->getJunk() > 0)
            $pet->increaseJunk(-1);

        if($pet->getWhack() > 0)
            $pet->increaseWhack(-1);

        if($pet->getSafety() > 0 && mt_rand(1, 2) === 1)
            $pet->increaseSafety(-1);
        else if($pet->getSafety() < 0)
            $pet->increaseSafety(1);

        if($pet->getLove() > 0 && mt_rand(1, 2) === 1)
            $pet->increaseLove(-1);
        else if($pet->getLove() < 0 && mt_rand(1, 2) === 1)
            $pet->increaseLove(1);

        if($pet->getEsteem() > 0)
            $pet->increaseEsteem(-1);
        else if($pet->getEsteem() < 0 && mt_rand(1, 2) === 1)
            $pet->increaseEsteem(1);

        if($pet->getWhack() > 0 || $pet->getJunk() > 0)
        {
            if($this->calculateAgeInDays($pet) > 365 * 2)
            {
                // elderly tolerance
                $junkDie = 8;
                $whackDie = 12;
            }
            else if($this->calculateAgeInDays($pet) > 365)
            {
                // adult tolerance
                $junkDie = 12;
                $whackDie = 20;
            }
            else if($this->calculateAgeInDays($pet) > 365 / 2)
            {
                // young adult tolerance
                $junkDie = 20;
                $whackDie = 10;
            }
            else
            {
                // kid tolerance
                $junkDie = 12;
                $whackDie = 6;
            }

            if($this->randomService->roll(1, $whackDie) + $this->randomService->roll(1, $junkDie) < $pet->getWhack() + $pet->getJunk())
            {
                $changes = new PetChanges($pet);

                $pet->increaseWhack(-mt_rand(1, max(1, $pet->getWhack() / 2)));
                $pet->increaseJunk(-mt_rand(1, max(1, $pet->getJunk() / 2)));
                $pet->increaseFood(-mt_rand(1, max(1, $pet->getFood() / 2)));

                $pet->increaseSafety(-round(mt_rand(1, max(1, $pet->getWhack() + $pet->getJunk()) / 2)));
                $pet->increaseEsteem(-round(mt_rand(1, max(1, $pet->getWhack() + $pet->getJunk()) / 2)));

                $pet->spendTime(\mt_rand(15, 45));

                $this->responseService->createActivityLog($pet, $pet->getName() . ' threw up :(', $changes->compare($pet));

                return;
            }

            if($this->randomService->roll(1, 12) < $pet->getWhack())
            {
                $this->responseService->createActivityLog($pet, $pet->getName() . ' is feeling loopy, so took some time to rest.');
                $pet->spendTime(\mt_rand(45, 75));

                return;
            }

            if($this->randomService->roll(1, $junkDie) < $pet->getJunk())
            {
                $this->responseService->createActivityLog($pet, $pet->getName() . ' couldn\'t muster the energy to do anything.');
                $pet->spendTime(\mt_rand(45, 75));

                return;
            }
        }

        $eatDesire = $pet->getStomachSize() / 2 - $pet->getFood();

        if(\mt_rand(1, $pet->getStomachSize()) <= $eatDesire)
        {

        }

        $itemsInHouse = (int)$this->inventoryRepository->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->andWhere('i.owner=:user')
            ->setParameter('user', $pet->getOwner())
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $craftingPossibilities = $this->craftingService->getCraftingPossibilities($pet);

        $houseTooFull = \mt_rand(1, 10) > $pet->getOwner()->getMaxInventory() - $itemsInHouse;

        if($houseTooFull)
        {
            if($itemsInHouse >= $pet->getOwner()->getMaxInventory())
                $description = 'The house is crazy-full.';
            else
                $description = 'The house is getting pretty full.';

            if(count($craftingPossibilities) === 0)
            {
                $pet->spendTime(\mt_rand(45, 60));

                $this->responseService->createActivityLog($pet, $description . ' ' . $pet->getName() . ' wanted to make something, but couldn\'t find any materials to work with.');
            }
            else
            {
                $activityLog = $this->craftingService->adventure($pet, $craftingPossibilities);
                $activityLog->setEntry($description . ' ' . $activityLog->getEntry());
            }

            return;
        }

        if(mt_rand(1, 50) === 1)
        {
            $this->genericAdventureService->adventure($pet);
            return;
        }

        if($pet->getTool() && $pet->getTool()->getItem()->getName() === 'Cetgueli\'s Treasure Map')
        {
            $this->treasureMapService->doCetguelisTreasureMap($pet);
            return;
        }

        $petDesires = [
            'fish' => $this->generateFishingDesire($pet),
            'hunt' => $this->generateMonsterHuntingDesire($pet),
            'gather' => $this->generateGatheringDesire($pet),
        ];

        if(count($craftingPossibilities) > 0)
            $petDesires['craft'] = $this->generateCraftingDesire($pet);

        $desire = $this->pickDesire($petDesires);

        switch($desire)
        {
            case 'fish': $this->fishingService->adventure($pet); break;
            case 'hunt': $this->huntingService->adventure($pet); break;
            case 'gather': $this->gatheringService->adventure($pet); break;
            case 'craft': $this->craftingService->adventure($pet, $craftingPossibilities); break;
            default: $this->doNothing($pet); break;
        }
    }

    private function doNothing(Pet $pet)
    {
        $pet->spendTime(\mt_rand(30, 60));
        $this->responseService->createActivityLog($pet, $pet->getName() . ' hung around the house.');
    }

    private function pickDesire(array $petDesires)
    {
        $totalDesire = \array_sum($petDesires);

        $pick = mt_rand(0, $totalDesire - 1);

        foreach($petDesires as $action=>$desire)
        {
            if($pick < $desire)
                return $action;

            $pick -= $desire;
        }

        return array_key_last($petDesires);
    }

    public function calculateAgeInDays(Pet $pet)
    {
        return (new \DateTimeImmutable())->diff($pet->getBirthDate())->days;
    }

    public function generateFishingDesire(Pet $pet): int
    {
        $desire = $pet->getSkills()->getDexterity() + $pet->getSkills()->getNature() + \mt_rand(1, 4);

        return max(1, round($desire * (1 + \mt_rand(-10, 10) / 100)));
    }

    public function generateMonsterHuntingDesire(Pet $pet): int
    {
        $desire = $pet->getSkills()->getStrength() + $pet->getSkills()->getBrawl() + \mt_rand(1, 4);

        return max(1, round($desire * (1 + \mt_rand(-10, 10) / 100)));
    }

    public function generateCraftingDesire(Pet $pet): int
    {
        $desire = $pet->getSkills()->getIntelligence() + $pet->getSkills()->getCrafts() + \mt_rand(1, 4);

        return max(1, round($desire * (1 + \mt_rand(-10, 10) / 100)));
    }

    public function generateGatheringDesire(Pet $pet): int
    {
        $desire = $pet->getSkills()->getPerception() + $pet->getSkills()->getNature() + \mt_rand(1, 4);

        return max(1, round($desire * (1 + \mt_rand(-10, 10) / 100)));
    }
}