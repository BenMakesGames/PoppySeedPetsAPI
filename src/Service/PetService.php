<?php
namespace App\Service;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\ItemFood;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetBaby;
use App\Entity\PetRelationship;
use App\Entity\StatusEffect;
use App\Enum\EnumInvalidValueException;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\SpiritCompanionStarEnum;
use App\Enum\StatusEffectEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Model\PetChanges;
use App\Repository\InventoryRepository;
use App\Repository\PetRepository;
use App\Repository\UserStatsRepository;
use App\Service\PetActivity\CraftingService;
use App\Service\PetActivity\FishingService;
use App\Service\PetActivity\GatheringService;
use App\Service\PetActivity\GenericAdventureService;
use App\Service\PetActivity\GivingTreeGatheringService;
use App\Service\PetActivity\HuntingService;
use App\Service\PetActivity\PoopingService;
use App\Service\PetActivity\PregnancyService;
use App\Service\PetActivity\ProgrammingService;
use App\Service\PetActivity\Protocol7Service;
use App\Service\PetActivity\TreasureMapService;
use App\Service\PetActivity\UmbraService;
use Doctrine\ORM\EntityManagerInterface;

class PetService
{
    private $em;
    private $petRepository;
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
    private $umbraService;
    private $poopingService;
    private $givingTreeGatheringService;
    private $pregnancyService;
    private $petActivityStatsService;
    private $petGroupService;
    private $petExperienceService;

    public function __construct(
        EntityManagerInterface $em, ResponseService $responseService,
        PetRelationshipService $petRelationshipService, PetRepository $petRepository,
        FishingService $fishingService, HuntingService $huntingService, GatheringService $gatheringService,
        CraftingService $craftingService, UserStatsRepository $userStatsRepository, InventoryRepository $inventoryRepository,
        TreasureMapService $treasureMapService, GenericAdventureService $genericAdventureService,
        Protocol7Service $protocol7Service, ProgrammingService $programmingService, UmbraService $umbraService,
        PoopingService $poopingService, GivingTreeGatheringService $givingTreeGatheringService,
        PregnancyService $pregnancyService, PetActivityStatsService $petActivityStatsService, PetGroupService $petGroupService,
        PetExperienceService $petExperienceService
    )
    {
        $this->em = $em;
        $this->petRepository = $petRepository;
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
        $this->umbraService = $umbraService;
        $this->poopingService = $poopingService;
        $this->givingTreeGatheringService = $givingTreeGatheringService;
        $this->pregnancyService = $pregnancyService;
        $this->petActivityStatsService = $petActivityStatsService;
        $this->petGroupService = $petGroupService;
        $this->petExperienceService = $petExperienceService;
    }

    /**
     * @param Pet $pet
     * @param int $points
     */
    public function gainAffection(Pet $pet, int $points)
    {
        if($pet->getInDaycare()) throw new \InvalidArgumentException('Pets in daycare cannot be interacted with.');

        if($points === 0) return;

        $divideBy = 1;

        if($pet->getFood() + $pet->getAlcohol() < 0) $divideBy++;
        if($pet->getSafety() + $pet->getAlcohol() < 0) $divideBy++;
        if($pet->getLove() + $pet->getAlcohol() < 0) $divideBy++;
        if($pet->getEsteem() + $pet->getAlcohol() < 0) $divideBy++;

        $points = ceil($points / $divideBy);

        if($points === 0) return;

        $previousAffectionLevel = $pet->getAffectionLevel();

        $pet->increaseAffectionPoints($points);

        // if a pet's affection level increased, and you haven't unlocked the park, now you get the park!
        if($pet->getAffectionLevel() > $previousAffectionLevel && $pet->getOwner()->getUnlockedPark() === null)
            $pet->getOwner()->setUnlockedPark();
    }

