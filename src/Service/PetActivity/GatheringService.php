<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\DistractionLocationEnum;
use App\Enum\EnumInvalidValueException;
use App\Enum\FlavorEnum;
use App\Enum\MeritEnum;
use App\Enum\MoonPhaseEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetLocationEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ActivityHelpers;
use App\Functions\AdventureMath;
use App\Functions\ArrayFunctions;
use App\Functions\ColorFunctions;
use App\Functions\DateFunctions;
use App\Functions\NumberFunctions;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\EnchantmentRepository;
use App\Repository\ItemRepository;
use App\Repository\MeritRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\PetRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\SpiceRepository;
use App\Repository\UserQuestRepository;
use App\Service\CalendarService;
use App\Service\FieldGuideService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\PetFactory;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\TransactionService;
use App\Service\WeatherService;

class GatheringService
{
    private ResponseService $responseService;
    private InventoryService $inventoryService;
    private PetExperienceService $petExperienceService;
    private TransactionService $transactionService;
    private ItemRepository $itemRepository;
    private SpiceRepository $spiceRepository;
    private WeatherService $weatherService;
    private IRandom $squirrel3;
    private FieldGuideService $fieldGuideService;
    private PetSpeciesRepository $petSpeciesRepository;
    private PetRepository $petRepository;
    private PetFactory $petFactory;
    private MeritRepository $meritRepository;
    private GatheringDistractionService $gatheringDistractions;
    private UserQuestRepository $userQuestRepository;
    private CalendarService $calendarService;
    private PetActivityLogTagRepository $petActivityLogTagRepository;
    private EnchantmentRepository $enchantmentRepository;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetExperienceService $petExperienceService,
        TransactionService $transactionService, ItemRepository $itemRepository, SpiceRepository $spiceRepository,
        Squirrel3 $squirrel3, WeatherService $weatherService, FieldGuideService $fieldGuideService,
        PetSpeciesRepository $petSpeciesRepository, PetRepository $petRepository, PetFactory $petFactory,
        MeritRepository $meritRepository, GatheringDistractionService $gatheringDistractions,
        UserQuestRepository $userQuestRepository, CalendarService $calendarService,
        PetActivityLogTagRepository $petActivityLogTagRepository, EnchantmentRepository $enchantmentRepository
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->transactionService = $transactionService;
        $this->itemRepository = $itemRepository;
        $this->spiceRepository = $spiceRepository;
        $this->squirrel3 = $squirrel3;
        $this->weatherService = $weatherService;
        $this->fieldGuideService = $fieldGuideService;
        $this->petSpeciesRepository = $petSpeciesRepository;
        $this->petRepository = $petRepository;
        $this->petFactory = $petFactory;
        $this->meritRepository = $meritRepository;
        $this->gatheringDistractions = $gatheringDistractions;
        $this->userQuestRepository = $userQuestRepository;
        $this->calendarService = $calendarService;
        $this->petActivityLogTagRepository = $petActivityLogTagRepository;
        $this->enchantmentRepository = $enchantmentRepository;
    }

    public function adventure(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $maxSkill = 10 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal() - $pet->getAlcohol() - $pet->getPsychedelic();

        $maxSkill = NumberFunctions::clamp($maxSkill, 1, 24);

        $roll = $this->squirrel3->rngNextInt(1, $maxSkill);

        $activityLog = null;
        $changes = new PetChanges($pet);

        switch($roll)
        {
            case 1:
            case 2:
            case 3:
            case 4:
                $activityLog = $this->foundNothing($pet);
                break;
            case 5:
                $activityLog = $this->foundPaperBag($pet);
                break;
            case 6:
                $activityLog = $this->foundTeaBush($pet);
                break;
            case 7:
            case 8:
                $activityLog = $this->foundBerryBush($petWithSkills);
                break;
            case 9:
            case 10:
                $activityLog = $this->foundHollowLog($petWithSkills);
                break;
            case 11:
                $activityLog = $this->foundAbandonedQuarry($petWithSkills);
                break;
            case 12:
                $activityLog = $this->foundBirdNest($petWithSkills);
                break;
            case 13:
                $activityLog = $this->foundBeach($petWithSkills);
                break;
            case 14:
                $activityLog = $this->foundOvergrownGarden($petWithSkills);
                break;
            case 15:
                $activityLog = $this->foundIronMine($petWithSkills);
                break;
            case 16:
                $activityLog = $this->foundMicroJungle($petWithSkills);
                break;
            case 17:
            case 18:
                $activityLog = $this->foundWildHedgemaze($petWithSkills);
                break;
            case 19:
            case 20:
                $activityLog = $this->foundVolcano($petWithSkills);
                break;
            case 21:
                $activityLog = $this->foundGypsumCave($petWithSkills);
                break;
            case 22:
            case 23:
                $activityLog = $this->foundDeepMicroJungle($petWithSkills);
                break;
            case 24:
                if($this->fieldGuideService->hasUnlocked($pet->getOwner(), 'Île Volcan'))
                    $activityLog = $this->foundOldSettlement($petWithSkills);
                else if($this->squirrel3->rngNextBool())
                    $activityLog = $this->foundMicroJungle($petWithSkills);
                else
                    $activityLog = $this->foundAbandonedQuarry($petWithSkills);
                break;
        }

        if($activityLog)
        {
            $activityLog->setChanges($changes->compare($pet));
        }

        if(AdventureMath::petAttractsBug($this->squirrel3, $pet, 75))
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    private function foundAbandonedQuarry(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $pobosFound = $this->userQuestRepository->findOrCreate($pet->getOwner(), 'Pobos Found', 0);
        $poboChance = 150 + (int)(200 * log10($pobosFound->getValue() + 1));

        if($this->squirrel3->rngNextInt(1, 2000) < $petWithSkills->getPerception()->getTotal())
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went to an Abandoned Quarry, and happened to find a piece of Striped Microcline!', '')
                ->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => 'Gathering' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
            $this->inventoryService->petCollectsItem('Striped Microcline', $pet, $pet->getName() . ' found this at an Abandoned Quarry.', $activityLog);
            $pet->increaseEsteem(4);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::GATHER, true);
        }
        else if($this->squirrel3->rngNextInt(1, $poboChance) === 1)
        {
            $pobosFound->setValue($pobosFound->getValue() + 1);
            $pobo = $this->petSpeciesRepository->findOneBy([ 'name' => 'Pobo' ]);

            $poboName = $this->squirrel3->rngNextFromArray([
                'Flit', 'Waverly', 'Mirage', 'Shadow', 'Calcium',
                'Kneecap', 'Osteal', 'Papyrus', 'Quint', 'Debris'
            ]);

            $colorA = ColorFunctions::HSL2Hex($this->squirrel3->rngNextFloat(), 0.62, 0.53);

            $newPet = $this->petFactory->createPet(
                $pet->getOwner(), $poboName, $pobo,
                $colorA, 'ece8d0',
                FlavorEnum::getRandomValue($this->squirrel3),
                $this->meritRepository->findOneByName(MeritEnum::NO_SHADOW_OR_REFLECTION)
            );

            $newPet
                ->increaseLove(-8)
                ->increaseSafety(10)
                ->increaseEsteem(-8)
                ->increaseFood(10)
                ->setScale($this->squirrel3->rngNextInt(80, 120))
            ;

            $numberOfPetsAtHome = $this->petRepository->getNumberAtHome($pet->getOwner());

            $petJoinsHouse = $numberOfPetsAtHome < $pet->getOwner()->getMaxPets();

            $extraMessage = 'It followed %pet:' . $pet->getId() . '.name% home';

            if($petJoinsHouse)
            {
                $extraMessage .= ', and made itself - well - at home!';
            }
            else
            {
                $newPet->setLocation(PetLocationEnum::DAYCARE);
                $extraMessage .= ', but upon seeing the house was full, wafted off to the Daycare.';
            }

            $this->responseService->setReloadPets($petJoinsHouse);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went to an Abandoned Quarry, and happened to find a Stereotypical Bone! But when they picked it up, it began to move on its own! IT\'S POSSESSED! ' . $extraMessage, '')
                ->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => 'Gathering' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::UMBRA ], $activityLog);
            $pet->increaseSafety(-4);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
        }
        else if($this->squirrel3->rngNextInt(1, 150) === 1)
        {
            $bone = $this->squirrel3->rngNextFromArray([ 'Rib', 'Stereotypical Bone' ]);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went to an Abandoned Quarry, and happened to find a ' . $bone . '!', '')
                ->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => 'Gathering' ]))
            ;

            $this->inventoryService->petCollectsItem($bone, $pet, $pet->getName() . ' found this at an Abandoned Quarry!', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ], $activityLog);
            $pet->increaseEsteem(4);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
        }
        else if($petWithSkills->getStrength()->getTotal() < 4)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a huge block of Limestone at an Abandoned Quarry, and, with all their might, pushed, dragged, and "rolled" it home.', 'items/mineral/limestone')
                ->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => 'Gathering' ]))
            ;
            $pet->increaseFood(-2);
            $this->inventoryService->petCollectsItem('Limestone', $pet, $pet->getName() . ' found this at an Abandoned Quarry. It was really heavy!', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::GATHER, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a huge block of Limestone at an Abandoned Quarry, and carried it home.', 'items/mineral/limestone')
                ->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => 'Gathering' ]))
            ;
            $this->inventoryService->petCollectsItem('Limestone', $pet, $pet->getName() . ' found this at an Abandoned Quarry.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
        }

        return $activityLog;
    }

    private function foundNothing(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::GATHER, false);

        return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went out gathering, but couldn\'t find anything.', 'icons/activity-logs/confused')
            ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering' ]))
        ;
    }

    private function foundPaperBag(Pet $pet): PetActivityLog
    {
        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a Paper Bag just, like, lyin\' around.', 'items/bag/paper')
            ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering' ]))
        ;

        $this->inventoryService->petCollectsItem('Paper Bag', $pet, $pet->getName() . ' found this just lyin\' around.', $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

        return $activityLog;
    }

    private function foundTeaBush(Pet $pet): PetActivityLog
    {
        if($this->weatherService->getWeather(new \DateTimeImmutable(), $pet)->getRainfall() > 0 && $this->squirrel3->rngNextInt(1, 4) === 1)
        {
            $message = '%pet:' . $pet->getId() . '.name% found a Tea Bush, and grabbed a few Tea Leaves, as well as some Worms which had surfaced to escape the rain.';

            $activityLog = $this->responseService->createActivityLog($pet, $message, 'items/veggie/tea-leaves')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering' ]))
            ;

            $this->inventoryService->petCollectsItem('Tea Leaves', $pet, $pet->getName() . ' harvested this from a Tea Bush.', $activityLog);
            $this->inventoryService->petCollectsItem('Worms', $pet, $pet->getName() . ' found these under a Tea Bush.', $activityLog);
        }
        else
        {
            $message = '%pet:' . $pet->getId() . '.name% found a Tea Bush, and grabbed a few Tea Leaves.';

            $activityLog = $this->responseService->createActivityLog($pet, $message, 'items/veggie/tea-leaves')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering' ]))
            ;

            $this->inventoryService->petCollectsItem('Tea Leaves', $pet, $pet->getName() . ' harvested this from a Tea Bush.', $activityLog);
            $this->inventoryService->petCollectsItem('Tea Leaves', $pet, $pet->getName() . ' harvested this from a Tea Bush.', $activityLog);

            if($this->squirrel3->rngNextBool())
                $this->inventoryService->petCollectsItem('Tea Leaves', $pet, $pet->getName() . ' harvested this from a Tea Bush.', $activityLog);
        }

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

        return $activityLog;
    }

    private function foundBerryBush(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $getPinecone = $this->calendarService->getMonthAndDay() > 1225 && $this->squirrel3->rngNextInt(1, 3) === 1;

        if($this->squirrel3->rngNextInt(1, 8) >= 6)
        {
            $harvest = 'Blueberries';
            $additionalHarvest = $this->squirrel3->rngNextInt(1, 4) === 1;
        }
        else
        {
            $harvest = 'Blackberries';
            $additionalHarvest = $this->squirrel3->rngNextInt(1, 3) === 1;
        }

        if($this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 10)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% harvested berries from a Thorny ' . $harvest . ' Bush.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering' ]))
            ;
        }
        else
        {
            $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 4));
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% got scratched up harvesting berries from a Thorny ' . $harvest . ' Bush.', 'icons/activity-logs/wounded')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering' ]))
            ;
        }

        if($getPinecone)
        {
            $activityLog
                ->setEntry($activityLog->getEntry() . ' Hm-what? There was a Pinecone in the bush, too?!')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Special Event', 'Stocking Stuffing Season' ]))
            ;

            $this->inventoryService->petCollectsItem('Pinecone', $pet, $pet->getName() . ' found this in a Thorny ' . $harvest . ' Bush.', $activityLog);
        }

        $this->inventoryService->petCollectsItem($harvest, $pet, $pet->getName() . ' harvested these from a Thorny ' . $harvest . ' Bush.', $activityLog);

        if($additionalHarvest)
            $this->inventoryService->petCollectsItem($harvest, $pet, $pet->getName() . ' harvested these from a Thorny ' . $harvest . ' Bush.', $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

        return $activityLog;
    }

    private function foundHollowLog(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->squirrel3->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::WOODS, 'exploring the nearby woods');

        $pet = $petWithSkills->getPet();

        $toadChance = $this->weatherService->getWeather(new \DateTimeImmutable(), $pet)->getRainfall() > 0 ? 75 : 25;

        if($this->squirrel3->rngNextInt(1, 100) <= $toadChance)
        {
            if($petWithSkills->getCanSeeInTheDark()->getTotal() <= 0)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a Hollow Log, but it was too dark inside to see anything.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering', 'Dark' ]))
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::GATHER, false);
            }
            else if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getStealth()->getTotal() + $petWithSkills->getBrawl(false)->getTotal()) >= 15)
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'Using their ' . ActivityHelpers::SourceOfLight($petWithSkills) . ', ' . ActivityHelpers::PetName($pet) . ' looked inside a Hollow Log, and found a Huge Toad! They got the jump on it, wrestled it to the ground, and claimed its Toadstool!', 'items/fungus/toadstool')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering', 'Fighting', 'Dark', 'Stealth' ]))
                ;
                $this->inventoryService->petCollectsItem('Toadstool', $pet, $pet->getName() . ' harvested this from the back of a Huge Toad found inside a Hollow Log.', $activityLog);
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH, PetSkillEnum::BRAWL ], $activityLog);
                $pet->increaseEsteem($this->squirrel3->rngNextInt(1, 2));
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);

                $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Huge Toad', $activityLog->getEntry());
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'Using their ' . ActivityHelpers::SourceOfLight($petWithSkills) . ', ' . ActivityHelpers::PetName($pet) . ' looked inside a Hollow Log, and found a Huge Toad, but it hopped into the woods, and ' . ActivityHelpers::PetName($pet) . ' lost sight of it!', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering', 'Fighting', 'Dark', 'Stealth' ]))
                ;
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH, PetSkillEnum::BRAWL ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);

                $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Huge Toad', $activityLog->getEntry());
            }
        }
        else if($pet->hasMerit(MeritEnum::BEHATTED) && $this->squirrel3->rngNextInt(1, 75) === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'Resting on top of a Hollow Log, ' . ActivityHelpers::PetName($pet) . ' spotted a Red Bow! (Hot dang!)', 'items/hat/bow-red')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering' ]))
            ;
            $this->inventoryService->petCollectsItem('Red Bow', $pet, $pet->getName() . ' found this on top of a Hollow Log!', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::GATHER, true);
        }
        else
        {
            $success = true;

            if($this->squirrel3->rngNextBool())
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% broke a Crooked Stick off of a Hollow Log.', 'items/plant/stick-crooked')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering' ]))
                ;
                $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' broke this off of a Hollow Log.', $activityLog);
            }
            else
            {
                if($petWithSkills->getCanSeeInTheDark()->getTotal() > 0)
                {
                    $activityLog = $this->responseService->createActivityLog($pet, 'Using their ' . ActivityHelpers::SourceOfLight($petWithSkills) . ', ' . ActivityHelpers::PetName($pet) . ' looked inside a Hollow Log, and found a Grandparoot!', '')
                        ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering', 'Dark' ]))
                    ;
                    $this->inventoryService->petCollectsItem('Grandparoot', $pet, $pet->getName() . ' found this growing inside a Hollow Log.', $activityLog);
                }
                else
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a Hollow Log, but it was too dark inside to see anything.', '')
                        ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering', 'Dark' ]))
                    ;
                    $success = false;
                }
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::GATHER, $success);
        }

        return $activityLog;
    }

    private function foundBirdNest(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->squirrel3->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::WOODS, 'exploring the nearby woods');

        $pet = $petWithSkills->getPet();

        if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getStealth()->getTotal() + $petWithSkills->getDexterity()->getTotal()) >= 10)
        {
            $foundPinecone = $this->calendarService->getMonthAndDay() > 1225;

            if($foundPinecone)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% stole an Egg from a Bird Nest. Hm-what? There was a Pinecone up there, too!', '');
                $activityLog
                    ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering', 'Stealth', 'Special Event', 'Stocking Stuffing Season' ]))
                ;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% stole an Egg from a Bird Nest.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering', 'Stealth' ]))
                ;
            }

            $this->inventoryService->petCollectsItem('Egg', $pet, $pet->getName() . ' stole this from a Bird Nest.', $activityLog);

            if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal()) >= 10)
                $this->inventoryService->petCollectsItem('Fluff', $pet, $pet->getName() . ' stole this from a Bird Nest.', $activityLog);

            if($foundPinecone)
                $this->inventoryService->petCollectsItem('Pinecone', $pet, $pet->getName() . ' found this in a tree while stealing from a Bird Nest.', $activityLog);

            $pet->increaseEsteem($this->squirrel3->rngNextInt(1, 2));
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH ], $activityLog);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
        }
        else
        {
            if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl()->getTotal()) >= 15)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to steal an Egg from a Bird Nest, was spotted by a parent bird, and was able to defeat it in combat!', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering', 'Stealth', 'Fighting' ]))
                ;
                $this->inventoryService->petCollectsItem('Egg', $pet, $pet->getName() . ' stole this from a Bird Nest, after a fight.', $activityLog);
                $this->inventoryService->petCollectsItem('Fluff', $pet, $pet->getName() . ' stole this from a Bird Nest, after a fight.', $activityLog);
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH, PetSkillEnum::BRAWL ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::HUNT, true);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to steal an Egg from a Bird Nest, but was spotted by a parent bird, and chased off!', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering', 'Stealth', 'Fighting' ]))
                ;
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH, PetSkillEnum::BRAWL ], $activityLog);
                $pet->increaseEsteem(-$this->squirrel3->rngNextInt(1, 2));
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::HUNT, false);
            }
        }

        return $activityLog;
    }

    private function foundBeach(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->squirrel3->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::BEACH, 'exploring the beach');

        $pet = $petWithSkills->getPet();

        $loot = [];
        $didWhat = 'found this at a Sandy Beach';

        if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getStealth()->getTotal() + $petWithSkills->getDexterity()->getTotal()) < 10)
        {
            $pet->increaseFood(-1);

            if($this->squirrel3->rngNextInt(1, 20) + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl()->getTotal() >= 15)
            {
                $loot[] = $this->squirrel3->rngNextFromArray([ 'Fish', 'Crooked Stick', 'Egg' ]);

                if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal()) >= 25)
                    $loot[] = $this->squirrel3->rngNextFromArray([ 'Feathers', 'Talon' ]);

                $pet->increaseEsteem($this->squirrel3->rngNextInt(1, 2));
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went to a Sandy Beach, but while looking around, was attacked by a Giant Seagull. ' . $pet->getName() . ' defeated the Giant Seagull, and took its ' . ArrayFunctions::list_nice($loot) . '.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering', 'Stealth', 'Fighting' ]))
                ;
                $didWhat = 'defeated a Giant Seagull at the Beach, and got this';

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::BRAWL, PetSkillEnum::NATURE ], $activityLog);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);

                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::HUNT, true);
            }
            else
            {
                $pet->increaseEsteem(-$this->squirrel3->rngNextInt(1, 2));
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went to a Sandy Beach, but was attacked and routed by a Giant Seagull.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering', 'Stealth', 'Fighting' ]))
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::BRAWL, PetSkillEnum::NATURE ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::HUNT, false);
            }
        }
        else
        {
            $possibleLoot = [
                'Scales', 'Silica Grounds', 'Seaweed', 'Coconut',
            ];

            $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

            if($pet->getTool() && $pet->getTool()->fishingBonus() > 0)
                $loot[] = 'Fish';
            else if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal()) >= 15)
                $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

            if($this->squirrel3->rngNextInt(1, 20) == 1)
                $loot[] = 'Secret Seashell';

            if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 25)
            {
                $moneys = $this->squirrel3->rngNextInt(4, 12);
                $this->transactionService->getMoney($pet->getOwner(), $moneys, $pet->getName() . ' found this on a Sandy Beach.');
                $lootList = $loot;
                $lootList[] = $moneys . '~~m~~';
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went to a Sandy Beach, and stole ' . ArrayFunctions::list_nice($lootList) . ' while the seagulls weren\'t paying attention.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering', 'Stealth', 'Moneys' ]))
                ;
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::STEALTH, PetSkillEnum::NATURE ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::GATHER, true);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went to a Sandy Beach, and stole ' . ArrayFunctions::list_nice($loot) . ' while the seagulls weren\'t paying attention.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering', 'Stealth' ]))
                ;
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::NATURE ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
            }
        }

        foreach($loot as $itemName)
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' ' . $didWhat . '.', $activityLog);

        return $activityLog;
    }

    private function foundOvergrownGarden(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->squirrel3->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::WOODS, 'exploring the woods');

        $pet = $petWithSkills->getPet();

        $possibleLoot = [
            'Carrot', 'Onion', 'Celery', 'Tomato', 'Beans',
            'Sweet Beet', 'Sweet Beet', 'Ginger', 'Rice Flower'
        ];

        if($this->weatherService->getWeather(new \DateTimeImmutable(), $pet)->getRainfall() > 0)
            $possibleLoot[] = 'Worms';

        $loot = [];
        $didWhat = 'harvested this from an Overgrown Garden';

        if($pet->hasMerit(MeritEnum::BEHATTED))
        {
            $chanceToGetOrangeBow = 1 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal();

            if($this->squirrel3->rngNextInt(1, 100) <= $chanceToGetOrangeBow)
                $loot[] = 'Orange Bow';
        }

        if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getStealth()->getTotal() + $petWithSkills->getDexterity()->getTotal()) < 10)
        {
            $pet->increaseFood(-1);

            if($this->squirrel3->rngNextInt(1, 20) + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl()->getTotal() >= 15)
            {
                $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

                if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 25)
                    $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

                if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 15)
                    $loot[] = 'Talon';

                $pet->increaseEsteem($this->squirrel3->rngNextInt(1, 2));
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found an Overgrown Garden, but while looking for food, was attacked by an Angry Mole. ' . $pet->getName() . ' defeated the Angry Mole, and took its ' . ArrayFunctions::list_nice($loot) . '.', 'icons/activity-logs/overgrown-garden')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering', 'Stealth', 'Fighting' ]))
                ;
                $didWhat = 'defeated an Angry Mole in an Overgrown Garden, and got this';

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::STEALTH, PetSkillEnum::BRAWL, PetSkillEnum::NATURE ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::HUNT, true);
            }
            else
            {
                $pet->increaseEsteem(-$this->squirrel3->rngNextInt(1, 2));
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found an Overgrown Garden, but, while looking for food, was attacked and routed by an Angry Mole.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering', 'Stealth', 'Fighting' ]))
                ;
                $loot = [];

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::BRAWL, PetSkillEnum::NATURE ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::HUNT, false);
            }
        }
        else
        {
            $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

            if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 15)
                $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

            if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 25)
                $loot[] = $this->squirrel3->rngNextFromArray([ 'Avocado', 'Red', 'Orange', 'Apricot', 'Yellowy Lime' ]);

            $lucky = false;

            if($pet->hasMerit(MeritEnum::LUCKY) && $this->squirrel3->rngNextInt(1, 20) === 1)
            {
                $loot[] = 'Honeydont';
                $lucky = true;
            }
            else if($this->squirrel3->rngNextInt(1, 100) == 1)
                $loot[] = 'Honeydont';

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% snuck into an Overgrown Garden, and harvested ' . ArrayFunctions::list_nice($loot) . '.', 'icons/activity-logs/overgrown-garden')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering', 'Stealth' ]))
            ;

            if($lucky)
            {
                $activityLog
                    ->setEntry($activityLog->getEntry() . ' (Honeydont?! Lucky~!)')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Lucky~!' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
        }

        foreach($loot as $itemName)
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' ' . $didWhat . '.', $activityLog);

        return $activityLog;
    }

    private function foundIronMine(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($petWithSkills->getCanSeeInTheDark()->getTotal() <= 0)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found an Old Iron Mine, but all the ore must have been hidden deep inside, and ' . $pet->getName() . ' didn\'t have a light.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Mining', 'Dark' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::GATHER, false);

            return $activityLog;
        }

        if($this->squirrel3->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::UNDERGROUND, 'exploring an iron mine');

        if($this->squirrel3->rngNextInt(1, 20) + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getStamina()->getTotal() >= 10)
        {
            $pet->increaseFood(-1);
            $tags = [ 'Mining', 'Dark' ];

            if($pet->hasMerit(MeritEnum::LUCKY) && $this->squirrel3->rngNextInt(1, 20) === 1)
            {
                $pet->increaseEsteem(5);

                if($this->squirrel3->rngNextBool())
                    $loot = 'Gold Ore';
                else
                    $loot = 'Silver Ore';

                $punctuation = '! Lucky~!';
                $tags[] = 'Lucky~!';
            }
            else if($this->squirrel3->rngNextInt(1, 50) === 1)
            {
                $pet->increaseEsteem(5);
                $loot = 'Gold Ore';
                $punctuation = '!!';
            }
            else if($this->squirrel3->rngNextInt(1, 10) === 1)
            {
                $pet->increaseEsteem(3);
                $loot = 'Silver Ore';
                $punctuation = '!';
            }
            else
            {
                $pet->increaseEsteem(1);
                $loot = 'Iron Ore';
                $punctuation = '.';
            }

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found an Old Iron Mine. It was dark, but thanks to their ' . ActivityHelpers::SourceOfLight($petWithSkills) . ', they easily dug up some ' . $loot . $punctuation, '')
                ->addTags($this->petActivityLogTagRepository->findByNames($tags))
            ;
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' dug this out of an Old Iron Mine' . $punctuation, $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::GATHER, true);
        }
        else
        {
            $pet->increaseFood(-2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found an Old Iron Mine. It was dark, but despite their ' . ActivityHelpers::SourceOfLight($petWithSkills) . ', they were unable to dig anything up before getting tired out.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Mining', 'Dark' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::GATHER, false);
        }

        return $activityLog;
    }

    private function foundMicroJungle(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        // no chance for a "gathering distraction" here; that code is in doNormalMicroJungle

        if(DateFunctions::moonPhase(new \DateTimeImmutable()) === MoonPhaseEnum::FULL_MOON)
            $activityLog = $this->encounterNangTani($petWithSkills);
        else
            $activityLog = $this->doNormalMicroJungle($petWithSkills);

        // more chances to get bugs in the jungle!
        if(AdventureMath::petAttractsBug($this->squirrel3, $petWithSkills->getPet(), 25))
            $this->inventoryService->petAttractsRandomBug($petWithSkills->getPet());

        return $activityLog;
    }

    private function encounterNangTani(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getUmbra()->getTotal());
        $success = $roll >= 12;

        $pet->increaseSafety($this->squirrel3->rngNextInt(2, 4));

        if($success)
        {
            $loot = $this->itemRepository->findOneByName($this->squirrel3->rngNextFromArray([
                'Fishkebab Stew',
                'Grilled Fish',
                $this->squirrel3->rngNextFromArray([ 'Orange', 'Yellowy Lime', 'Ponzu' ]),
                $this->squirrel3->rngNextFromArray([ 'Honeydont Ice Cream', 'Naner Ice Cream' ]),
                'Coconut',
                'Mango',
                'Pineapple',
            ]));

            $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 4));

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a lone Naner Tree in the island\'s Micro-Jungle. They left a small offering for Nang Tani... who appeared out of thin air, and gave them ' . $loot->getNameWithArticle() . '!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);

            $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Nang Tani', '%pet:' . $pet->getId() . '.name% encountered Nang Tani at a lone Naner Tree!');

            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' received this from Nang Tani while leaving an offering at a lone Naner Tree in the island\'s Micro-Jungle.', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a lone Naner Tree in the island\'s Micro-Jungle. They left a small offering for Nang Tani, and left.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
        }

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, $success);

        return $activityLog;
    }

    private function doNormalMicroJungle(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->squirrel3->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::WOODS, 'exploring the jungle');

        $pet = $petWithSkills->getPet();

        $possibleLoot = [
            'Naner', 'Naner', 'Orange', 'Orange', 'Cacao Fruit', 'Cacao Fruit', 'Coffee Beans',
        ];

        $extraLoot = [
            'Nutmeg', 'Spicy Peps', 'Yellowy Lime'
        ];

        $loot = [];

        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());

        if($roll >= 12)
        {
            $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

            if($roll >= 16)
            {
                $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

                if($this->squirrel3->rngNextInt(1, 50) === 1)
                    $loot[] = $this->squirrel3->rngNextFromArray([ 'Rib', 'Stereotypical Bone' ]);
            }

            if($roll >= 24)
                $loot[] = $this->squirrel3->rngNextFromArray($extraLoot);

            if($roll >= 30 && $this->squirrel3->rngNextInt(1, 20) === 1)
                $loot[] = 'Silver Ore';
        }

        sort($loot);

        if(count($loot) === 0)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% entered the island\'s Micro-Jungle, but couldn\'t find anything.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% entered the island\'s Micro-Jungle, and got ' . ArrayFunctions::list_nice($loot) . '.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering' ]))
            ;

            foreach($loot as $itemName)
                $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' found this in the island\'s Micro-Jungle.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ], $activityLog);
        }

        $this->maybeGetHeatstroke($petWithSkills, $activityLog, 6, 'the Micro-Jungle');

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60) + count($loot) * 5, PetActivityStatEnum::GATHER, count($loot) > 0);

        return $activityLog;
    }

    private function foundWildHedgemaze(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->squirrel3->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::WOODS, 'exploring the woods');

        $pet = $petWithSkills->getPet();

        $possibleLoot = [
            'Smallish Pumpkin', 'Crooked Stick', 'Sweet Beet', 'Toadstool', 'Grandparoot', 'Pamplemousse',
        ];

        if($this->squirrel3->rngNextInt(1, 20) === 1)
        {
            $possibleLoot[] = $this->squirrel3->rngNextFromArray([
                'Glowing Four-sided Die',
                'Glowing Six-sided Die',
                'Glowing Eight-sided Die'
            ]);
        }

        $loot = [];

        if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY) || $petWithSkills->getClimbingBonus()->getTotal() > 0)
        {
            $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);
            $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

            if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 20)
                $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

            $lucky = false;

            if($pet->hasMerit(MeritEnum::LUCKY) && $this->squirrel3->rngNextInt(1, 15) === 1)
            {
                $loot[] = 'Melowatern';
                $lucky = true;
            }
            else if($this->squirrel3->rngNextInt(1, 75) == 1)
                $loot[] = 'Melowatern';

            if($petWithSkills->getClimbingBonus()->getTotal() > 0)
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went to the Wild Hedgemaze. It turns out mazes are way easier when you can just climb over the walls! ' . $pet->getName() . ' found ' . ArrayFunctions::list_nice($loot) . '.', '');
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went to the Wild Hedgemaze. It turns out mazes are way easier with a perfect memory! ' . $pet->getName() . ' found ' . ArrayFunctions::list_nice($loot) . '.', '');

            $tags = [ 'Gathering' ];

            if($lucky)
            {
                $activityLog
                    ->setEntry($activityLog->getEntry() . ' (Melowatern!? Lucky~!)')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;
                $tags[] = 'Lucky~!';
            }

            $activityLog
                ->addTags($this->petActivityLogTagRepository->findByNames($tags))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::GATHER, true);
        }
        else if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal()) < 15)
        {
            $pet->increaseFood(-1);

            if($this->squirrel3->rngNextInt(1, 20) + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getUmbra()->getTotal() >= 15)
            {
                $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);
                $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

                if($this->squirrel3->rngNextInt(1, 8) === 1)
                    $loot[] = 'Silver Ore';
                else if($this->squirrel3->rngNextInt(1, 8) === 1)
                    $loot[] = 'Music Note';
                else
                    $loot[] = 'Quintessence';

                if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 25)
                    $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

                $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 3));
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% got lost in a Wild Hedgemaze, and ran into a Hedgemaze Sphinx. ' . $pet->getName() . ' was able to solve its riddle, and kept exploring, coming away with ' . ArrayFunctions::list_nice($loot) . '.', '')
                    ->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => 'Gathering' ]))
                ;

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA, PetSkillEnum::NATURE ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::GATHER, true);
            }
            else
            {
                $pet->increaseEsteem(-$this->squirrel3->rngNextInt(1, 2));
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% got lost in a Wild Hedgemaze, and ran into a Hedgemaze Sphinx. The sphinx asked a really hard question; ' . $pet->getName() . ' wasn\'t able to answer it, and was consequentially ejected from the maze.', '')
                    ->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => 'Gathering' ]))
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::NATURE ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::GATHER, false);
            }
        }
        else
        {
            $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);
            $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

            if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 25)
                $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

            $lucky = false;

            if($pet->hasMerit(MeritEnum::LUCKY) && $this->squirrel3->rngNextInt(1, 20) === 1)
            {
                $loot[] = 'Melowatern';
                $lucky = true;
            }
            else if($this->squirrel3->rngNextInt(1, 100) == 1)
                $loot[] = 'Melowatern';

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wandered through a Wild Hedgemaze, and found ' . ArrayFunctions::list_nice($loot) . '.', '');

            $tags = [ 'Gathering' ];

            if($lucky)
            {
                $activityLog
                    ->setEntry($activityLog->getEntry() . ' (Melowatern!? Lucky~!)')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;
                $tags[] = 'Lucky~!';
            }

            $activityLog->addTags($this->petActivityLogTagRepository->findByNames($tags));

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ], $activityLog);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
        }

        foreach($loot as $itemName)
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' found this in a Wild Hedgemaze.', $activityLog);

        return $activityLog;
    }

    private function foundVolcano(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->squirrel3->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::VOLCANO, 'exploring the island\'s volcano');

        $pet = $petWithSkills->getPet();
        $check = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());

        if($check < 15)
        {
            $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Île Volcan', '%pet:' . $pet->getId() . '.name% explored the island\'s Volcano.');

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the island\'s Volcano, but couldn\'t find anything.', 'icons/activity-logs/confused')
                ->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => 'Gathering' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, false);
        }
        else if($this->squirrel3->rngNextInt(1, max(10, 50 - $pet->getSkills()->getIntelligence())) === 1)
        {
            $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Île Volcan', '%pet:' . $pet->getId() . '.name% climbed to the top of the island\'s Volcano.');

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% climbed to the top of the island\'s Volcano, and captured some Lightning in a Bottle!', '')
                ->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => 'Gathering' ]))
            ;

            $this->inventoryService->petCollectsItem('Lightning in a Bottle', $pet, $pet->getName() . ' captured this on the top of the island\'s Volcano!', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::GATHER, true);
        }
        else
        {
            $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Île Volcan', '%pet:' . $pet->getId() . '.name% explored the island\'s Volcano.');

            $loot = $this->itemRepository->findOneByName($this->squirrel3->rngNextFromArray([
                'Iron Ore', 'Silver Ore', 'Liquid-hot Magma', 'Hot Potato'
            ]));

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the island\'s Volcano, and got ' . $loot->getNameWithArticle() . '.', 'items/' . $loot->getImage())
                ->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => 'Gathering' ]))
            ;

            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' found this near the island\'s Volcano.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
        }

        $this->maybeGetHeatstroke($petWithSkills, $activityLog, 8, 'the Volcano');

        return $activityLog;
    }

    private function foundGypsumCave(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->squirrel3->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::UNDERGROUND, 'exploring a gypsum cave');

        $pet = $petWithSkills->getPet();
        $eideticMemory = $pet->hasMerit(MeritEnum::EIDETIC_MEMORY);
        $check = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());

        if($check >= 15 || $eideticMemory)
        {
            $loot = [
                'Gypsum'
            ];

            if($petWithSkills->getCanSeeInTheDark()->getTotal() >= 0)
            {
                if($check >= 20)
                    $loot[] = $this->squirrel3->rngNextFromArray([ 'Iron Ore', 'Toadstool', 'Gypsum', 'Gypsum', 'Gypsum', 'Limestone' ]);

                if($check >= 30)
                    $loot[] = $this->squirrel3->rngNextFromArray([ 'Silver Ore', 'Silver Ore', 'Gypsum', 'Gold Ore' ]);

                if($eideticMemory)
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored a huge cave, perfectly memorizing its layout as they went, and found ' . ArrayFunctions::list_nice($loot) . '.', '')
                        ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                    ;
                }
                else
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored a huge cave, and found ' . ArrayFunctions::list_nice($loot) . '.', '');
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a huge cave! It was too dark to explore very far, but they found ' . ArrayFunctions::list_nice($loot) . ' near the entrance.', '');
            }

            if($this->squirrel3->rngNextInt(1, 2000) < $petWithSkills->getPerception()->getTotal())
            {
                $loot[] = 'Striped Microcline';
                $pet->increaseEsteem(4);
            }

            $activityLog
                ->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => 'Mining' ]))
            ;

            foreach($loot as $item)
                $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' found this in a huge cave.', $activityLog);

            $this->petExperienceService->gainExp($pet, max(2, count($loot)), [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
        }
        else
        {
            if($petWithSkills->getCanSeeInTheDark()->getTotal() >= 0)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored a huge cave, and tried to explore it, but got lost for a while!', 'icons/activity-logs/confused')
                    ->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => 'Mining' ]))
                ;

                $pet->increaseSafety(-4);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a huge cave, and tried to explore it, but got lost in the dark for a while!', 'icons/activity-logs/confused')
                    ->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => 'Mining' ]))
                ;

                $pet->increaseSafety(-8);
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::GATHER, false);
        }

        return $activityLog;
    }

    private function foundDeepMicroJungle(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        // no "gathering distraction" here; that's been placed inside doNormalDeepMicroJungle

        if(DateFunctions::moonPhase(new \DateTimeImmutable()) === MoonPhaseEnum::FULL_MOON)
            $activityLog = $this->encounterNangTani($petWithSkills);
        else
            $activityLog = $this->doNormalDeepMicroJungle($petWithSkills);

        // more chances to get bugs in the jungle!
        if(AdventureMath::petAttractsBug($this->squirrel3, $petWithSkills->getPet(), 20))
            $this->inventoryService->petAttractsRandomBug($petWithSkills->getPet());

        return $activityLog;
    }

    private function doNormalDeepMicroJungle(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->squirrel3->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::UNDERGROUND, 'exploring the deep jungle');

        $pet = $petWithSkills->getPet();

        $possibleLoot = [
            'Naner', 'Naner', 'Mango', 'Mango', 'Cacao Fruit', 'Coffee Beans',
        ];

        $foodLoot = [];
        $extraLoot = [];

        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());

        if($roll >= 16)
        {
            $foodLoot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

            if($roll >= 18)
            {
                $foodLoot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

                if($this->squirrel3->rngNextInt(1, 40) === 1)
                    $extraLoot[] = $this->squirrel3->rngNextFromArray([ 'Rib', 'Stereotypical Bone' ]);
            }

            if($roll >= 24)
                $foodLoot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

            if($roll >= 30 && $this->squirrel3->rngNextInt(1, 10) === 1)
                $extraLoot[] = $this->squirrel3->rngNextFromArray([ 'Gold Ore', 'Gold Ore', 'Blackonite', 'Striped Microcline' ]);
        }

        $allLoot = array_merge($foodLoot, $extraLoot);
        sort($allLoot);

        if(count($allLoot) === 0)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored deep in the island\'s Micro-Jungle, but couldn\'t find anything.', 'icons/activity-logs/confused')
                ->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => 'Gathering' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored deep in the island\'s Micro-Jungle, and got ' . ArrayFunctions::list_nice($allLoot) . '.', '')
                ->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => 'Gathering' ]))
            ;

            $tropicalSpice = $this->spiceRepository->findOneByName('Tropical');

            foreach($foodLoot as $itemName)
                $this->inventoryService->petCollectsEnhancedItem($itemName, null, $tropicalSpice, $pet, $pet->getName() . ' found this deep in the island\'s Micro-Jungle.', $activityLog);

            foreach($extraLoot as $itemName)
                $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' found this deep in the island\'s Micro-Jungle.', $activityLog);

            $this->petExperienceService->gainExp($pet, $this->squirrel3->rngNextInt(2, 3), [ PetSkillEnum::NATURE ], $activityLog);
        }

        $this->maybeGetHeatstroke($petWithSkills, $activityLog, 8, 'the Micro-Jungle');

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60) + count($allLoot) * 5, PetActivityStatEnum::GATHER, count($allLoot) > 0);

        return $activityLog;
    }

    private function foundOldSettlement(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->squirrel3->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::WOODS, 'exploring the deep jungle');

        $pet = $petWithSkills->getPet();

        $extraLoot = [
            'Filthy Cloth', 'Crooked Stick', 'Canned Food',
            'String', 'Iron Bar'
        ];

        $loot = [];

        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());

        if($roll >= 15)
        {
            $loot[] = $this->squirrel3->rngNextFromArray($extraLoot);

            if($roll >= 25)
                $loot[] = 'Rusted, Busted Mechanism';

            if($roll >= 35)
                $loot[] = 'The Beginning of the Armadillos';

            if($this->squirrel3->rngNextInt(1, 25) === 1)
                $loot[] = 'No Right Turns';
        }

        if(count($loot) === 0)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored deep in the island\'s Micro-Jungle, and found a ruined settlement. They looked around for a while, but didn\'t really find anything...', 'icons/activity-logs/confused')
                ->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => 'Gathering' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ], $activityLog);
        }
        else
        {
            sort($loot);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored deep in the island\'s Micro-Jungle, and found a ruined settlement. They looked around for a while, and scavenged up ' . ArrayFunctions::list_nice($loot) . '.', '')
                ->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => 'Gathering' ]))
            ;

            foreach($loot as $itemName)
            {
                $item = $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' found this in a ruined settlement deep in the island\'s Micro-Jungle.', $activityLog);

                if($itemName === 'No Right Turns')
                    $item->setEnchantment($this->enchantmentRepository->findOneByName('Thorn-covered'));
            }

            $this->petExperienceService->gainExp($pet, 2 + count($loot), [ PetSkillEnum::NATURE ], $activityLog);
        }

        $this->maybeGetHeatstroke($petWithSkills, $activityLog, 8, 'the Micro-Jungle');

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60) + count($loot) * 5, PetActivityStatEnum::GATHER, count($loot) > 0);

        return $activityLog;
    }

    private function maybeGetHeatstroke(ComputedPetSkills $petWithSkills, PetActivityLog $activityLog, int $difficulty, string $locationName)
    {
        if($this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getStamina()->getTotal()) < 8)
        {
            $pet = $petWithSkills->getPet();

            $activityLog
                ->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => 'Heatstroke' ]))
            ;

            if($petWithSkills->getHasProtectionFromHeat()->getTotal() > 0)
            {
                $activityLog->setEntry($activityLog->getEntry() . ' ' . ucfirst($locationName) . ' was hot, but their ' . $pet->getTool()->getItem()->getName() . ' protected them.')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;
            }
            else
            {
                $pet
                    ->increaseFood(-1)
                    ->increaseSafety(-$this->squirrel3->rngNextInt(1, 2))
                ;

                // why need to have unlocked the greenhouse? just testing that you've been playing for a while
                if($this->squirrel3->rngNextInt(1, 20) === 1 && $pet->getOwner()->getUnlockedGreenhouse() !== null)
                    $activityLog->setEntry($activityLog->getEntry() . ' ' . ucfirst($locationName) . ' was CRAZY hot, and I don\'t mean in a sexy way; %pet:' . $pet->getId() . '.name% got a bit light-headed.');
                else
                    $activityLog->setEntry($activityLog->getEntry() . ' ' . ucfirst($locationName) . ' was CRAZY hot, and %pet:' . $pet->getId() . '.name% got a bit light-headed.');
            }
        }
    }
}
