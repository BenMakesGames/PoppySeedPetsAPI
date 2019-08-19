<?php
namespace App\Service;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetRelationship;
use App\Enum\FlavorEnum;
use App\Enum\MeritEnum;
use App\Enum\SpiritCompanionStarEnum;
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
use App\Service\PetActivity\ProgrammingService;
use App\Service\PetActivity\Protocol7Service;
use App\Service\PetActivity\TreasureMapService;
use Doctrine\ORM\EntityManagerInterface;

class PetService
{
    private $em;
    private $randomService;
    private $responseService;
    private $petRelationshipService;
    private $fishingService;
    private $huntingService;
    private $gatheringService;
    private $craftingService;
    private $programmingService;
    private $userStatsRepository;
    private $inventoryRepository;
    private $treasureMapService;
    private $genericAdventureService;
    private $protocol7Service;

    public function __construct(
        EntityManagerInterface $em, RandomService $randomService, ResponseService $responseService,
        PetRelationshipService $petRelationshipService,
        FishingService $fishingService, HuntingService $huntingService, GatheringService $gatheringService,
        CraftingService $craftingService, UserStatsRepository $userStatsRepository, InventoryRepository $inventoryRepository,
        TreasureMapService $treasureMapService, GenericAdventureService $genericAdventureService,
        Protocol7Service $protocol7Service, ProgrammingService $programmingService
    )
    {
        $this->em = $em;
        $this->randomService = $randomService;
        $this->responseService = $responseService;
        $this->petRelationshipService = $petRelationshipService;
        $this->fishingService = $fishingService;
        $this->huntingService = $huntingService;
        $this->gatheringService = $gatheringService;
        $this->craftingService = $craftingService;
        $this->userStatsRepository = $userStatsRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->treasureMapService = $treasureMapService;
        $this->genericAdventureService = $genericAdventureService;
        $this->protocol7Service = $protocol7Service;
        $this->programmingService = $programmingService;
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

        if($pet->getFood() + $pet->getAlcohol() < 0) $divideBy++;
        if($pet->getSafety() + $pet->getAlcohol() < 0) $divideBy++;
        if($pet->getLove() + $pet->getAlcohol() < 0) $divideBy++;
        if($pet->getEsteem() + $pet->getAlcohol() < 0) $divideBy++;

        $divideBy += 1 + ($pet->getAlcohol() / $pet->getStomachSize());

        $exp = \ceil($exp / $divideBy);

        if($exp === 0) return;

        $pet->increaseExperience($exp);

        while($pet->getExperience() >= $pet->getExperienceToLevel())
        {
            $pet->decreaseExperience($pet->getExperienceToLevel());
            $pet->getSkills()->increaseStat(ArrayFunctions::pick_one($possibleStats));
        }
    }

    /**
     * @param string[] $stats
     */
    public function gainAffection(Pet $pet, int $points)
    {
        if($points === 0) return;

        $divideBy = 1;

        if($pet->getFood() + $pet->getAlcohol() < 0) $divideBy++;
        if($pet->getSafety() + $pet->getAlcohol() < 0) $divideBy++;
        if($pet->getLove() + $pet->getAlcohol() < 0) $divideBy++;
        if($pet->getEsteem() + $pet->getAlcohol() < 0) $divideBy++;

        $points = \ceil($points / $divideBy);

        if($points === 0) return;

        $previousAffectionLevel = $pet->getAffectionLevel();

        $pet->increaseAffectionPoints($points);

        // if a pet's affection level increased, and you haven't unlocked the park, now you get the park!
        if($pet->getAffectionLevel() > $previousAffectionLevel && $pet->getOwner()->getUnlockedPark() === null)
            $pet->getOwner()->setUnlockedPark();
    }