    public function doPet(Pet $pet)
    {
        if($pet->getInDaycare()) throw new \InvalidArgumentException('Pets in daycare cannot be interacted with.');

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
        if($pet->getInDaycare()) throw new \InvalidArgumentException('Pets in daycare cannot be interacted with.');

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
     * @param Pet $pet
     * @param Inventory[] $inventory
     * @return PetActivityLog
     * @throws EnumInvalidValueException
     */
    public function doFeed(Pet $pet, array $inventory): PetActivityLog
    {
        if($pet->getInDaycare()) throw new \InvalidArgumentException('Pets in daycare cannot be interacted with.');

        if(ArrayFunctions::any($inventory, function(Inventory $i) { return $i->getItem()->getFood() === null; }))
            throw new \InvalidArgumentException('At least one of the items selected is not edible!');

        shuffle($inventory);

        $petChanges = new PetChanges($pet);
        $foodsEaten = [];
        $favorites = [];
        $tooFull = [];
        $tooPoisonous = [];

        foreach($inventory as $i)
        {
            if($pet->getJunk() + $pet->getFood() >= $pet->getStomachSize())
            {
                $tooFull[] = $i->getItem()->getName();
                continue;
            }

            $food = $i->getItem()->getFood();

            if($pet->wantsSobriety() && ($food->getAlcohol() > 0 || $food->getCaffeine() > 0 || $food->getPsychedelic() > 0))
            {
                $tooPoisonous[] = $i->getItem()->getName();
                continue;
            }

            $this->petExperienceService->applyFoodEffects($pet, $food);

            // consider favorite flavor:
            if(!FlavorEnum::isAValue($pet->getFavoriteFlavor()))
                throw new EnumInvalidValueException(FlavorEnum::class, $pet->getFavoriteFlavor());

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

            if ($remainder > 0 && mt_rand(1, 8) <= $remainder)
                $gain++;

            $pet->increaseSafety($gain);
            $this->gainAffection($pet, $gain);

            if($pet->getPregnancy())
                $pet->getPregnancy()->increaseAffection($gain);

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
        {
            if(count($tooPoisonous) > 0)
                return $this->responseService->createActivityLog($pet, 'You tried to feed ' . $pet->getName() . ', but ' . ArrayFunctions::pick_one($tooPoisonous) . ' really isn\'t appealing right now.', '');
            else
                return $this->responseService->createActivityLog($pet, 'You tried to feed ' . $pet->getName() . ', but they\'re too full to eat anymore.', '');
        }
    }

    public function runHour(Pet $pet)
    {
        if($pet->getInDaycare())
            throw new \InvalidArgumentException('Pets in daycare cannot be interacted with.');

        if($pet->getTime() < 60)
            throw new \InvalidArgumentException('Pet does not have enough Time.');

        $pet->increaseFood(-1);

        if($pet->getJunk() > 0)
            $pet->increaseJunk(-1);

        if($pet->getPoison() > 0 && $pet->getAlcohol() === 0 && $pet->getCaffeine() === 0 && $pet->getPsychedelic() === 0)
            $pet->increasePoison(-1);

        if($pet->getAlcohol() > 0)
        {
            $pet->increaseAlcohol(-1);
            $pet->increasePoison(1);
        }

        if($pet->getCaffeine() > 0)
        {
            $pet->increaseCaffeine(-1);

            if(mt_rand(1, 2) === 1)
                $pet->increasePoison(1);
        }

        if($pet->getPsychedelic() > 0)
        {
            $pet->increasePsychedelic(-1);
            $pet->increasePoison(2);
        }

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

        $pregnancy = $pet->getPregnancy();

        if($pregnancy)
        {
            if($pet->getFood() < 0) $pregnancy->increaseAffection(-1);
            if($pet->getSafety() < 0 && mt_rand(1, 2) === 1) $pregnancy->increaseAffection(-1);
            if($pet->getLove() < 0 && mt_rand(1, 3) === 1) $pregnancy->increaseAffection(-1);
            if($pet->getEsteem() < 0 && mt_rand(1, 4) === 1) $pregnancy->increaseAffection(-1);

            if($pregnancy->getGrowth() >= PetBaby::PREGNANCY_DURATION)
            {
                $this->pregnancyService->giveBirth($pet);
                return;
            }
        }

        if($pet->getPoison() > 0)
        {
            if(mt_rand(6, 24) < $pet->getPoison())
            {
                $changes = new PetChanges($pet);

                $safetyVom = ceil($pet->getPoison() / 4);

                $pet->increasePoison(-mt_rand( ceil($pet->getPoison() / 4), ceil($pet->getPoison() * 3 / 4)));
                if($pet->getAlcohol() > 0) $pet->increaseAlcohol(-mt_rand(1, ceil($pet->getAlcohol() / 2)));
                if($pet->getPsychedelic() > 0) $pet->increasePsychedelic(-mt_rand(1, ceil($pet->getPsychedelic() / 2)));
                if($pet->getCaffeine() > 0) $pet->increaseFood(-mt_rand(1, ceil($pet->getCaffeine() / 2)));
                if($pet->getJunk() > 0) $pet->increaseJunk(-mt_rand(1, ceil($pet->getJunk() / 2)));
                if($pet->getFood() > 0) $pet->increaseFood(-mt_rand(1, ceil($pet->getFood() / 2)));

                $pet->increaseSafety(-mt_rand(1, $safetyVom));
                $pet->increaseEsteem(-mt_rand(1, $safetyVom));

                $this->petExperienceService->spendTime($pet, mt_rand(15, 30), PetActivityStatEnum::OTHER, null);

                $this->responseService->createActivityLog($pet, $pet->getName() . ' threw up :(', '', $changes->compare($pet));

                return;
            }
        }

        if($pet->hasMerit(MeritEnum::BLACK_HOLE_TUM) && mt_rand(1, 200) === 1)
        {
            $this->poopingService->poopDarkMatter($pet);
        }

        if(
            // has food
            $pet->getFood() > 0 &&

            // a random factor
            mt_rand(1, max(10, 5 + $pet->getLove() + $pet->getSafety() + $pet->getEsteem())) <= 3
        )
        {
            if($this->hangOutWithFriend($pet))
                return;
        }

        if($this->meetRoommates($pet))
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HANG_OUT, null);
            return;
        }

        $itemsInHouse = (int)$this->inventoryRepository->countItemsInLocation($pet->getOwner(), LocationEnum::HOME);
        $craftingPossibilities = $this->craftingService->getCraftingPossibilities($pet);
        $programmingPossibilities = $this->programmingService->getCraftingPossibilities($pet);

        $houseTooFull = mt_rand(1, 10) > $pet->getOwner()->getMaxInventory() - $itemsInHouse;

        if($houseTooFull)
        {
            if($itemsInHouse >= $pet->getOwner()->getMaxInventory())
                $description = 'The house is crazy-full.';
            else
                $description = 'The house is getting pretty full.';

            if(count($craftingPossibilities) === 0 && count($programmingPossibilities) === 0)
            {
                $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::OTHER, null);

                $this->responseService->createActivityLog($pet, $description . ' ' . $pet->getName() . ' wanted to make something, but couldn\'t find any materials to work with.', '');
            }
            else
            {
                $possibilities = [];

                if(count($craftingPossibilities) > 0) $possibilities[] = [ $this->craftingService, $craftingPossibilities ];
                if(count($programmingPossibilities) > 0) $possibilities[] = [ $this->programmingService, $programmingPossibilities ];

                $do = ArrayFunctions::pick_one($possibilities);

                /** @var PetActivityLog $activityLog */
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

        if(mt_rand(1, 48) === 1)
        {
            if(count($pet->getGroups()) > 0)
            {
                $this->petGroupService->doGroupActivity($pet, ArrayFunctions::pick_one($pet->getGroups()->toArray()));
                return;
            }
            else
            {
                if($this->petGroupService->createGroup($pet))
                    return;
            }
        }

        if($pet->getTool() && $pet->getTool()->getItem()->getName() === 'Cetgueli\'s Treasure Map')
        {
            $this->treasureMapService->doCetguelisTreasureMap($pet);
            return;
        }

        if($pet->getTool() && $pet->getTool()->getItem()->getName() === '"Gold" Idol')
        {
            $this->treasureMapService->doGoldIdol($pet);
            return;
        }

        if(mt_rand(1, 50) === 1)
        {
            $activityLog = $this->givingTreeGatheringService->gatherFromGivingTree($pet);
            if($activityLog)
                return;
        }

        $petDesires = [
            'fish' => $this->generateFishingDesire($pet),
            'hunt' => $this->generateMonsterHuntingDesire($pet),
            'gather' => $this->generateGatheringDesire($pet),
            'umbra' => $this->generateExploreUmbraDesire($pet),
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
            case 'umbra': $this->umbraService->adventure($pet); break;
            default: $this->doNothing($pet); break;
        }
    }

    private function hangOutWithFriend(Pet $pet): bool
    {
        /** @var PetRelationship[] $friends */
        $relationships = $pet->getPetRelationships()->filter(function(PetRelationship $p) {
            // starving pets don't have time to hang out, and 0-commitment pets are not hung out with
            return $p->getRelationship()->getFood() > 0 && $p->getCommitment() > 0;
        })->toArray();

        // no friends available? no spirit companion? GIT OUTTA' HE'E!
        if(count($relationships) === 0 && !$pet->hasMerit(MeritEnum::SPIRIT_COMPANION))
            return false;

        // maybe hang out with a spirit companion, if you have one
        if($pet->hasMerit(MeritEnum::SPIRIT_COMPANION) && (count($relationships) === 0 || mt_rand(1, count($relationships) + 1) === 1))
            $this->hangOutWithSpiritCompanion($pet);
        else
        {
            /** @var PetRelationship $relationship */
            $relationship = ArrayFunctions::pick_one_weighted($relationships, function(PetRelationship $r) {
                return $r->getCommitment();
            });

            $friend = $relationship->getRelationship();

            $friendRelationship = $friend->getPetRelationships()->filter(function(PetRelationship $p) use($pet) { return $p->getRelationship()->getId() === $pet->getId(); })->first();

            if($friendRelationship === false)
            {
                throw new \Exception($pet->getName() . ' knows ' . $friend->getName() . ', but not the other way around! This is a bug, and should never happen! Make Ben fix it!');
            }

            // hang out with selected pet
            $this->hangOutWithOtherPet($relationship, $friendRelationship);
        }

        return true;
    }

    private function hangOutWithSpiritCompanion(Pet $pet)
    {
        $changes = new PetChanges($pet);

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HANG_OUT, null);

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

        $this->responseService->createActivityLog($pet, $message, 'companions/' . $companion->getImage(), $changes->compare($pet))
            ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
        ;
    }

