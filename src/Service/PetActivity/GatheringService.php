<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetSpecies;
use App\Enum\DistractionLocationEnum;
use App\Enum\FlavorEnum;
use App\Enum\GuildEnum;
use App\Enum\MeritEnum;
use App\Enum\MoonPhaseEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetLocationEnum;
use App\Enum\PetSkillEnum;
use App\Enum\RelationshipEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Functions\ActivityHelpers;
use App\Functions\AdventureMath;
use App\Functions\ArrayFunctions;
use App\Functions\ColorFunctions;
use App\Functions\DateFunctions;
use App\Functions\EnchantmentRepository;
use App\Functions\ItemRepository;
use App\Functions\MeritRepository;
use App\Functions\NumberFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\SpiceRepository;
use App\Functions\UserQuestRepository;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\PetRepository;
use App\Service\Clock;
use App\Service\FieldGuideService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\PetFactory;
use App\Service\PetRelationshipService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use App\Service\WeatherService;
use Doctrine\ORM\EntityManagerInterface;

class GatheringService
{
    public function __construct(
        private readonly ResponseService $responseService,
        private readonly InventoryService $inventoryService,
        private readonly PetExperienceService $petExperienceService,
        private readonly TransactionService $transactionService,
        private readonly IRandom $rng,
        private readonly FieldGuideService $fieldGuideService,
        private readonly EntityManagerInterface $em,
        private readonly PetFactory $petFactory,
        private readonly GatheringDistractionService $gatheringDistractions,
        private readonly Clock $clock,
        private readonly PetRelationshipService $petRelationshipService
    )
    {
    }

    public function adventure(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $maxSkill = 10 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal() - $pet->getAlcohol() - $pet->getPsychedelic();

        $maxSkill = NumberFunctions::clamp($maxSkill, 1, 24);

        $roll = $this->rng->rngNextInt(1, $maxSkill);

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
                else if($this->rng->rngNextBool())
                    $activityLog = $this->foundMicroJungle($petWithSkills);
                else
                    $activityLog = $this->foundAbandonedQuarry($petWithSkills);
                break;
        }

        if($activityLog)
        {
            $activityLog->setChanges($changes->compare($pet));
        }

