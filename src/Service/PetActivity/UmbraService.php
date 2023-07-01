<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\GuildEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\SpiritCompanionStarEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Functions\GrammarFunctions;
use App\Functions\InventoryModifierFunctions;
use App\Functions\NumberFunctions;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Model\WeatherData;
use App\Repository\DragonRepository;
use App\Repository\ItemRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\SpiceRepository;
use App\Service\CalendarService;
use App\Service\FieldGuideService;
use App\Service\HattierService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\TransactionService;
use App\Service\WeatherService;

class UmbraService
{
    private ResponseService $responseService;
    private InventoryService $inventoryService;
    private PetExperienceService $petExperienceService;
    private TransactionService $transactionService;
    private ItemRepository $itemRepository;
    private StrangeUmbralEncounters $strangeUmbralEncounters;
    private DragonRepository $dragonRepository;
    private WeatherService $weatherService;
    private IRandom $squirrel3;
    private HattierService $hattierService;
    private FieldGuideService $fieldGuideService;
    private PetActivityLogTagRepository $petActivityLogTagRepository;
    private SpiceRepository $spiceRepository;
    private LeonidsService $leonidsService;
    private CalendarService $calendarService;
    private GuildService $guildService;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetExperienceService $petExperienceService,
        TransactionService $transactionService, GuildService $guildService, StrangeUmbralEncounters $strangeUmbralEncounters,
        ItemRepository $itemRepository, FieldGuideService $fieldGuideService,
        DragonRepository $dragonRepository, Squirrel3 $squirrel3, WeatherService $weatherService,
        HattierService $hattierService, PetActivityLogTagRepository $petActivityLogTagRepository,
        SpiceRepository $spiceRepository, LeonidsService $leonidsService, CalendarService $calendarService
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->transactionService = $transactionService;
        $this->guildService = $guildService;
        $this->itemRepository = $itemRepository;
        $this->strangeUmbralEncounters = $strangeUmbralEncounters;
        $this->dragonRepository = $dragonRepository;
        $this->squirrel3 = $squirrel3;
        $this->weatherService = $weatherService;
        $this->hattierService = $hattierService;
        $this->fieldGuideService = $fieldGuideService;
        $this->petActivityLogTagRepository = $petActivityLogTagRepository;
        $this->spiceRepository = $spiceRepository;
        $this->leonidsService = $leonidsService;
        $this->calendarService = $calendarService;
    }

    public function adventure(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();

        $activityLog = null;
        $changes = new PetChanges($pet);

        if($this->calendarService->isLeonidPeakOrAdjacent() && $this->squirrel3->rngNextInt(1, 4) === 1)
        {
            $activityLog = $this->leonidsService->adventure($petWithSkills);
        }
        else
        {
            $weather = $this->weatherService->getWeather(new \DateTimeImmutable(), $pet);

            $skill = 10 + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getUmbra()->getTotal(); // psychedelics bonus is built into getUmbra()

            $skill = NumberFunctions::clamp($skill, 1, 23);

            $roll = $this->squirrel3->rngNextInt(1, $skill);

            switch($roll)
            {
                case 1:
                case 2:
                case 3:
                    $activityLog = $this->foundNothing($pet, $roll);
                    break;
                case 4:
                case 5:
                case 6:
                    $activityLog = $this->foundScragglyBush($petWithSkills);
                    break;
                case 7:
                case 8:
                    $activityLog = $this->helpedLostSoul($petWithSkills);
                    break;
                case 9:
                    $activityLog = $this->found2Moneys($petWithSkills, $weather);
                    break;

                case 10:
                    $activityLog = $this->strangeUmbralEncounters->adventure($petWithSkills);
                    break;

                case 11:
                case 12:
                    $activityLog = $this->fightEvilSpirit($petWithSkills);
                    break;

                case 13:
                    $dragon = $this->dragonRepository->findOneBy([ 'owner' => $pet->getOwner() ]);

                    if($dragon)
                        $activityLog = $this->visitLibraryOfFire($petWithSkills);
                    else
                        $activityLog = $this->foundNothing($pet, $roll);
                    break;

                case 14:
                    $activityLog = $this->found2Moneys($petWithSkills, $weather);
                    break;

                case 15:
                case 16:
                    $activityLog = $this->fishingAtRiver($petWithSkills, $weather);
                    break;
                case 17:
                    $activityLog = $this->strangeUmbralEncounters->adventure($petWithSkills);
                    break;
                case 18:
                    $activityLog = $this->gatheringAtTheNoetala($petWithSkills);
                    break;
                case 19:
                    $activityLog = $this->foundVampireCastle($petWithSkills);
                    break;
                case 20:
                case 21:
                    $activityLog = $this->frozenQuag($petWithSkills);
                    break;
                case 22:
                    $activityLog = $this->fightAbandondero($petWithSkills);
                    break;
                case 23:
                    $activityLog = $this->foundCursedGarden($petWithSkills);
                    break;
            }
        }

        if($activityLog)
        {
            $activityLog->setChanges($changes->compare($pet));
        }
    }

    private function foundNothing(Pet $pet, int $roll): PetActivityLog
    {
        $exp = ceil($roll / 10);

        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% crossed into the Umbra, but the Storm was too harsh; %pet:' . $pet->getId() . '.name% retreated before finding anything.', 'icons/activity-logs/confused')
            ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra' ]))
        ;

        $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::UMBRA ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, false);

        return $activityLog;
    }

    private function visitLibraryOfFire(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($this->squirrel3->rngNextInt(1, 10) === 1)
        {
            // visit the library's arboretum

            if($this->squirrel3->rngNextInt(1, 5) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Library of Fire\'s arboretum, and found the brick with your name on it!', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra' ]))
                ;

                $pet
                    ->increaseEsteem($this->squirrel3->rngNextInt(3, 6))
                    ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
                ;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Library of Fire\'s arboretum.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra' ]))
                ;

                $pet->increaseSafety($this->squirrel3->rngNextInt(2, 4));
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, 'true');
        }
        else if($this->squirrel3->rngNextInt(1, 3) === 1 && $pet->getGuildMembership() === null && !$pet->hasMerit(MeritEnum::AFFECTIONLESS))
        {
            $activityLog = $this->guildService->joinGuildUmbra($petWithSkills);
        }
        else
        {
            // visit a floor of the library and read some books

            $floor = $this->squirrel3->rngNextInt(8, 414);

            if($floor === 29)
                $floor = 28;
            else if($floor === 30)
                $floor = 31;

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the ' . GrammarFunctions::ordinalize($floor) . ' floor of the Library of Fire, and read a random book...', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra' ]))
            ;

            $pet->increaseSafety($this->squirrel3->rngNextInt(2, 4));
            $this->petExperienceService->gainExp($pet, $this->squirrel3->rngNextInt(1, 2), PetSkillEnum::getValues(), $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, 'true');
        }

        $activityLog->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY);

        return $activityLog;
    }

    private function foundScragglyBush(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $skill = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getGatheringBonus()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($skill >= 11)
        {
            $reward = $this->squirrel3->rngNextInt(1, 3);

            if($reward === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'In the Umbra, ' . '%pet:' . $pet->getId() . '.name% found an outcropping of rocks where the full force of the Storm could not reach. Some Grandparoot was growing there; %pet:' . $pet->getId() . '.name% took one.', 'items/veggie/grandparoot')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Gathering' ]))
                ;
                $this->inventoryService->petCollectsItem('Grandparoot', $pet, $pet->getName() . ' pulled this up from between some rocks in the Umbra.', $activityLog);
            }
            else if($reward === 2)
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'In the Umbra, ' . '%pet:' . $pet->getId() . '.name% found an outcropping of rocks where the full force of the Storm could not reach. A dry bush once grew there; %pet:' . $pet->getId() . '.name% took a Crooked Stick from its remains.', 'items/plant/stick-crooked')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Gathering' ]))
                ;
                $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' took this from the remains of a dead bush in the Umbra.', $activityLog);
            }
            else // if($reward === 3)
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'In the Umbra, ' . '%pet:' . $pet->getId() . '.name% found an outcropping of rocks where the full force of the Storm could not reach. A small Blackberry bush was growing there; %pet:' . $pet->getId() . '.name% took a few berries.', 'items/fruit/blackberries')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Gathering' ]))
                ;
                $this->inventoryService->petCollectsItem('Blackberries', $pet, $pet->getName() . ' harvested these exceptionally-dark Blackberries from a rock-sheltered berry bush in the Umbra.', $activityLog);
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, 'true');

            return $activityLog;
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'In the Umbra, ' . '%pet:' . $pet->getId() . '.name% found an outcropping of rocks where the full force of the Storm could not reach. Some weeds were growing there, but nothing of value.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Gathering' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, false);

            return $activityLog;
        }
    }

    private function helpedLostSoul(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $hasEideticMemory = $pet->hasMerit(MeritEnum::EIDETIC_MEMORY);
        $hasRelevantSpirit = $pet->getSpiritCompanion() !== null && $pet->getSpiritCompanion()->getStar() === SpiritCompanionStarEnum::ALTAIR;

        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getUmbra()->getTotal());

        $rewards = [
            'Quintessence' => 'some',
            'Music Note' => 'a',
            'Ginger' => 'some',
            'Oil' => 'some',
            'Silica Grounds' => 'some'
        ];

        if($this->squirrel3->rngNextInt(1, 10) === 1)
            $rewards['Broccolinomicon'] = 'a copy of the';

        $reward = array_rand($rewards);

        if($hasEideticMemory || $hasRelevantSpirit)
        {
            if($hasEideticMemory && !($hasRelevantSpirit && $this->squirrel3->rngNextBool()))
            {
                $messageDetail = $pet->getName() . ' had already memorized the lay of the land, and pointed the way';
                $useSpirit = false;
            }
            else
            {
                $messageDetail = $pet->getName() . ' and ' . $pet->getSpiritCompanion()->getName() . ' were able to point the way';
                $useSpirit = true;
            }

            if($this->squirrel3->rngNextInt(1, 2) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% met a friendly spirit lost in the Umbra. ' . $messageDetail . '; the spirit was very thankful, and insisted that ' . $pet->getName() . ' take ' . $rewards[$reward] . ' ' . $reward . '.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra' ]))
                ;
                $this->inventoryService->petCollectsItem($reward, $pet, $pet->getName() . ' received this from a friendly spirit as thanks for helping it navigate the Umbra.', $activityLog);
                $pet->increaseEsteem(1);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% met a friendly spirit lost in the Umbra. ' . $messageDetail . '; the spirit was very thankful, and wished ' . $pet->getName() . ' well.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra' ]))
                ;
                $pet->increaseEsteem(4);
            }

            if($useSpirit)
                $activityLog->addTags($this->petActivityLogTagRepository->findByNames([ 'Spirit Companion' ]));

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);

            $activityLog->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT);

            return $activityLog;
        }

        if($roll >= 14)
        {
            if($this->squirrel3->rngNextInt(1, 2) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% met a friendly spirit lost in the Umbra. ' . $pet->getName() . ' was able to point the way; the spirit was very thankful, and insisted that ' . $pet->getName() . ' take ' . $rewards[$reward] . ' ' . $reward . '.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra' ]))
                ;
                $this->inventoryService->petCollectsItem($reward, $pet, $pet->getName() . ' received this from a friendly spirit as thanks for helping it navigate the Umbra.', $activityLog);
                $pet->increaseEsteem(1);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% met a friendly spirit lost in the Umbra. ' . $pet->getName() . ' was able to point the way; the spirit was very thankful, and wished ' . $pet->getName() . ' well.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra' ]))
                ;
                $pet->increaseEsteem(4);
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);

            return $activityLog;
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% met a friendly spirit lost in the Umbra. It asked for directions, but ' . $pet->getName() . ' didn\'t know how to help.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, false);

            return $activityLog;
        }
    }

    private function foundDrizzlyBear(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStealth()->getTotal());
        $pet = $petWithSkills->getPet();

        $petName = ActivityHelpers::PetName($pet);

        $success = $roll >= 15;

        if($success)
        {
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA, PetSkillEnum::STEALTH ]);

            $drizzlyBearDiscovery = 'While exploring the Umbra, ' . $petName . ' stumbled upon a Drizzly Bear emerging from a dark river. It shook itself off, sending rain into the material world.';

            $activityLog = $this->responseService->createActivityLog($pet, $drizzlyBearDiscovery . ' ' . $petName . ' caught some, and brought it home.' , '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Stealth' ]))
            ;

            $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' caught this off a Drizzly Bear shaking itself dry.', $activityLog);

            $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Drizzly Bear', $drizzlyBearDiscovery);
        }
        else
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::STEALTH ]);

            $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . $petName . ' stumbled upon a Drizzly Bear emerging from a dark river. ' . $petName . ' tried to hide, but the Drizzly Bear spotted them, so ' . $petName . ' backed off, and returned home.' , '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Stealth' ]))
            ;
        }

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, $success);

        return $activityLog;
    }

    private function found2Moneys(ComputedPetSkills $petWithSkills, WeatherData $weather): PetActivityLog
    {
        if($weather->getRainfall() > 0 && $weather->getRainfall() < 2)
            return $this->foundDrizzlyBear($petWithSkills);

        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, false);

        if($pet->hasMerit(MeritEnum::LUCKY) && $this->squirrel3->rngNextInt(1, 80) === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% walked along a dark river for a while. On its shore, ' . $pet->getName() . ' spotted a Little Strongbox! Lucky~!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Lucky~!' ]))
            ;

            $this->inventoryService->petCollectsItem('Little Strongbox', $pet, $pet->getName() . ' found this on the shores of a dark river in the Umbra.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);

            return $activityLog;
        }

        if($this->squirrel3->rngNextInt(1, 100) === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% walked along a dark river for a while. On its shore, ' . $pet->getName() . ' spotted a Little Strongbox, and took it!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra' ]))
            ;

            $this->inventoryService->petCollectsItem('Little Strongbox', $pet, $pet->getName() . ' found this on the shores of a dark river in the Umbra.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);

            return $activityLog;
        }

        if($pet->hasMerit(MeritEnum::LUCKY))
            $die = $this->squirrel3->rngNextFromArray([ 'Glowing Four-sided Die', 'Glowing Six-sided Die', 'Glowing Eight-sided Die', 'Glowing Ten-sided Die' ]);
        else
            $die = $this->squirrel3->rngNextFromArray([ 'Glowing Four-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Eight-sided Die' ]);

        if($pet->hasMerit(MeritEnum::LUCKY) && $this->squirrel3->rngNextInt(1, 50) === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% walked along a dark river for a while. On its shore, ' . $pet->getName() . ' spotted a ' . $die . '! Lucky~!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Lucky~!' ]))
            ;

            $this->inventoryService->petCollectsItem($die, $pet, $pet->getName() . ' found this on the shores of a dark river in the Umbra.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);

            return $activityLog;
        }

        if($this->squirrel3->rngNextInt(1, 80) === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% walked along a dark river for a while. On its shore, ' . $pet->getName() . ' spotted a ' . $die . ', and took it!', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra' ]))
            ;

            $this->inventoryService->petCollectsItem($die, $pet, $pet->getName() . ' found this on the shores of a dark river in the Umbra.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);

            return $activityLog;
        }

        $this->transactionService->getMoney($pet->getOwner(), 2, $pet->getName() . ' found this on the shores of a dark river in the Umbra.');

        $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% walked along a dark river for a while. On its shore, ' . $pet->getName() . ' spotted 2~~m~~. No one else was around, so...', 'icons/activity-logs/moneys')
            ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Moneys' ]))
        ;

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);

        return $activityLog;
    }

    private function fightEvilSpirit(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $prizes = [
            'Silica Grounds', 'Quintessence', 'Aging Powder', 'Fluff'
        ];

        if($this->squirrel3->rngNextInt(1, 100) === 1)
            $prize = 'Forgetting Scroll';
        else if($this->squirrel3->rngNextInt(1, 50) === 1)
            $prize = 'Spirit Polymorph Potion Recipe';
        else if($this->squirrel3->rngNextInt(1, 100) === 1)
            $prize = 'Blackonite';
        else if($this->squirrel3->rngNextInt(1, 50) === 1)
            $prize = 'Charcoal';
        else
            $prize = $this->squirrel3->rngNextFromArray($prizes);

        if($pet->isInGuild(GuildEnum::LIGHT_AND_SHADOW))
        {
            $skill = 20 + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal();

            $roll = $this->squirrel3->rngNextInt(1, $skill);
            $success = $roll >= 12;

            if($success)
            {
                $pet->getGuildMembership()->increaseReputation();

                $prizeItem = $this->itemRepository->findOneByName($prize);

                $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% encountered a super gross-looking mummy dragging its long arms through the Umbral sand. It screeched and swung wildly; but ' . $pet->getName() . ' endured its attacks long enough to calm it down! It eventually wandered away, dropping ' . $prizeItem->getNameWithArticle() . ' as it went...', 'guilds/light-and-shadow')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Guild' ]))
                ;

                $this->inventoryService->petCollectsItem($prize, $pet, $pet->getName() . ' defeated a gross-looking mummy with crazy-long arms, and took this.', $activityLog);

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);

                return $activityLog;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% encountered a super gross-looking mummy dragging its long arms through the Umbral sand. It screeched and swung wildly. ' . $pet->getName() . ' tried to endure its attacks long enough to calm it down, but was eventually forced to retreat!', 'guilds/light-and-shadow')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Guild' ]))
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, false);

                return $activityLog;
            }
        }

        $skill = 20 + max($petWithSkills->getBrawl()->getTotal(), $petWithSkills->getUmbra()->getTotal()) + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal();

        $roll = $this->squirrel3->rngNextInt(1, $skill);
        $success = $roll >= 12;

        $isRanged = $pet->getTool() && $pet->getTool()->rangedOnly() && $pet->getTool()->brawlBonus() > 0;

        $defeated = $isRanged ? 'shot it down' : 'beat it back';

        if($success)
        {
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);

            if($pet->isInGuild(GuildEnum::THE_UNIVERSE_FORGETS))
            {
                $pet->getGuildMembership()->increaseReputation();
                $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% encountered a super gross-looking mummy dragging its long arms through the Umbral sand. It screeched and swung wildly; but ' . $pet->getName() . ' ' . $defeated . ', and claimed its ' . $prize . '!', 'guilds/the-universe-forgets')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Fighting' ]))
                ;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% encountered a super gross-looking mummy dragging its long arms through the Umbral sand. It screeched and swung wildly; but ' . $pet->getName() . ' ' . $defeated . ', and claimed its ' . $prize . '!', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Fighting' ]))
                ;
            }

            $this->inventoryService->petCollectsItem($prize, $pet, $pet->getName() . ' defeated a gross-looking mummy with crazy-long arms, and took this.', $activityLog);
        }
        else
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);
            $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% encountered a super gross-looking mummy dragging its long arms through the Umbral sand. It screeched and swung wildly; ' . $pet->getName() . ' made a hasty retreat.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Fighting' ]))
            ;
        }

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, $roll >= $success);

        return $activityLog;
    }

    private function fishingAtRiver(ComputedPetSkills $petWithSkills, WeatherData $weather): PetActivityLog
    {
        if($weather->getRainfall() > 0 && $weather->getRainfall() < 2)
            return $this->foundDrizzlyBear($petWithSkills);

        $pet = $petWithSkills->getPet();

        $fishingSkill = $this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() + $petWithSkills->getUmbra()->getTotal());

        $roll = $this->squirrel3->rngNextInt(1, $fishingSkill);

        if($this->squirrel3->rngNextInt(1, 200) == 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% decided to fish in a dark river, and pulled up a Jelling Polyp!', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Fishing' ]))
                ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
            ;

            $pet->increaseEsteem(6);

            $spice = $this->spiceRepository->findOneByName('Cosmic');

            $this->inventoryService->petCollectsEnhancedItem('Jelling Polyp', null, $spice, $pet, $pet->getName() . ' got this from fishing in the Umbra.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ], $activityLog);
        }
        else if($roll >= 13)
        {
            $prizes = [ 'Fish' ];

            if($this->squirrel3->rngNextInt(1, 2) == 1)
            {
                $prizes[] = 'Dark Scales';

                if($this->squirrel3->rngNextInt(1, 10) === 1)
                    $prizes[] = 'Secret Seashell';
                else
                    $prizes[] = 'Seaweed';

                $fish = 'some horrible, writhing thing';
            }
            else
            {
                $prizes[] = 'Quintessence';

                if($this->squirrel3->rngNextInt(1, 4) === 1)
                    $prizes[] = 'Music Note';
                else
                    $prizes[] = 'Creamy Milk';

                $fish = 'an oddly-beautiful, squirming mass';
            }

            $this->squirrel3->rngNextShuffle($prizes);

            if($roll >= 18)
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% decided to fish in a dark river. They caught ' . $fish . ', and harvested its ' . $prizes[0] . ' and ' . $prizes[1] . '.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Fishing' ]))
                ;
                $this->inventoryService->petCollectsItem($prizes[0], $pet, $pet->getName() . ' got this from fishing in the Umbra.', $activityLog);
                $this->inventoryService->petCollectsItem($prizes[1], $pet, $pet->getName() . ' got this from fishing in the Umbra.', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% decided to fish in a dark river. They caught ' . $fish . ', and harvested its ' . $prizes[0] . '.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Fishing' ]))
                ;
                $this->inventoryService->petCollectsItem($prizes[0], $pet, $pet->getName() . ' got this from fishing in the Umbra.', $activityLog);
            }

            $activityLog->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% decided to fish in a dark river. Plenty of strange things swam by, but ' . $pet->getName() . ' didn\'t manage to catch any of them.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Fishing' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
        }

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::FISH, $roll >= 13);

        return $activityLog;
    }

    private function gatheringAtTheNoetala(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $loot = [ 'Noetala Egg' ];

        if($pet->hasMerit(MeritEnum::BEHATTED) && $this->squirrel3->rngNextInt(1, 100) === 1)
        {
            $activityLog = $this->hattierService->petMaybeUnlockAura(
                $pet,
                'Umbral',
                ActivityHelpers::PetName($pet) . ' fell into a giant cocoon. While finding their way out, ' . ActivityHelpers::PetName($pet) . ' noticed that the swirling mists at their feet were particularly beautiful... and so just put some on their hat! (Why not!)',
                ActivityHelpers::PetName($pet) . ' fell into a giant cocoon. While finding their way out, ' . ActivityHelpers::PetName($pet) . ' noticed that the swirling mists at their feet were particularly beautiful...',
                ActivityHelpers::PetName($pet) . ' was captivated by the swirling mists in Noetala\'s giant cocoon...'
            );

            if($activityLog)
            {
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);

                $activityLog->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra' ]));

                return $activityLog;
            }
        }

        if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getStealth()->getTotal() + $petWithSkills->getDexterity()->getTotal()) < 15)
        {
            $pet->increaseFood(-1);

            if($this->squirrel3->rngNextInt(1, 20) + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl()->getTotal() >= 20)
            {
                if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 25)
                    $loot[] = 'Quintessence';

                if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 15)
                    $loot[] = 'Fluff';

                $pet->increaseEsteem(3);
                $activityLog = $this->responseService->createActivityLog($pet, ActivityHelpers::PetName($pet) . ' fell into a giant cocoon. While trying to find their way out, ' . ActivityHelpers::PetName($pet) . ' was ambushed by one of Noetala\'s guard, but was able to defeat it!', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Stealth', 'Fighting' ]))
                ;

                $didWhat = 'defeated one of Noetala\'s guard, and took this';

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::STEALTH, PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::HUNT, true);
            }
            else
            {
                $loot = [ 'Fluff' ];

                $pet->increaseEsteem(-3);
                $pet->increaseSafety(-$this->squirrel3->rngNextInt(4, 8));
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% fell into a giant cocoon. While trying to find their way out, ' . $pet->getName() . ' was ambushed by one of Noetala\'s guard, and was wounded and covered in Fluff before being able to escape!', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Stealth', 'Fighting' ]))
                ;
                $didWhat = 'was attacked by one of Noetala\'s guard, and covered in this';

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::HUNT, false);
            }
        }
        else
        {
            $didWhat = 'stole this from a giant cocoon';

            if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 25)
                $loot[] = 'Quintessence';

            if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 15)
                $loot[] = 'Fluff';

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% stumbled upon Noetala\'s giant cocoon. They snuck around inside for a bit, and made off with ' . ArrayFunctions::list_nice($loot) . '.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Stealth' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::UMBRA ]);

            if($this->squirrel3->rngNextInt(1, 100) === 1)
                $activityLog->setEntry($activityLog->getEntry() . ' ("Snuck"? "Sneaked"? I dunno. One of thems.)');

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);
        }

        foreach($loot as $itemName)
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' ' . $didWhat . '.', $activityLog);

        return $activityLog;
    }

    private function foundVampireCastle(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();

        $umbraCheck = $this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getPerception()->getTotal());

        if($umbraCheck >= 12)
        {
            // realize it's vampires; chance to steal
            $stealthCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getStealth()->getTotal() + $petWithSkills->getDexterity()->getTotal());

            if($stealthCheck >= 16)
            {
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::STEALTH, PetSkillEnum::UMBRA ]);
                $loot = $this->squirrel3->rngNextFromArray([ 'Blood Wine', 'Linens and Things' ]);

                $pet->increaseEsteem(2);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% stumbled upon a castle that was obviously home to vampires. They snuck around inside for a while, and made off with some ' . $loot . '.', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Stealth' ]))
                ;

                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' stole this from a vampire castle.', $activityLog);
            }
            else
            {
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::UMBRA ]);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% stumbled upon a castle that was obviously home to vampires. They snuck around inside for a while, but couldn\'t find a good opportunity to steal anything.', 'icons/activity-logs/confused')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Stealth' ]))
                ;
            }
        }
        else if($pet->getTool() && $pet->getTool()->isGrayscaling())
        {
            $loot = $this->squirrel3->rngNextFromArray([ 'Blood Wine', 'Linens and Things' ]);

            $pet->increaseEsteem(2);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% stumbled upon a castle that was apparently home to vampires! Fortunately, the vampires mistook ' . $pet->getName() . '\'s monochromatic appearance as vampirism, and welcomed them as kin. ' . $pet->getName() . ' stole a few items while none of the vampires were looking, and fled the castle as soon as they could!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);

            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' stole this from a vampire castle.', $activityLog);
        }
        else if($pet->hasStatusEffect(StatusEffectEnum::CORDIAL))
        {
            $loot = $this->squirrel3->rngNextFromArray([ 'Blood Wine', 'Linens and Things' ]);

            $pet->increaseEsteem(2);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% stumbled upon a castle that was apparently home to vampires! Fortunately, the vampires were completely taken by ' . $pet->getName() . '\'s cordiality, and they all had a simply _wonderful_ time! ' . $pet->getName() . ' received a few gifts from the vampires, then found some excuse to leave...', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);

            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' stole this from a vampire castle.', $activityLog);
        }
        else
        {
            // don't realize; get in a fight
            $brawlCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl()->getTotal());

            if($brawlCheck >= 20)
            {
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);
                $loot = $this->squirrel3->rngNextFromArray([ 'White Cloth', 'Talon', 'Quintessence' ]);

                $pet
                    ->increaseEsteem(2)
                    ->increaseSafety(2)
                ;

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% stumbled upon a castle. While exploring it, a vampire attacked them! ' . $pet->getName() . ' was able to drive them away, however, and even nab ' . $loot . '!', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Fighting' ]))
                ;

                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' beat up a vampire and took this.', $activityLog);
            }
            else
            {
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);

                $pet
                    ->increaseEsteem(-2)
                    ->increaseSafety(-2)
                ;

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% stumbled upon a castle. While exploring it, a vampire attacked them! ' . $pet->getName() . ', caught completely by surprise, was forced to flee...', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Fighting' ]))
                ;
            }
        }

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);

        return $activityLog;
    }

    private function frozenQuag(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($petWithSkills->getCanSeeInTheDark()->getTotal() <= 0)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wandered into a deep, dark part of the Umbra, but they didn\'t have a light, so turned back...', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Light Needed' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::UMBRA, false);

            return $activityLog;
        }

        $pet->increaseFood(-1);

        if($this->squirrel3->rngNextInt(1, 3) === 1)
        {
            if($this->squirrel3->rngNextInt(1, 20) + $petWithSkills->getBrawl()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStrength()->getTotal() >= 18)
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'While %pet:' . $pet->getId() . '.name% was creeping through a frozen quag deep in the Umbra, a fox spirit leapt out of nowhere and attacked! %pet:' . $pet->getId() . '.name% fought back, liberating the creature\'s Quintessence, and... its nuts?', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Light Needed', 'Fighting' ]))
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::HUNT, false);

                $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' liberated this from a fox spirit in a frozen quag in the deep Umbra.', $activityLog);
                $this->inventoryService->petCollectsItem('Fox Nut', $pet, $pet->getName() . ' liberated this from a fox spirit in a frozen quag in the deep Umbra.', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'While %pet:' . $pet->getId() . '.name% was creeping through a frozen quag deep in the Umbra, a fox spirit leapt out of nowhere and attacked! %pet:' . $pet->getId() . '.name% was taken aback by the creature\'s ferocity, and fled the quag...', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Light Needed', 'Fighting' ]))
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::HUNT, false);
            }
        }
        else
        {
            if($this->squirrel3->rngNextInt(1, 20) + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getPerception()->getTotal() >= 18)
            {
                if($this->squirrel3->rngNextBool())
                {
                    if($pet->getTool() && $pet->getTool()->providesLight())
                        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wandered into a frozen quag deep in the Umbra. The light of their ' . InventoryModifierFunctions::getNameWithModifiers($pet->getTool()) . ' caught on some frost-covered Marshmallows, which ' . $pet->getName() . ' took!', '');
                    else
                        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wandered into a frozen quag deep in the Umbra, and happened to spot some Marshmallows!', '');

                    $activityLog
                        ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                        ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Light Needed', 'Gathering' ]))
                    ;

                    $this->inventoryService->petCollectsItem('Marshmallows', $pet, $pet->getName() . ' found this in a frozen quag in the deep Umbra.', $activityLog);
                }
                else
                {
                    if($pet->getTool() && $pet->getTool()->providesLight())
                        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wandered into a frozen quag deep in the Umbra. The light of their ' . InventoryModifierFunctions::getNameWithModifiers($pet->getTool()) . ' caught on a cube of Everice, which ' . $pet->getName() . ' took!', '');
                    else
                        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wandered into a frozen quag deep in the Umbra, and happened to spot a cube of Everice!', '');

                    $activityLog
                        ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                        ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Light Needed', 'Gathering' ]))
                    ;

                    $this->inventoryService->petCollectsItem('Everice', $pet, $pet->getName() . ' found this in a frozen quag in the deep Umbra.', $activityLog);
                }

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::GATHER, true);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% crept through a frozen quag deep in the Umbra, but all they found was a Crooked Stick.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Light Needed', 'Gathering' ]))
                ;
                $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' found this in a frozen quag in the deep Umbra.', $activityLog);

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::GATHER, false);
            }
        }

        return $activityLog;
    }

    private function fightAbandondero(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skill = 20 + $petWithSkills->getBrawl()->getTotal() + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal();

        $roll = $this->squirrel3->rngNextInt(1, $skill);

        $isRanged = $pet->getTool() && $pet->getTool()->rangedOnly() && $pet->getTool()->brawlBonus() > 0;

        $defeated = $isRanged ? 'drew their ' . $pet->getTool()->getItem()->getName() . ' faster' : 'pounced on it before it could fire';

        if($roll >= 20)
        {
            $possiblePrizes = [ 'Alien Tissue', 'Plastic', 'Silver Bar', 'Qabrêk Splàdj' ];

            $prizes = [ $this->squirrel3->rngNextFromArray($possiblePrizes) ];

            if($roll >= 30)
                $prizes[] = $this->squirrel3->rngNextFromArray($possiblePrizes);

            $this->petExperienceService->gainExp($pet, 2 + count($prizes), [ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);
            $pet
                ->increaseEsteem(3)
                ->increaseSafety(3)
            ;
            $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% encountered an Abandondero! It whipped out a laser gun, but ' . $pet->getName() . ' ' . $defeated . ', defeated it, and claimed its ' . ArrayFunctions::list_nice($prizes) . '!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Fighting' ]))
            ;

            foreach($prizes as $prize)
                $this->inventoryService->petCollectsItem($prize, $pet, $pet->getName() . ' defeated an Abandondero, and took this.', $activityLog);
        }
        else
        {
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);
            $pet->increaseSafety(-4);
            $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . '%pet:' . $pet->getId() . '.name% encountered an Abandondero! It whipped out a laser gun, and took a few shots at ' . $pet->getName() . ', who made a hasty retreat.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Fighting' ]))
            ;
        }

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, $roll >= 20);

        return $activityLog;
    }

    private function foundCursedGarden(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $loot = [
            'Eggplant', 'Grandparoot'
        ];

        $didWhat = 'harvested this from a Cursed Garden in the Umbra';

        if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getStealth()->getTotal() + $petWithSkills->getDexterity()->getTotal()) < 15)
        {
            $pet->increaseFood(-1);

            if($this->squirrel3->rngNextInt(1, 20) + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getBrawl()->getTotal() + $petWithSkills->getUmbra()->getTotal() >= 20)
            {
                if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getUmbra()->getTotal()) >= 15)
                    $loot[] = 'Quintessence';

                $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::STEALTH, PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);
                $pet->increaseEsteem($this->squirrel3->rngNextInt(1, 2));
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a Cursed Garden, but while looking for food, was attacked by an Angry Spirit. ' . $pet->getName() . ' defeated the Angry Spirit, and took its ' . ArrayFunctions::list_nice($loot) . '.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Stealth', 'Fighting' ]))
                ;
                $didWhat = 'defeated an Angry Spirit in the Umbra, and got this';

                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::UMBRA, true);
            }
            else
            {
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::STEALTH, PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);
                $pet
                    ->increaseEsteem(-2)
                    ->increaseSafety(-4)
                ;
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a Cursed Garden, but, while looking for food, was attacked and routed by an Angry Spirit.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Stealth', 'Fighting' ]))
                ;

                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::UMBRA, false);

                return $activityLog;
            }
        }
        else
        {
            if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 25)
                $loot[] = $this->squirrel3->rngNextFromArray([ 'Nutmeg', 'Eggplant', 'Silica Grounds' ]);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::STEALTH, PetSkillEnum::UMBRA ]);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a Cursed Garden, and harvested ' . ArrayFunctions::list_nice($loot) . '.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra', 'Stealth', 'Gathering' ]))
            ;

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);
        }

        foreach($loot as $itemName)
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' ' . $didWhat . '.', $activityLog);

        return $activityLog;
    }

    public function speakToBunnySpirit(Pet $pet): PetActivityLog
    {
        $activityLog = $this->responseService->createActivityLog($pet, 'A rabbit spirit visited %pet:' . $pet->getId() . '.name%, and the two talked for a while, about this world, and the other...', '')
            ->addTags($this->petActivityLogTagRepository->findByNames([ 'The Umbra' ]))
        ;
        $this->petExperienceService->gainExp($pet, 10, [ PetSkillEnum::UMBRA, PetSkillEnum::NATURE ]);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);

        return $activityLog;

    }
}