    /**
     * @param PetRelationship $pet
     * @param PetRelationship $friend
     * @throws EnumInvalidValueException
     */
    private function hangOutWithOtherPet(PetRelationship $pet, PetRelationship $friend)
    {
        $petChanges = new PetChanges($pet->getPet());
        $friendChanges = new PetChanges($friend->getPet());

        $this->petExperienceService->spendTime($pet->getPet(), mt_rand(45, 60), PetActivityStatEnum::HANG_OUT, null);
        $this->petExperienceService->spendTime($friend->getPet(), mt_rand(5, 10), PetActivityStatEnum::HANG_OUT, null);

        $petPreviousRelationship = $pet->getCurrentRelationship();
        $friendPreviousRelationship = $friend->getCurrentRelationship();

        list($petLog, $friendLog) = $this->petRelationshipService->hangOutPrivately($pet, $friend);

        if($petPreviousRelationship !== $pet->getCurrentRelationship())
        {
            $relationshipMovement = $this->petRelationshipService->calculateRelationshipDistance($petPreviousRelationship, $pet->getCurrentRelationship());

            $pet->getPet()
                ->increaseLove($relationshipMovement * 2)
                ->increaseEsteem($relationshipMovement)
            ;
        }

        if($friendPreviousRelationship !== $friend->getCurrentRelationship())
        {
            $relationshipMovement = $this->petRelationshipService->calculateRelationshipDistance($friendPreviousRelationship, $friend->getCurrentRelationship());

            $friend->getPet()
                ->increaseLove($relationshipMovement * 2)
                ->increaseEsteem($relationshipMovement)
            ;
        }

        $petLog->setChanges($petChanges->compare($pet->getPet()));
        $friendLog->setChanges($friendChanges->compare($friend->getPet()));

        $this->responseService->addActivityLog($petLog);
    }