    public function doPet(Pet $pet)
    {
        $now = new \DateTimeImmutable();

        $changes = new PetChanges($pet);

        if($pet->getLastInteracted() < $now->modify('-48 hours'))
        {
            $pet->setLastInteracted($now->modify('-20 hours'));
            $pet->increaseSafety(15);
            $pet->increaseLove(15);
            $this->gainAffection($pet, 10);
        }
        else if($pet->getLastInteracted() < $now->modify('-20 hours'))
        {
            $pet->setLastInteracted($now->modify('-4 hours'));
            $pet->increaseSafety(10);
            $pet->increaseLove(10);
            $this->gainAffection($pet, 5);
        }
        else if($pet->getLastInteracted() < $now->modify('-4 hours'))
        {
            $pet->setLastInteracted($now);
            $pet->increaseSafety(7);
            $pet->increaseLove(7);
            $this->gainAffection($pet, 1);
        }
        else
            throw new \InvalidArgumentException('You\'ve already interacted with this pet recently.');

        $this->responseService->createActivityLog($pet, 'You pet ' . $pet->getName(). '.', 'ui/affection', $changes->compare($pet));
        $this->userStatsRepository->incrementStat($pet->getOwner(), UserStatEnum::PETTED_A_PET);
    }

    public function doPraise(Pet $pet)
    {
        $now = new \DateTimeImmutable();

        $changes = new PetChanges($pet);

        if($pet->getLastInteracted() < $now->modify('-48 hours'))
        {
            $pet->setLastInteracted($now->modify('-20 hours'));
            $pet->increaseLove(15);
            $pet->increaseEsteem(15);
            $this->gainAffection($pet, 10);
        }
        else if($pet->getLastInteracted() < $now->modify('-20 hours'))
        {
            $pet->setLastInteracted($now->modify('-4 hours'));
            $pet->increaseLove(10);
            $pet->increaseEsteem(10);
            $this->gainAffection($pet, 5);
        }
        else if($pet->getLastInteracted() < $now->modify('-4 hours'))
        {
            $pet->setLastInteracted($now);
            $pet->increaseLove(7);
            $pet->increaseEsteem(7);
            $this->gainAffection($pet, 1);
        }
        else
            throw new \InvalidArgumentException('You\'ve already interacted with this pet recently.');

        $this->responseService->createActivityLog($pet, 'You praised ' . $pet->getName(). '.', 'ui/affection', $changes->compare($pet));
        $this->userStatsRepository->incrementStat($pet->getOwner(), UserStatEnum::PRAISED_A_PET);
    }

    /**
     * @param Inventory[] $inventory
     */
    public function doFeed(Pet $pet, array $inventory): PetActivityLog
    {
        if(ArrayFunctions::any($inventory, function(Inventory $i) { return $i->getItem()->getFood() === null; }))
            throw new \InvalidArgumentException('At least one of the items selected is not edible!');

        \shuffle($inventory);

        $petChanges = new PetChanges($pet);
        $foodsEaten = [];
        $favorites = [];

        foreach($inventory as $i)
        {

            if($pet->getJunk() + $pet->getFood() >= $pet->getStomachSize())
                break;

            $food = $i->getItem()->getFood();

            $pet->increaseAlcohol($food->getAlcohol());
            $pet->increaseFood($food->getFood());
            $pet->increaseJunk($food->getJunk());

            // consider favorite flavor:
            if(!FlavorEnum::isAValue($pet->getFavoriteFlavor()))
                throw new \Exception('pet\'s favorite flavor is invalid');

            $favoriteFlavorStrength = $food->{'get' . $pet->getFavoriteFlavor()}();

            $bonusLoveAndEsteem = $food->getLove() + $favoriteFlavorStrength;

            $pet
                ->increaseLove($bonusLoveAndEsteem)
                ->increaseEsteem($bonusLoveAndEsteem)
            ;

            if($favoriteFlavorStrength > 0)
            {
                $this->gainAffection($pet, $favoriteFlavorStrength);

                $favorites[] = $i->getItem();
            }

            $this->em->remove($i);

            $foodsEaten[] = $i->getItem()->getName();
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
            $this->gainAffection($pet, $gain);

            $this->userStatsRepository->incrementStat($pet->getOwner(), UserStatEnum::FOOD_HOURS_FED_TO_PETS, $foodGained);
        }

        if(count($foodsEaten) > 0)
        {
            if(count($favorites) > 0)
                return $this->responseService->createActivityLog($pet, 'You fed ' . $pet->getName() . ' ' . ArrayFunctions::list_nice($foodsEaten) . '. ' . $pet->getName() . ' really liked the ' . ArrayFunctions::pick_one($favorites)->getName() . '!', 'ui/affection', $petChanges->compare($pet));
            else
                return $this->responseService->createActivityLog($pet, 'You fed ' . $pet->getName() . ' ' . ArrayFunctions::list_nice($foodsEaten) . '.', '', $petChanges->compare($pet));
        }
        else
            return $this->responseService->createActivityLog($pet, 'You tried to feed ' . $pet->getName() . ', but they\'re too full to eat anymore.', '', $petChanges->compare($pet));
    }