        if(AdventureMath::petAttractsBug($this->rng, $pet, 75))
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    private function foundAbandonedQuarry(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $pobosFound = UserQuestRepository::findOrCreate($this->em, $pet->getOwner(), 'Pobos Found', 0);
        $poboChance = 150 + (int)(200 * log10($pobosFound->getValue() + 1));

        if($this->rng->rngNextInt(1, 2000) < $petWithSkills->getPerception()->getTotal())
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% went to an Abandoned Quarry, and happened to find a piece of Striped Microcline!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Gathering,
                    PetActivityLogTagEnum::Location_Abandoned_Quarry
                ]))
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
            $this->inventoryService->petCollectsItem('Striped Microcline', $pet, $pet->getName() . ' found this at an Abandoned Quarry.', $activityLog);
            $pet->increaseEsteem(4);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::GATHER, true);
        }
        else if($this->rng->rngNextInt(1, $poboChance) === 1)
        {
            $newPetInfo = $this->rng->rngNextFromArray([
                [
                    'Species' => 'Pobo',
                    'FindDescription' => '%pet:' . $pet->getId() . '.name% went to an Abandoned Quarry, and happened to find a Stereotypical Bone! But when they picked it up, it began to move on its own! IT\'S POSSESSED!',
                    'ColorB' => 'ece8d0',
                ],
                [
                    'Species' => 'Catacomb Spirit',
                    'FindDescription' => '%pet:' . $pet->getId() . '.name% went to an Abandoned Quarry, and stumbled upon an ancient catacomb! As they were exploring, looking for treasure, a spirit rose from the bones and began to follow them!',
                    'ColorB' => ColorFunctions::HSL2Hex($this->rng->rngNextFloat(), 0.85, 0.5),
                ]
            ]);

            $pobosFound->setValue($pobosFound->getValue() + 1);
            $newPetSpecies = $this->em->getRepository(PetSpecies::class)->findOneBy([ 'name' => $newPetInfo['Species'] ]);

            $newPetName = $this->rng->rngNextFromArray([
                'Flit', 'Waverly', 'Mirage', 'Shadow', 'Calcium',
                'Kneecap', 'Osteal', 'Papyrus', 'Quint', 'Debris'
            ]);

            $colorA = ColorFunctions::HSL2Hex($this->rng->rngNextFloat(), 0.62, 0.53);

            $newPet = $this->petFactory->createPet(
                $pet->getOwner(), $newPetName, $newPetSpecies,
                $colorA, $newPetInfo['ColorB'],
                FlavorEnum::getRandomValue($this->rng),
                MeritRepository::findOneByName($this->em, MeritEnum::NO_SHADOW_OR_REFLECTION)
            );

            $newPet
                ->increaseLove(-8)
                ->increaseSafety(10)
                ->increaseEsteem(-8)
                ->increaseFood(10)
                ->setScale($this->rng->rngNextInt(80, 120))
            ;

            $numberOfPetsAtHome = PetRepository::getNumberAtHome($this->em, $pet->getOwner());

            $petJoinsHouse = $numberOfPetsAtHome < $pet->getOwner()->getMaxPets();

            if($pet->isInGuild(GuildEnum::LIGHT_AND_SHADOW))
            {
                $extraMessage = ActivityHelpers::PetName($pet) . ' recognized the spirit from Light and Shadow texts: a ' . $newPet->getSpecies()->getName() . '! They began to talk - it\'s name was ' . $newPet->getName() . ', and the two formed a quick connection! ';

                $this->petRelationshipService->createRelationship(
                    $pet,
                    '%pet.name% found %relationship.name% in an Abandoned Quarry.',
                    $newPet,
                    '%relationship.name% found %pet.name% in an Abandoned Quarry.',
                    RelationshipEnum::FRIEND,
                    [ RelationshipEnum::FRIENDLY_RIVAL, RelationshipEnum::FRIEND, RelationshipEnum::FRIEND, RelationshipEnum::BFF, RelationshipEnum::BFF, RelationshipEnum::FWB, RelationshipEnum::MATE ]
                );
            }
            else
                $extraMessage = '';

            $extraMessage .= 'It followed %pet:' . $pet->getId() . '.name% home';

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

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $newPetInfo['FindDescription'] . ' ' . $extraMessage)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Gathering,
                    PetActivityLogTagEnum::Location_Abandoned_Quarry
                ]))
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::ARCANA ], $activityLog);
            $pet->increaseSafety(-4);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
        }
        else if($this->rng->rngNextInt(1, 150) === 1)
        {
            $bone = $this->rng->rngNextFromArray([ 'Rib', 'Stereotypical Bone' ]);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% went to an Abandoned Quarry, and happened to find a ' . $bone . '!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Gathering,
                    PetActivityLogTagEnum::Location_Abandoned_Quarry
                ]))
            ;

            $this->inventoryService->petCollectsItem($bone, $pet, $pet->getName() . ' found this at an Abandoned Quarry!', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ], $activityLog);
            $pet->increaseEsteem(4);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
        }
        else if($petWithSkills->getStrength()->getTotal() < 4)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% found a huge block of Limestone at an Abandoned Quarry, and, with all their might, pushed, dragged, and "rolled" it home.')
                ->setIcon('items/mineral/limestone')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Gathering,
                    PetActivityLogTagEnum::Location_Abandoned_Quarry
                ]))
            ;
            $pet->increaseFood(-2);
            $this->inventoryService->petCollectsItem('Limestone', $pet, $pet->getName() . ' found this at an Abandoned Quarry. It was really heavy!', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::GATHER, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% found a huge block of Limestone at an Abandoned Quarry, and carried it home.')
                ->setIcon('items/mineral/limestone')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Gathering,
                    PetActivityLogTagEnum::Location_Abandoned_Quarry
                ]))
            ;
            $this->inventoryService->petCollectsItem('Limestone', $pet, $pet->getName() . ' found this at an Abandoned Quarry.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
        }

        return $activityLog;
    }

    private function foundNothing(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::GATHER, false);

        return PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% went out gathering, but couldn\'t find anything.')
            ->setIcon('icons/activity-logs/confused')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                PetActivityLogTagEnum::Gathering
            ]))
        ;
    }

    private function foundPaperBag(Pet $pet): PetActivityLog
    {
        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% found a Paper Bag just, like, lyin\' around.')
            ->setIcon('items/bag/paper')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                PetActivityLogTagEnum::Gathering,
                PetActivityLogTagEnum::Location_Neighborhood
            ]))
        ;

        $this->inventoryService->petCollectsItem('Paper Bag', $pet, $pet->getName() . ' found this just lyin\' around.', $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

        return $activityLog;
    }

    private function foundTeaBush(Pet $pet): PetActivityLog
    {
        if(WeatherService::getWeather(new \DateTimeImmutable(), $pet)->getRainfall() > 0 && $this->rng->rngNextInt(1, 4) === 1)
        {
            $message = '%pet:' . $pet->getId() . '.name% found a Tea Bush, and grabbed a few Tea Leaves, as well as some Worms which had surfaced to escape the rain.';

            $activityLog = $this->responseService->createActivityLog($pet, $message, 'items/veggie/tea-leaves')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Gathering,
                    PetActivityLogTagEnum::Rain,
                    PetActivityLogTagEnum::Location_Micro_Jungle,
                ]))
            ;

            $this->inventoryService->petCollectsItem('Tea Leaves', $pet, $pet->getName() . ' harvested this from a Tea Bush.', $activityLog);
            $this->inventoryService->petCollectsItem('Worms', $pet, $pet->getName() . ' found these under a Tea Bush.', $activityLog);
        }
        else
        {
            $message = '%pet:' . $pet->getId() . '.name% found a Tea Bush, and grabbed a few Tea Leaves.';

            $activityLog = $this->responseService->createActivityLog($pet, $message, 'items/veggie/tea-leaves')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Gathering,
                    PetActivityLogTagEnum::Location_Micro_Jungle,
                ]))
            ;

            $this->inventoryService->petCollectsItem('Tea Leaves', $pet, $pet->getName() . ' harvested this from a Tea Bush.', $activityLog);
            $this->inventoryService->petCollectsItem('Tea Leaves', $pet, $pet->getName() . ' harvested this from a Tea Bush.', $activityLog);

            if($this->rng->rngNextBool())
                $this->inventoryService->petCollectsItem('Tea Leaves', $pet, $pet->getName() . ' harvested this from a Tea Bush.', $activityLog);
        }

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

        return $activityLog;
    }

    private function foundBerryBush(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $getPinecone = $this->clock->getMonthAndDay() > 1225 && $this->rng->rngNextInt(1, 3) === 1;

        if($this->rng->rngNextInt(1, 8) >= 6)
        {
            $harvest = 'Blueberries';
            $additionalHarvest = $this->rng->rngNextInt(1, 4) === 1;
        }
        else
        {
            $harvest = 'Blackberries';
            $additionalHarvest = $this->rng->rngNextInt(1, 3) === 1;
        }

        if($this->rng->rngNextInt(1, 10 + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 10)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% harvested berries from a Thorny ' . $harvest . ' Bush.', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]))
            ;
        }
        else
        {
            $pet->increaseSafety(-$this->rng->rngNextInt(2, 4));
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% got scratched up harvesting berries from a Thorny ' . $harvest . ' Bush.', 'icons/activity-logs/wounded')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]))
            ;
        }

        if($getPinecone)
        {
            $activityLog
                ->setEntry($activityLog->getEntry() . ' Hm-what? There was a Pinecone in the bush, too?!')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Special Event', 'Stocking Stuffing Season' ]))
            ;

            $this->inventoryService->petCollectsItem('Pinecone', $pet, $pet->getName() . ' found this in a Thorny ' . $harvest . ' Bush.', $activityLog);
        }

        $this->inventoryService->petCollectsItem($harvest, $pet, $pet->getName() . ' harvested these from a Thorny ' . $harvest . ' Bush.', $activityLog);

        if($additionalHarvest)
            $this->inventoryService->petCollectsItem($harvest, $pet, $pet->getName() . ' harvested these from a Thorny ' . $harvest . ' Bush.', $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

        return $activityLog;
    }

    private function foundHollowLog(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->rng->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::WOODS, 'exploring the nearby woods');

        $pet = $petWithSkills->getPet();

        $toadChance = WeatherService::getWeather(new \DateTimeImmutable(), $pet)->getRainfall() > 0 ? 75 : 25;

        if($this->rng->rngNextInt(1, 100) <= $toadChance)
        {
            if($petWithSkills->getCanSeeInTheDark()->getTotal() <= 0)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a Hollow Log, but it was too dark inside to see anything.', '')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        'Gathering', 'Dark',
                        PetActivityLogTagEnum::Location_Hollow_Log
                    ]))
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::GATHER, false);
            }
            else if($this->rng->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getStealth()->getTotal() + $petWithSkills->getBrawl(false)->getTotal()) >= 15)
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'Using their ' . ActivityHelpers::SourceOfLight($petWithSkills) . ', ' . ActivityHelpers::PetName($pet) . ' looked inside a Hollow Log, and found a Huge Toad! They got the jump on it, wrestled it to the ground, and claimed its Toadstool!', 'items/fungus/toadstool')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        'Gathering', 'Fighting', 'Dark', 'Stealth',
                        PetActivityLogTagEnum::Location_Hollow_Log
                    ]))
                ;
                $this->inventoryService->petCollectsItem('Toadstool', $pet, $pet->getName() . ' harvested this from the back of a Huge Toad found inside a Hollow Log.', $activityLog);
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH, PetSkillEnum::BRAWL ], $activityLog);
                $pet->increaseEsteem($this->rng->rngNextInt(1, 2));
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);

                $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Huge Toad', $activityLog->getEntry());
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'Using their ' . ActivityHelpers::SourceOfLight($petWithSkills) . ', ' . ActivityHelpers::PetName($pet) . ' looked inside a Hollow Log, and found a Huge Toad, but it hopped into the woods, and ' . ActivityHelpers::PetName($pet) . ' lost sight of it!', '')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        'Gathering', 'Fighting', 'Dark', 'Stealth',
                        PetActivityLogTagEnum::Location_Hollow_Log
                    ]))
                ;
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH, PetSkillEnum::BRAWL ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);

                $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Huge Toad', $activityLog->getEntry());
            }
        }
        else if($pet->hasMerit(MeritEnum::BEHATTED) && $this->rng->rngNextInt(1, 75) === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'Resting on top of a Hollow Log, ' . ActivityHelpers::PetName($pet) . ' spotted a Red Bow! (Hot dang!)', 'items/hat/bow-red')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    'Gathering',
                    PetActivityLogTagEnum::Location_Hollow_Log
                ]))
            ;
            $this->inventoryService->petCollectsItem('Red Bow', $pet, $pet->getName() . ' found this on top of a Hollow Log!', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::GATHER, true);
        }
        else
        {
            $success = true;

            if($this->rng->rngNextBool())
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% broke a Crooked Stick off of a Hollow Log.', 'items/plant/stick-crooked')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        'Gathering',
                        PetActivityLogTagEnum::Location_Hollow_Log
                    ]))
                ;
                $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' broke this off of a Hollow Log.', $activityLog);
            }
            else
            {
                if($petWithSkills->getCanSeeInTheDark()->getTotal() > 0)
                {
                    $activityLog = $this->responseService->createActivityLog($pet, 'Using their ' . ActivityHelpers::SourceOfLight($petWithSkills) . ', ' . ActivityHelpers::PetName($pet) . ' looked inside a Hollow Log, and found a Grandparoot!', '')
                        ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                            'Gathering', 'Dark',
                            PetActivityLogTagEnum::Location_Hollow_Log
                        ]))
                    ;
                    $this->inventoryService->petCollectsItem('Grandparoot', $pet, $pet->getName() . ' found this growing inside a Hollow Log.', $activityLog);
                }
                else
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a Hollow Log, but it was too dark inside to see anything.', '')
                        ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                            'Gathering', 'Dark',
                            PetActivityLogTagEnum::Location_Hollow_Log
                        ]))
                    ;
                    $success = false;
                }
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::GATHER, $success);
        }

        return $activityLog;
    }

    private function foundBirdNest(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->rng->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::WOODS, 'exploring the nearby woods');

        $pet = $petWithSkills->getPet();

        if($this->rng->rngNextInt(1, 20 + $petWithSkills->getStealth()->getTotal() + $petWithSkills->getDexterity()->getTotal()) >= 10)
        {
            $foundPinecone = $this->clock->getMonthAndDay() > 1225;

            if($foundPinecone)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% stole an Egg from a Bird Nest. Hm-what? There was a Pinecone up there, too!', '');
                $activityLog
                    ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering', 'Stealth', 'Special Event', 'Stocking Stuffing Season' ]))
                ;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% stole an Egg from a Bird Nest.', '')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering', 'Stealth' ]))
                ;
            }

            $this->inventoryService->petCollectsItem('Egg', $pet, $pet->getName() . ' stole this from a Bird Nest.', $activityLog);

            if($this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal()) >= 10)
                $this->inventoryService->petCollectsItem('Fluff', $pet, $pet->getName() . ' stole this from a Bird Nest.', $activityLog);

            if($foundPinecone)
                $this->inventoryService->petCollectsItem('Pinecone', $pet, $pet->getName() . ' found this in a tree while stealing from a Bird Nest.', $activityLog);

            $pet->increaseEsteem($this->rng->rngNextInt(1, 2));
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH ], $activityLog);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
        }
        else
        {
            if($this->rng->rngNextInt(1, 20 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl()->getTotal()) >= 15)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to steal an Egg from a Bird Nest, was spotted by a parent bird, and was able to defeat it in combat!', '')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering', 'Stealth', 'Fighting' ]))
                ;
                $this->inventoryService->petCollectsItem('Egg', $pet, $pet->getName() . ' stole this from a Bird Nest, after a fight.', $activityLog);
                $this->inventoryService->petCollectsItem('Fluff', $pet, $pet->getName() . ' stole this from a Bird Nest, after a fight.', $activityLog);
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH, PetSkillEnum::BRAWL ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::HUNT, true);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to steal an Egg from a Bird Nest, but was spotted by a parent bird, and chased off!', '')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering', 'Stealth', 'Fighting' ]))
                ;
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH, PetSkillEnum::BRAWL ], $activityLog);
                $pet->increaseEsteem(-$this->rng->rngNextInt(1, 2));
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::HUNT, false);
            }
        }

        return $activityLog;
    }

    private function foundBeach(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->rng->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::BEACH, 'exploring the beach');

        $pet = $petWithSkills->getPet();

        $loot = [];
        $didWhat = 'found this at a Sandy Beach';

        if($this->rng->rngNextInt(1, 20 + $petWithSkills->getStealth()->getTotal() + $petWithSkills->getDexterity()->getTotal()) < 10)
        {
            $pet->increaseFood(-1);

            if($this->rng->rngNextInt(1, 20) + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl()->getTotal() >= 15)
            {
                $loot[] = $this->rng->rngNextFromArray([ 'Fish', 'Crooked Stick', 'Egg' ]);

                if($this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal()) >= 25)
                    $loot[] = $this->rng->rngNextFromArray([ 'Feathers', 'Talon' ]);

                $pet->increaseEsteem($this->rng->rngNextInt(1, 2));
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went to a Sandy Beach, but while looking around, was attacked by a Giant Seagull. ' . $pet->getName() . ' defeated the Giant Seagull, and took its ' . ArrayFunctions::list_nice($loot) . '.', '')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering', 'Stealth', 'Fighting' ]))
                ;
                $didWhat = 'defeated a Giant Seagull at the Beach, and got this';

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::BRAWL, PetSkillEnum::NATURE ], $activityLog);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);

                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::HUNT, true);
            }
            else
            {
                $pet->increaseEsteem(-$this->rng->rngNextInt(1, 2));
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went to a Sandy Beach, but was attacked and routed by a Giant Seagull.', '')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering', 'Stealth', 'Fighting' ]))
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::BRAWL, PetSkillEnum::NATURE ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::HUNT, false);
            }
        }
        else
        {
            $possibleLoot = [
                'Scales', 'Silica Grounds', 'Seaweed', 'Coconut',
            ];

            $loot[] = $this->rng->rngNextFromArray($possibleLoot);

            if($pet->getTool() && $pet->getTool()->fishingBonus() > 0)
                $loot[] = 'Fish';
            else if($this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal()) >= 15)
                $loot[] = $this->rng->rngNextFromArray($possibleLoot);

            if($this->rng->rngNextInt(1, 20) == 1)
                $loot[] = 'Secret Seashell';

            if($this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 25)
            {
                $moneys = $this->rng->rngNextInt(4, 12);
                $this->transactionService->getMoney($pet->getOwner(), $moneys, $pet->getName() . ' found this on a Sandy Beach.');
                $lootList = $loot;
                $lootList[] = $moneys . '~~m~~';
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went to a Sandy Beach, and stole ' . ArrayFunctions::list_nice($lootList) . ' while the seagulls weren\'t paying attention.', '')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering', 'Stealth', 'Moneys' ]))
                ;
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::STEALTH, PetSkillEnum::NATURE ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::GATHER, true);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went to a Sandy Beach, and stole ' . ArrayFunctions::list_nice($loot) . ' while the seagulls weren\'t paying attention.', '')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering', 'Stealth' ]))
                ;
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::NATURE ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
            }
        }

        foreach($loot as $itemName)
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' ' . $didWhat . '.', $activityLog);

        return $activityLog;
    }

    private function foundOvergrownGarden(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->rng->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::WOODS, 'exploring the woods');

        $pet = $petWithSkills->getPet();

        $possibleLoot = [
            'Carrot', 'Onion', 'Celery', 'Tomato', 'Beans',
            'Sweet Beet', 'Sweet Beet', 'Ginger', 'Rice Flower'
        ];

        if(WeatherService::getWeather(new \DateTimeImmutable(), $pet)->getRainfall() > 0)
            $possibleLoot[] = 'Worms';

        $loot = [];
        $didWhat = 'harvested this from an Overgrown Garden';

        if($pet->hasMerit(MeritEnum::BEHATTED))
        {
            $chanceToGetOrangeBow = 1 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal();

            if($this->rng->rngNextInt(1, 100) <= $chanceToGetOrangeBow)
                $loot[] = 'Orange Bow';
        }

        if($this->rng->rngNextInt(1, 20 + $petWithSkills->getStealth()->getTotal() + $petWithSkills->getDexterity()->getTotal()) < 10)
        {
            $pet->increaseFood(-1);

            if($this->rng->rngNextInt(1, 20) + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl()->getTotal() >= 15)
            {
                $loot[] = $this->rng->rngNextFromArray($possibleLoot);

                if($this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 25)
                    $loot[] = $this->rng->rngNextFromArray($possibleLoot);

                if($this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 15)
                    $loot[] = 'Talon';

                $pet->increaseEsteem($this->rng->rngNextInt(1, 2));
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found an Overgrown Garden, but while looking for food, was attacked by an Angry Mole. ' . $pet->getName() . ' defeated the Angry Mole, and took its ' . ArrayFunctions::list_nice($loot) . '.', 'icons/activity-logs/overgrown-garden')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering', 'Stealth', 'Fighting' ]))
                ;
                $didWhat = 'defeated an Angry Mole in an Overgrown Garden, and got this';

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::STEALTH, PetSkillEnum::BRAWL, PetSkillEnum::NATURE ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::HUNT, true);
            }
            else
            {
                $pet->increaseEsteem(-$this->rng->rngNextInt(1, 2));
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found an Overgrown Garden, but, while looking for food, was attacked and routed by an Angry Mole.', '')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering', 'Stealth', 'Fighting' ]))
                ;
                $loot = [];

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::BRAWL, PetSkillEnum::NATURE ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::HUNT, false);
            }
        }
        else
        {
            $loot[] = $this->rng->rngNextFromArray($possibleLoot);

            if($this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 15)
                $loot[] = $this->rng->rngNextFromArray($possibleLoot);

            if($this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 25)
                $loot[] = $this->rng->rngNextFromArray([ 'Avocado', 'Red', 'Orange', 'Apricot', 'Yellowy Lime' ]);

            $lucky = false;

            if($pet->hasMerit(MeritEnum::LUCKY) && $this->rng->rngNextInt(1, 20) === 1)
            {
                $loot[] = 'Honeydont';
                $lucky = true;
            }
            else if($this->rng->rngNextInt(1, 100) == 1)
                $loot[] = 'Honeydont';

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% snuck into an Overgrown Garden, and harvested ' . ArrayFunctions::list_nice($loot) . '.', 'icons/activity-logs/overgrown-garden')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering', 'Stealth' ]))
            ;

            if($lucky)
            {
                $activityLog
                    ->setEntry($activityLog->getEntry() . ' (Honeydont?! Lucky~!)')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Lucky~!' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
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
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Mining', 'Dark' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::GATHER, false);

            return $activityLog;
        }

        if($this->rng->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::UNDERGROUND, 'exploring an iron mine');

        if($this->rng->rngNextInt(1, 20) + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getStamina()->getTotal() >= 10)
        {
            $pet->increaseFood(-1);
            $tags = [ 'Mining', 'Dark' ];

            if($pet->hasMerit(MeritEnum::LUCKY) && $this->rng->rngNextInt(1, 20) === 1)
            {
                $pet->increaseEsteem(5);

                if($this->rng->rngNextBool())
                    $loot = 'Gold Ore';
                else
                    $loot = 'Silver Ore';

                $punctuation = '! Lucky~!';
                $tags[] = 'Lucky~!';
            }
            else if($this->rng->rngNextInt(1, 50) === 1)
            {
                $pet->increaseEsteem(5);
                $loot = 'Gold Ore';
                $punctuation = '!!';
            }
            else if($this->rng->rngNextInt(1, 10) === 1)
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
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, $tags))
            ;
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' dug this out of an Old Iron Mine' . $punctuation, $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::GATHER, true);
        }
        else
        {
            $pet->increaseFood(-2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found an Old Iron Mine. It was dark, but despite their ' . ActivityHelpers::SourceOfLight($petWithSkills) . ', they were unable to dig anything up before getting tired out.', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Mining', 'Dark' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::GATHER, false);
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
        if(AdventureMath::petAttractsBug($this->rng, $petWithSkills->getPet(), 25))
            $this->inventoryService->petAttractsRandomBug($petWithSkills->getPet());

        return $activityLog;
    }

    private function encounterNangTani(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getArcana()->getTotal());
        $success = $roll >= 12;

        $pet->increaseSafety($this->rng->rngNextInt(2, 4));

        if($success)
        {
            $loot = ItemRepository::findOneByName($this->em, $this->rng->rngNextFromArray([
                'Fishkebab Stew',
                'Grilled Fish',
                $this->rng->rngNextFromArray([ 'Orange', 'Yellowy Lime', 'Ponzu' ]),
                $this->rng->rngNextFromArray([ 'Honeydont Ice Cream', 'Naner Ice Cream' ]),
                'Coconut',
                'Mango',
                'Pineapple',
            ]));

            $pet->increaseEsteem($this->rng->rngNextInt(2, 4));

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a lone Naner Tree in the island\'s Micro-jungle. They left a small offering for Nang Tani... who appeared out of thin air, and gave them ' . $loot->getNameWithArticle() . '!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::ARCANA ], $activityLog);

            $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Nang Tani', '%pet:' . $pet->getId() . '.name% encountered Nang Tani at a lone Naner Tree!');

            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' received this from Nang Tani while leaving an offering at a lone Naner Tree in the island\'s Micro-jungle.', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a lone Naner Tree in the island\'s Micro-jungle. They left a small offering for Nang Tani, and left.', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::ARCANA ], $activityLog);
        }

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, $success);

        return $activityLog;
    }

    private function doNormalMicroJungle(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->rng->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::WOODS, 'exploring the jungle');

        $pet = $petWithSkills->getPet();

        $possibleLoot = [
            'Naner', 'Naner', 'Orange', 'Orange', 'Cacao Fruit', 'Cacao Fruit', 'Coffee Beans',
        ];

        $extraLoot = [
            'Nutmeg', 'Spicy Peps', 'Yellowy Lime'
        ];

        $loot = [];

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());

        if($roll >= 12)
        {
            $loot[] = $this->rng->rngNextFromArray($possibleLoot);

            if($roll >= 16)
            {
                $loot[] = $this->rng->rngNextFromArray($possibleLoot);

                if($this->rng->rngNextInt(1, 50) === 1)
                    $loot[] = $this->rng->rngNextFromArray([ 'Rib', 'Stereotypical Bone' ]);
            }

            if($roll >= 24)
                $loot[] = $this->rng->rngNextFromArray($extraLoot);

            if($roll >= 30 && $this->rng->rngNextInt(1, 20) === 1)
                $loot[] = 'Silver Ore';
        }

        sort($loot);

        if(count($loot) === 0)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% entered the island\'s Micro-jungle, but couldn\'t find anything.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% entered the island\'s Micro-jungle, and got ' . ArrayFunctions::list_nice($loot) . '.', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]))
            ;

            foreach($loot as $itemName)
                $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' found this in the island\'s Micro-jungle.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ], $activityLog);
        }

        $this->maybeGetHeatstroke($petWithSkills, $activityLog, 6, 'the Micro-jungle');

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60) + count($loot) * 5, PetActivityStatEnum::GATHER, count($loot) > 0);

        return $activityLog;
    }

    private function foundWildHedgemaze(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->rng->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::WOODS, 'exploring the woods');

        $pet = $petWithSkills->getPet();

        $possibleLoot = [
            'Smallish Pumpkin', 'Crooked Stick', 'Sweet Beet', 'Toadstool', 'Grandparoot', 'Pamplemousse',
        ];

        if($this->rng->rngNextInt(1, 20) === 1)
        {
            $possibleLoot[] = $this->rng->rngNextFromArray([
                'Glowing Four-sided Die',
                'Glowing Six-sided Die',
                'Glowing Eight-sided Die'
            ]);
        }

        $loot = [];

        if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY) || $petWithSkills->getClimbingBonus()->getTotal() > 0)
        {
            $loot[] = $this->rng->rngNextFromArray($possibleLoot);
            $loot[] = $this->rng->rngNextFromArray($possibleLoot);

            if($this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 20)
                $loot[] = $this->rng->rngNextFromArray($possibleLoot);

            $lucky = false;

            if($pet->hasMerit(MeritEnum::LUCKY) && $this->rng->rngNextInt(1, 15) === 1)
            {
                $loot[] = 'Melowatern';
                $lucky = true;
            }
            else if($this->rng->rngNextInt(1, 75) == 1)
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
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, $tags))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::GATHER, true);
        }
        else if($this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal()) < 15)
        {
            $pet->increaseFood(-1);

            if($this->rng->rngNextInt(1, 20) + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getArcana()->getTotal() >= 15)
            {
                $loot[] = $this->rng->rngNextFromArray($possibleLoot);
                $loot[] = $this->rng->rngNextFromArray($possibleLoot);

                if($this->rng->rngNextInt(1, 8) === 1)
                    $loot[] = 'Silver Ore';
                else if($this->rng->rngNextInt(1, 8) === 1)
                    $loot[] = 'Music Note';
                else
                    $loot[] = 'Quintessence';

                if($this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 25)
                    $loot[] = $this->rng->rngNextFromArray($possibleLoot);

                $pet->increaseEsteem($this->rng->rngNextInt(2, 3));
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% got lost in a Wild Hedgemaze, and ran into a Hedgemaze Sphinx. ' . $pet->getName() . ' was able to solve its riddle, and kept exploring, coming away with ' . ArrayFunctions::list_nice($loot) . '.', '')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]))
                ;

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::ARCANA, PetSkillEnum::NATURE ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::GATHER, true);
            }
            else
            {
                $pet->increaseEsteem(-$this->rng->rngNextInt(1, 2));
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% got lost in a Wild Hedgemaze, and ran into a Hedgemaze Sphinx. The sphinx asked a really hard question; ' . $pet->getName() . ' wasn\'t able to answer it, and was consequentially ejected from the maze.', '')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]))
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::ARCANA, PetSkillEnum::NATURE ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::GATHER, false);
            }
        }
        else
        {
            $loot[] = $this->rng->rngNextFromArray($possibleLoot);
            $loot[] = $this->rng->rngNextFromArray($possibleLoot);

            if($this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 25)
                $loot[] = $this->rng->rngNextFromArray($possibleLoot);

            $lucky = false;

            if($pet->hasMerit(MeritEnum::LUCKY) && $this->rng->rngNextInt(1, 20) === 1)
            {
                $loot[] = 'Melowatern';
                $lucky = true;
            }
            else if($this->rng->rngNextInt(1, 100) == 1)
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

            $activityLog->addTags(PetActivityLogTagHelpers::findByNames($this->em, $tags));

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ], $activityLog);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
        }

        foreach($loot as $itemName)
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' found this in a Wild Hedgemaze.', $activityLog);

        return $activityLog;
    }

    private function foundVolcano(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->rng->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::VOLCANO, 'exploring the island\'s volcano');

        $pet = $petWithSkills->getPet();
        $check = $this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());

        if($check < 15)
        {
            $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Île Volcan', '%pet:' . $pet->getId() . '.name% explored the island\'s Volcano.');

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the island\'s Volcano, but couldn\'t find anything.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, false);
        }
        else if($this->rng->rngNextInt(1, max(10, 50 - $pet->getSkills()->getIntelligence())) === 1)
        {
            $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Île Volcan', '%pet:' . $pet->getId() . '.name% climbed to the top of the island\'s Volcano.');

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% climbed to the top of the island\'s Volcano, and captured some Lightning in a Bottle!', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]))
            ;

            $this->inventoryService->petCollectsItem('Lightning in a Bottle', $pet, $pet->getName() . ' captured this on the top of the island\'s Volcano!', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::GATHER, true);
        }
        else
        {
            $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Île Volcan', '%pet:' . $pet->getId() . '.name% explored the island\'s Volcano.');

            $loot = ItemRepository::findOneByName($this->em, $this->rng->rngNextFromArray([
                'Iron Ore', 'Silver Ore', 'Liquid-hot Magma', 'Hot Potato'
            ]));

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the island\'s Volcano, and got ' . $loot->getNameWithArticle() . '.', 'items/' . $loot->getImage())
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]))
            ;

            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' found this near the island\'s Volcano.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
        }

        $this->maybeGetHeatstroke($petWithSkills, $activityLog, 8, 'the Volcano');

        return $activityLog;
    }

    private function foundGypsumCave(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->rng->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::UNDERGROUND, 'exploring a gypsum cave');

        $pet = $petWithSkills->getPet();
        $eideticMemory = $pet->hasMerit(MeritEnum::EIDETIC_MEMORY);
        $check = $this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());

        if($check >= 15 || $eideticMemory)
        {
            $loot = [
                'Gypsum'
            ];

            if($petWithSkills->getCanSeeInTheDark()->getTotal() >= 0)
            {
                if($check >= 20)
                    $loot[] = $this->rng->rngNextFromArray([ 'Iron Ore', 'Toadstool', 'Gypsum', 'Gypsum', 'Gypsum', 'Limestone' ]);

                if($check >= 30)
                    $loot[] = $this->rng->rngNextFromArray([ 'Silver Ore', 'Silver Ore', 'Gypsum', 'Gold Ore' ]);

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

            if($this->rng->rngNextInt(1, 2000) < $petWithSkills->getPerception()->getTotal())
            {
                $loot[] = 'Striped Microcline';
                $pet->increaseEsteem(4);
            }

            $activityLog
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Mining' ]))
            ;

            foreach($loot as $item)
                $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' found this in a huge cave.', $activityLog);

            $this->petExperienceService->gainExp($pet, max(2, count($loot)), [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
        }
        else
        {
            if($petWithSkills->getCanSeeInTheDark()->getTotal() >= 0)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored a huge cave, and tried to explore it, but got lost for a while!', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Mining' ]))
                ;

                $pet->increaseSafety(-4);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a huge cave, and tried to explore it, but got lost in the dark for a while!', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Mining' ]))
                ;

                $pet->increaseSafety(-8);
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::GATHER, false);
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
        if(AdventureMath::petAttractsBug($this->rng, $petWithSkills->getPet(), 20))
            $this->inventoryService->petAttractsRandomBug($petWithSkills->getPet());

        return $activityLog;
    }

    private function doNormalDeepMicroJungle(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->rng->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::UNDERGROUND, 'exploring the deep jungle');

        $pet = $petWithSkills->getPet();

        $possibleLoot = [
            'Naner', 'Naner', 'Mango', 'Mango', 'Cacao Fruit', 'Coffee Beans',
        ];

        $foodLoot = [];
        $extraLoot = [];

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());

        if($roll >= 16)
        {
            $foodLoot[] = $this->rng->rngNextFromArray($possibleLoot);

            if($roll >= 18)
            {
                $foodLoot[] = $this->rng->rngNextFromArray($possibleLoot);

                if($this->rng->rngNextInt(1, 40) === 1)
                    $extraLoot[] = $this->rng->rngNextFromArray([ 'Rib', 'Stereotypical Bone' ]);
            }

            if($roll >= 24)
                $foodLoot[] = $this->rng->rngNextFromArray($possibleLoot);

            if($roll >= 30 && $this->rng->rngNextInt(1, 10) === 1)
                $extraLoot[] = $this->rng->rngNextFromArray([ 'Gold Ore', 'Gold Ore', 'Blackonite', 'Striped Microcline' ]);
        }

        $allLoot = array_merge($foodLoot, $extraLoot);
        sort($allLoot);

        if(count($allLoot) === 0)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored deep in the island\'s Micro-jungle, but couldn\'t find anything.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored deep in the island\'s Micro-jungle, and got ' . ArrayFunctions::list_nice($allLoot) . '.', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]))
            ;

            $tropicalSpice = SpiceRepository::findOneByName($this->em, 'Tropical');

            foreach($foodLoot as $itemName)
                $this->inventoryService->petCollectsEnhancedItem($itemName, null, $tropicalSpice, $pet, $pet->getName() . ' found this deep in the island\'s Micro-jungle.', $activityLog);

            foreach($extraLoot as $itemName)
                $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' found this deep in the island\'s Micro-jungle.', $activityLog);

            $this->petExperienceService->gainExp($pet, $this->rng->rngNextInt(2, 3), [ PetSkillEnum::NATURE ], $activityLog);
        }

        $this->maybeGetHeatstroke($petWithSkills, $activityLog, 8, 'the Micro-jungle');

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60) + count($allLoot) * 5, PetActivityStatEnum::GATHER, count($allLoot) > 0);

        return $activityLog;
    }

    private function foundOldSettlement(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->rng->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::WOODS, 'exploring the deep jungle');

        $pet = $petWithSkills->getPet();

        $extraLoot = [
            'Filthy Cloth', 'Crooked Stick', 'Canned Food',
            'String', 'Iron Bar'
        ];

        $loot = [];

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());

        if($roll >= 15)
        {
            $loot[] = $this->rng->rngNextFromArray($extraLoot);

            if($roll >= 25)
                $loot[] = 'Rusted, Busted Mechanism';

            if($roll >= 35)
                $loot[] = 'The Beginning of the Armadillos';

            if($this->rng->rngNextInt(1, 25) === 1)
                $loot[] = 'No Right Turns';
        }

        if(count($loot) === 0)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored deep in the island\'s Micro-jungle, and found a ruined settlement. They looked around for a while, but didn\'t really find anything...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ], $activityLog);
        }
        else
        {
            sort($loot);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored deep in the island\'s Micro-jungle, and found a ruined settlement. They looked around for a while, and scavenged up ' . ArrayFunctions::list_nice($loot) . '.', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]))
            ;

            foreach($loot as $itemName)
            {
                $item = $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' found this in a ruined settlement deep in the island\'s Micro-jungle.', $activityLog);

                if($itemName === 'No Right Turns')
                    $item->setEnchantment(EnchantmentRepository::findOneByName($this->em, 'Thorn-covered'));
            }

            $this->petExperienceService->gainExp($pet, 2 + count($loot), [ PetSkillEnum::NATURE ], $activityLog);
        }

        $this->maybeGetHeatstroke($petWithSkills, $activityLog, 8, 'the Micro-jungle');

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60) + count($loot) * 5, PetActivityStatEnum::GATHER, count($loot) > 0);

        return $activityLog;
    }

    private function maybeGetHeatstroke(ComputedPetSkills $petWithSkills, PetActivityLog $activityLog, int $difficulty, string $locationName)
    {
        if($this->rng->rngNextInt(1, 10 + $petWithSkills->getStamina()->getTotal()) < 8)
        {
            $pet = $petWithSkills->getPet();

            $activityLog
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Heatstroke' ]))
            ;

            if($petWithSkills->getHasProtectionFromHeat()->getTotal() > 0)
            {
                $activityLog->setEntry($activityLog->getEntry() . ' ' . ucfirst($locationName) . ' was hot, but their ' . ActivityHelpers::SourceOfHeatProtection($petWithSkills) . ' protected them.')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;
            }
            else
            {
                $pet
                    ->increaseFood(-1)
                    ->increaseSafety(-$this->rng->rngNextInt(1, 2))
                ;

                // why need to have unlocked the greenhouse? just testing that you've been playing for a while
                if($this->rng->rngNextInt(1, 20) === 1 && $pet->getOwner()->hasUnlockedFeature(UnlockableFeatureEnum::Greenhouse))
                    $activityLog->setEntry($activityLog->getEntry() . ' ' . ucfirst($locationName) . ' was CRAZY hot, and I don\'t mean in a sexy way; %pet:' . $pet->getId() . '.name% got a bit light-headed.');
                else
                    $activityLog->setEntry($activityLog->getEntry() . ' ' . ucfirst($locationName) . ' was CRAZY hot, and %pet:' . $pet->getId() . '.name% got a bit light-headed.');
            }
        }
    }
}