    private function meetRoommates(Pet $pet): bool
    {
        /** @var Pet[] $otherPets */
        $otherPets = $this->petRepository->getRoommates($pet);

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

    /**
     * @param Pet $pet
     * @throws EnumInvalidValueException
     */
    private function doNothing(Pet $pet)
    {
        $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::OTHER, null);
        $this->responseService->createActivityLog($pet, $pet->getName() . ' hung around the house.', '');
    }

    private function pickDesire(array $petDesires)
    {
        $totalDesire = array_sum($petDesires);

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
        $desire = $pet->getDexterity() + $pet->getNature() + $pet->getFishing() + mt_rand(1, 4);

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getNature() + $pet->getTool()->getItem()->getTool()->getFishing();

        return max(1, round($desire * (1 + mt_rand(-10, 10) / 100)));
    }

    public function generateMonsterHuntingDesire(Pet $pet): int
    {
        $desire = $pet->getStrength() + $pet->getBrawl() + mt_rand(1, 4);

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getBrawl();

        return max(1, round($desire * (1 + mt_rand(-10, 10) / 100)));
    }

    public function generateCraftingDesire(Pet $pet): int
    {
        $desire = $pet->getIntelligence() + $pet->getCrafts() + mt_rand(1, 4);

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getCrafts();

        return max(1, round($desire * (1 + mt_rand(-10, 10) / 100)));
    }