    public function doEat(Pet $pet, Item $item, ?PetActivityLog $activityLog)
    {
        $food = $item->getFood();

        $pet->increaseAlcohol($food->getAlcohol());
        $pet->increaseFood($food->getFood());
        $pet->increaseJunk($food->getJunk());

        // consider favorite flavor:
        if(!FlavorEnum::isAValue($pet->getFavoriteFlavor()))
            throw new \Exception('pet\'s favorite flavor is invalid');

        $favoriteFlavorStrength = $food->{'get' . $pet->getFavoriteFlavor()}();

        $pet->increaseEsteem($favoriteFlavorStrength + $food->getLove());

        if($activityLog)
            $activityLog->setEntry($activityLog->getEntry() . ' ' . $pet->getName() . ' immediately ate the ' . $item->getName() . '.');
    }

    public function runHour(Pet $pet)
    {
        if($pet->getTime() < 60)
            throw new \InvalidArgumentException('Pet does not have enough Time.');

        $pet->increaseFood(-1);

        if($pet->getJunk() > 0)
            $pet->increaseJunk(-1);

        if($pet->getAlcohol() > 0)
            $pet->increaseAlcohol(-1);

        if($pet->getCaffeine() > 0)
            $pet->increaseCaffeine(-1);

        if($pet->getPsychedelic() > 0)
            $pet->increasePsychedelic()(-1);

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

        if($pet->getAlcohol() + $pet->getPsychedelic() > 0)
        {
            if($this->randomService->roll(6, 24) < $pet->getAlcohol() + $pet->getPsychedelic())
            {
                $changes = new PetChanges($pet);

                $safetyVom = ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 4);

                if($pet->getAlcohol() > 0) $pet->increaseAlcohol(-mt_rand(1, ceil($pet->getAlcohol() / 2)));
                if($pet->getPsychedelic() > 0) $pet->increasePsychedelic(-mt_rand(1, ceil($pet->getPsychedelic() / 2)));
                if($pet->getJunk() > 0) $pet->increaseJunk(-mt_rand(1, ceil($pet->getJunk() / 2)));
                if($pet->getFood() > 0) $pet->increaseFood(-mt_rand(1, ceil($pet->getFood() / 2)));

                $pet->increaseSafety(-mt_rand(1, $safetyVom));
                $pet->increaseEsteem(-mt_rand(1, $safetyVom));

                $pet->spendTime(\mt_rand(15, 45));

                $this->responseService->createActivityLog($pet, $pet->getName() . ' threw up :(', '', $changes->compare($pet));

                return;
            }
        }

        $eatDesire = $pet->getStomachSize() / 2 - $pet->getFood();

        if(\mt_rand(1, $pet->getStomachSize()) <= $eatDesire)
        {

        }

        if(
            // has food
            $pet->getFood() > 0 &&

            // a random factor
            \mt_rand(1, max(10, 15 + min(5, $pet->getLove()) + min(5, $pet->getSafety()) + min(5, $pet->getEsteem()))) <= 3
        )
        {
            if($this->hangOutWithFriend($pet))
                return;
        }

        if($this->meetRoommates($pet))
        {
            $pet->spendTime(\mt_rand(45, 60));
            return;
        }

        $itemsInHouse = (int)$this->inventoryRepository->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->andWhere('i.owner=:user')
            ->setParameter('user', $pet->getOwner())
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $craftingPossibilities = $this->craftingService->getCraftingPossibilities($pet);
        $programmingPossibilities = $this->programmingService->getCraftingPossibilities($pet);