    public function generateExploreUmbraDesire(Pet $pet): int
    {
        $desire = $pet->getStamina() + $pet->getIntelligence() + $pet->getUmbra() + mt_rand(1, 4);

        if($pet->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getUmbra();

        if($pet->hasMerit(MeritEnum::NATURAL_CHANNEL))
        {
            if($pet->getPsychedelic() > $pet->getMaxPsychedelic() / 2)
                return ceil($desire * $pet->getPsychedelic() * 2 / $pet->getMaxPsychedelic());
            else
                return $desire;
        }
        else if($pet->getPsychedelic() > 0)
        {
            return ceil($desire * $pet->getPsychedelic() * 2 / $pet->getMaxPsychedelic());
        }
        else
            return 0;
    }

    public function generateGatheringDesire(Pet $pet): int
    {
        $desire = $pet->getPerception() + $pet->getNature() + $pet->getGathering() + mt_rand(1, 4);

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getNature() + $pet->getTool()->getItem()->getTool()->getGathering();

        return max(1, round($desire * (1 + mt_rand(-10, 10) / 100)));
    }

    public function generateHackingDesire(Pet $pet): int
    {
        $desire = $pet->getIntelligence() + $pet->getComputer() + mt_rand(1, 4);

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getComputer();

        return max(1, round($desire * (1 + mt_rand(-10, 10) / 100)));
    }

    public function generateProgrammingDesire(Pet $pet): int
    {
        $desire = $pet->getIntelligence() + $pet->getComputer() + mt_rand(1, 4);

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getComputer();

        return max(1, round($desire * (1 + mt_rand(-10, 10) / 100)));
    }
}