        $houseTooFull = \mt_rand(1, 10) > $pet->getOwner()->getMaxInventory() - $itemsInHouse;

        if($houseTooFull)
        {
            if($itemsInHouse >= $pet->getOwner()->getMaxInventory())
                $description = 'The house is crazy-full.';
            else
                $description = 'The house is getting pretty full.';

            if(count($craftingPossibilities) === 0 && count($programmingPossibilities) === 0)
            {
                $pet->spendTime(\mt_rand(45, 60));

                $this->responseService->createActivityLog($pet, $description . ' ' . $pet->getName() . ' wanted to make something, but couldn\'t find any materials to work with.', '');
            }
            else
            {
                $possibilities = [];

                if(count($craftingPossibilities) > 0) $possibilities[] = [ $this->craftingService, $craftingPossibilities ];
                if(count($programmingPossibilities) > 0) $possibilities[] = [ $this->programmingService, $programmingPossibilities ];

                $do = ArrayFunctions::pick_one($possibilities);

                $activityLog = $do[0]->adventure($pet, $do[1]);
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

        if($pet->hasMerit(MeritEnum::PROTOCOL_7))
            $petDesires['hack'] = $this->generateHackingDesire($pet);

        if(count($craftingPossibilities) > 0) $petDesires['craft'] = $this->generateCraftingDesire($pet);
        if(count($programmingPossibilities) > 0) $petDesires['program'] = $this->generateProgrammingDesire($pet);

        $desire = $this->pickDesire($petDesires);

        switch($desire)
        {
            case 'fish': $this->fishingService->adventure($pet); break;
            case 'hunt': $this->huntingService->adventure($pet); break;
            case 'gather': $this->gatheringService->adventure($pet); break;
            case 'craft': $this->craftingService->adventure($pet, $craftingPossibilities); break;
            case 'program': $this->programmingService->adventure($pet, $programmingPossibilities); break;
            case 'hack': $this->protocol7Service->adventure($pet); break;
            default: $this->doNothing($pet); break;
        }
    }

    private function hangOutWithFriend(Pet $pet): bool
    {
        /** @var PetRelationship[] $friends */
        $relationships = $pet->getPetRelationships()->filter(function(PetRelationship $p) { return $p->getRelationship()->getFood() > 0; })->toArray();

        if($pet->hasMerit(MeritEnum::SPIRIT_COMPANION))
            $relationships[] = MeritEnum::SPIRIT_COMPANION;

        if(count($relationships) === 0)
            return false;

        /** @var PetRelationship $relationship */
        $relationship = ArrayFunctions::pick_one($relationships);

        if($relationship === MeritEnum::SPIRIT_COMPANION)
            $this->hangOutWithSpiritCompanion($pet);
        else
        {
            $friend = $relationship->getRelationship();

            $friendRelationship = $friend->getPetRelationships()->filter(function(PetRelationship $p) use($pet) { return $p->getRelationship()->getId() === $pet->getId(); })->first();

            if($friendRelationship === false)
            {
                $friendRelationship = (new PetRelationship())
                    ->setRelationship($pet)
                    ->increaseIntimacy(mt_rand(20, 50))
                    ->increaseCommitment(mt_rand(10, 25))
                    ->increasePassion($friend->wouldBang($pet) ? mt_rand(150 + $pet->getWouldBangFraction() * 5, 500 + $pet->getWouldBangFraction() * 20) : mt_rand(0, 20))
                ;

                if($friend->wouldBang($pet))
                    $friendRelationship->setMetDescription($pet->getName() . ' popped by; ' . $friend->getName() . ' had totally noticed them earlier, but was too shy to say anything at the time. (What a cutie!)');
                else
                {
                    switch(mt_rand(1, 3))
                    {
                        case 1:
                            $friendRelationship->setMetDescription($pet->getName() . ' popped by; ' . $friend->getName() . ' had met them earlier, but kind of forgot.');
                            break;
                        case 2:
                            $friendRelationship->setMetDescription($pet->getName() . ' popped by; ' . $friend->getName() . ' had met them earlier, but figured they\'d never see each other again.');
                            break;
                        case 3:
                            $friendRelationship->setMetDescription($pet->getName() . ' popped by; ' . $friend->getName() . ' had met them earlier, but didn\'t think ' . $pet->getName() . ' was interested.');
                            break;
                    }
                }

                $friend->addPetRelationship($friendRelationship);

                $this->em->persist($friendRelationship);
            }

            $this->hangOutWithOtherPet($relationship, $friendRelationship);
        }

        return true;
    }

    private function hangOutWithSpiritCompanion(Pet $pet)
    {
        $changes = new PetChanges($pet);

        $pet->spendTime(\mt_rand(45, 60));

        $companion = $pet->getSpiritCompanion();

        $adjectives = [ 'bizarre', 'impressive', 'surprisingly-graphic', 'whirlwind' ];

        if(mt_rand(1, 3) !== 1 || ($pet->getSafety() > 0 && $pet->getLove() > 0 && $pet->getEsteem() > 0))
        {
            $pet
                ->increaseSafety(mt_rand(2, 4))
                ->increaseLove(mt_rand(2, 4))
                ->increaseEsteem(mt_rand(2, 4))
            ;
            $message = $pet->getName() . ' wasn\'t feeling great, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' listened patiently; in the end, ' . $pet->getName() . ' felt a little better.';
        }
        else if($pet->getSafety() <= 0)
        {
            switch($companion->getStar())
            {
                case SpiritCompanionStarEnum::ALTAIR:
                case SpiritCompanionStarEnum::CEPHEUS:
                    $pet
                        ->increaseSafety(mt_rand(6, 10))
                        ->increaseLove(mt_rand(2, 4))
                    ;
                    $message = $pet->getName() . ' was feeling nervous, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' told a ' . ArrayFunctions::pick_one($adjectives) . ' story about victory in combat, and swore to protect ' . $pet->getName() . '!';
                    break;
                case SpiritCompanionStarEnum::CASSIOPEIA:
                    $pet
                        ->increaseSafety(mt_rand(2, 4))
                    ;
                    $message = $pet->getName() . ' was feeling nervous, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' whispered odd prophecies, then stared at ' . $pet->getName() . ' expectantly. (It\'s the thought that counts...)';
                    break;
                case SpiritCompanionStarEnum::GEMINI:
                    $pet
                        ->increaseSafety(mt_rand(4, 8))
                        ->increaseLove(mt_rand(2, 4))
                        ->increaseEsteem(mt_rand(2, 4))
                    ;
                    $message = $pet->getName() . ' was feeling nervous, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' smiled, and split into multiple copies of itself, each defending ' . $pet->getName() . ' from another angle. They all turned to ' . $pet->getName() . ' and gave a sincere thumbs up before recombining.';
                    break;
                case SpiritCompanionStarEnum::SAGITTARIUS:
                    $pet
                        ->increaseSafety(mt_rand(2, 4))
                        ->increaseLove(mt_rand(2, 4))
                    ;
                    $message = $pet->getName() . ' was feeling nervous, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' tried to distract ' . $pet->getName() . ' with ' . ArrayFunctions::pick_one($adjectives) . ' stories about lavish parties. It kind of worked...';
                    break;
                case SpiritCompanionStarEnum::HYDRA:
                    $pet
                        ->increaseSafety(mt_rand(4, 8))
                        ->increaseLove(mt_rand(4, 8))
                    ;
                    $message = $pet->getName() . ' was feeling nervous, so talked to ' . $companion->getName() . '. Sensing ' . $pet->getName() . '\'s unease, ' . $companion->getName() . ' looked around for potential threats, and roared menacingly.';
                    break;
                default:
                    throw new \Exception('Unknown Spirit Companion Star "' . $companion->getStar() . '"');
            }
        }
        else if($pet->getLove() <= 0)
        {
            switch($companion->getStar())
            {
                case SpiritCompanionStarEnum::ALTAIR:
                case SpiritCompanionStarEnum::CEPHEUS:
                    $pet
                        ->increaseSafety(mt_rand(2, 4))
                        ->increaseLove(mt_rand(2, 4))
                    ;
                    $message = $pet->getName() . ' was feeling lonely, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' rambled some ' . ArrayFunctions::pick_one($adjectives) . ' story about victory in combat... (It\'s the thought that counts...)';
                    break;
                case SpiritCompanionStarEnum::CASSIOPEIA:
                    $pet
                        ->increaseSafety(mt_rand(2, 4))
                        ->increaseLove(mt_rand(2, 4))
                    ;
                    $message = $pet->getName() . ' was feeling lonely, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' whispered odd prophecies, then stared at ' . $pet->getName() . ' expectantly. (It\'s the thought that counts...)';
                    break;
                case SpiritCompanionStarEnum::GEMINI:
                    $pet
                        ->increaseSafety(mt_rand(4, 8))
                        ->increaseLove(mt_rand(4, 8))
                    ;
                    $message = $pet->getName() . ' was feeling lonely, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' smiled, and split into multiple copies of itself, and they all played games together!';
                    break;
                case SpiritCompanionStarEnum::SAGITTARIUS:
                    $pet
                        ->increaseSafety(mt_rand(2, 4))
                        ->increaseLove(mt_rand(4, 8))
                        ->increaseEsteem(mt_rand(2, 4))
                    ;
                    $message = $pet->getName() . ' was feeling lonely, so talked to ' . $companion->getName() . '. The two hosted a party for themselves; ' . $pet->getName() . ' had a lot of fun.';
                    break;
                case SpiritCompanionStarEnum::HYDRA:
                    $pet
                        ->increaseSafety(mt_rand(4, 8))
                        ->increaseLove(mt_rand(4, 8))
                    ;
                    $message = $pet->getName() . ' was feeling lonely, so talked to ' . $companion->getName() . '. Sensing ' . $pet->getName() . '\'s unease, ' . $companion->getName() . ' settled into ' . $pet->getName() . '\'s lap.';
                    break;
                default:
                    throw new \Exception('Unknown Spirit Companion Star "' . $companion->getStar() . '"');
            }
        }
        else // low on esteem
        {
            switch($companion->getStar())
            {
                case SpiritCompanionStarEnum::ALTAIR:
                case SpiritCompanionStarEnum::CEPHEUS:
                    $pet
                        ->increaseSafety(mt_rand(2, 4))
                        ->increaseLove(mt_rand(2, 4))
                        ->increaseEsteem(mt_rand(2, 4))
                    ;
                    $message = $pet->getName() . ' was feeling down, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' listened patiently; in the end, ' . $pet->getName() . ' felt a little better.';
                    break;
                case SpiritCompanionStarEnum::CASSIOPEIA:
                    $pet
                        ->increaseEsteem(mt_rand(4, 8))
                    ;
                    $message = $pet->getName() . ' was feeling down, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' whispered odd prophecies, then stared at ' . $pet->getName() . ' expectantly. Somehow, that actually helped!';
                    break;
                case SpiritCompanionStarEnum::GEMINI:
                    $pet
                        ->increaseLove(mt_rand(2, 4))
                    ;
                    $message = $pet->getName() . ' was feeling down, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' tried to entertain ' . $pet->getName() . ' by splitting into copies and dancing around, but it didn\'t really help...';
                    break;
                case SpiritCompanionStarEnum::SAGITTARIUS:
                    $pet
                        ->increaseSafety(mt_rand(2, 4))
                        ->increaseLove(mt_rand(2, 4))
                        ->increaseEsteem(mt_rand(4, 8))
                    ;
                    $message = $pet->getName() . ' was feeling down, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' empathized completely, having been in similar situations themselves. It was really nice to hear!';
                    break;
                case SpiritCompanionStarEnum::HYDRA:
                    $pet
                        ->increaseSafety(mt_rand(2, 4))
                        ->increaseLove(mt_rand(2, 4))
                        ->increaseEsteem(mt_rand(4, 8))
                    ;
                    $message = $pet->getName() . ' was feeling down, so talked to ' . $companion->getName() . '. Sensing ' . $pet->getName() . '\'s unease, ' . $companion->getName() . ' settled into ' . $pet->getName() . '\'s lap.';
                    break;
                default:
                    throw new \Exception('Unknown Spirit Companion Star "' . $companion->getStar() . '"');
            }

        }

        $this->responseService->createActivityLog($pet, $message, 'companions/' . $companion->getImage(), $changes->compare($pet));
    }

    private function hangOutWithOtherPet(PetRelationship $pet, PetRelationship $friend)
    {
        $changes = new PetChanges($pet->getPet());

        $pet->getPet()->spendTime(\mt_rand(45, 60));
        $friend->getPet()->spendTime(mt_rand(5, 10));

        list($petLog, $friendLog) = $this->petRelationshipService->meetOtherPetPrivately($pet, $friend);

        $petLog->setChanges($changes->compare($pet->getPet()));

        $this->responseService->addActivityLog($petLog);
    }

    private function meetRoommates(Pet $pet): bool
    {
        /** @var Pet[] $otherPets */
        $otherPets = $pet->getOwner()->getPets()->filter(function(Pet $p) use($pet) { return $p->getId() !== $pet->getId(); });

        $metNewPet = false;

        foreach($otherPets as $otherPet)
        {
            if(!$pet->hasRelationshipWith($otherPet))
            {
                $metNewPet = true;
                $this->petRelationshipService->meetRoommate($pet, $otherPet);
            }

            if(!$otherPet->hasRelationshipWith($pet))
            {
                $metNewPet = true;
                $this->petRelationshipService->meetRoommate($otherPet, $pet);
            }
        }

        return $metNewPet;
    }

    private function doNothing(Pet $pet)
    {
        $pet->spendTime(\mt_rand(30, 60));
        $this->responseService->createActivityLog($pet, $pet->getName() . ' hung around the house.', '');
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
        $desire = $pet->getDexterity() + $pet->getNature() + $pet->getFishing() + \mt_rand(1, 4);

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getDexterity() + $pet->getTool()->getItem()->getTool()->getNature() + $pet->getTool()->getItem()->getTool()->getFishing();

        return max(1, round($desire * (1 + \mt_rand(-10, 10) / 100)));
    }

    public function generateMonsterHuntingDesire(Pet $pet): int
    {
        $desire = $pet->getStrength() + $pet->getBrawl() + \mt_rand(1, 4);

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getStrength() + $pet->getTool()->getItem()->getTool()->getBrawl();

        return max(1, round($desire * (1 + \mt_rand(-10, 10) / 100)));
    }

    public function generateCraftingDesire(Pet $pet): int
    {
        $desire = $pet->getIntelligence() + $pet->getCrafts() + \mt_rand(1, 4);

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getIntelligence() + $pet->getTool()->getItem()->getTool()->getCrafts();

        return max(1, round($desire * (1 + \mt_rand(-10, 10) / 100)));
    }

    public function generateGatheringDesire(Pet $pet): int
    {
        $desire = $pet->getPerception() + $pet->getNature() + $pet->getGathering() + \mt_rand(1, 4);

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getPerception() + $pet->getTool()->getItem()->getTool()->getNature() + $pet->getTool()->getItem()->getTool()->getGathering();

        return max(1, round($desire * (1 + \mt_rand(-10, 10) / 100)));
    }

    public function generateHackingDesire(Pet $pet): int
    {
        $desire = $pet->getIntelligence() + $pet->getComputer() + \mt_rand(1, 4);

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getIntelligence() + $pet->getTool()->getItem()->getTool()->getComputer();

        return max(1, round($desire * (1 + \mt_rand(-10, 10) / 100)));
    }

    public function generateProgrammingDesire(Pet $pet): int
    {
        $desire = $pet->getIntelligence() + $pet->getComputer() + \mt_rand(1, 4);

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getIntelligence() + $pet->getTool()->getItem()->getTool()->getComputer();

        return max(1, round($desire * (1 + \mt_rand(-10, 10) / 100)));
    }
}