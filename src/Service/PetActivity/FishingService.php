<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Functions\NumberFunctions;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\TransactionService;

class FishingService
{
    private $responseService;
    private $inventoryService;
    private $petExperienceService;
    private $transactionService;
    private $userQuestRepository;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetExperienceService $petExperienceService,
        TransactionService $transactionService, UserQuestRepository $userQuestRepository
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->transactionService = $transactionService;
        $this->userQuestRepository = $userQuestRepository;
    }

    public function adventure(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $maxSkill = 5 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() - ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2);

        $maxSkill = NumberFunctions::constrain($maxSkill, 1, 21);

        $roll = mt_rand(1, $maxSkill);

        $activityLog = null;
        $changes = new PetChanges($pet);

        $fishedAMerchantFish = $this->userQuestRepository->findOrCreate($pet->getOwner(), 'Fished a Merchant Fish', false);

        if(!$fishedAMerchantFish->getValue() || mt_rand(1, 100) === 1)
        {
            $fishedAMerchantFish->setValue(true);
            $activityLog = $this->fishedMerchantFish($pet);
        }
        else
        {
            switch($roll)
            {
                case 1:
                    $activityLog = $this->failedToFish($pet);
                    break;
                case 2:
                case 3:
                case 4:
                    $activityLog = $this->fishedSmallLake($petWithSkills);
                    break;
                case 5:
                case 6:
                    $activityLog = $this->fishedUnderBridge($petWithSkills);
                    break;
                case 7:
                    $activityLog = $this->fishedRoadsideCreek($petWithSkills);
                    break;
                case 8:
                case 9:
                    $activityLog = $this->fishedWaterfallBasin($petWithSkills);
                    break;
                case 10:
                case 11:
                    $activityLog = $this->fishedPlazaFountain($petWithSkills, 0);
                    break;
                case 12:
                    $activityLog = $this->fishedFloodedPaddyField($petWithSkills);
                    break;
                case 13:
                    $activityLog = $this->fishedFoggyLake($petWithSkills);
                    break;
                case 14:
                case 15:
                    if(mt_rand(1, 50) === 1)
                        $activityLog = $this->fishedTheIsleOfRetreatingTeeth($pet);
                    else
                        $activityLog = $this->fishedGhoti($petWithSkills);
                    break;
                case 16:
                    $activityLog = $this->fishedCoralReef($petWithSkills);
                    break;
                case 17:
                    $activityLog = $this->fishedPlazaFountain($petWithSkills, 2);
                    break;
                case 18:
                    $activityLog = $this->fishedGallopingOctopus($petWithSkills);
                    break;
                case 19:
                    $activityLog = $this->fishedAlgae($petWithSkills);
                    break;
                case 20:
                case 21:
                    // @TODO
                    /*if(mt_rand(1, 50) === 1)
                        $activityLog = $this->fishedNarwhal($pet);
                    else*/
                        $activityLog = $this->fishedJellyfish($petWithSkills);
                    break;
            }
        }

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        if(mt_rand(1, 75) === 1)
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    private function failedToFish(Pet $pet): PetActivityLog
    {
        if($pet->getOwner()->getGreenhouse() && $pet->getOwner()->getGreenhouse()->getHasBirdBath() && !$pet->getOwner()->getGreenhouse()->getVisitingBird())
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::FISH, false);

            if($pet->getSkills()->getBrawl() < 5)
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

            $pet
                ->increaseSafety(mt_rand(1, 2))
                ->increaseEsteem(mt_rand(1, 2))
            ;

            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% couldn\'t find anything to fish, so watched some small birds play in the Greenhouse Bird Bath, instead.', 'icons/activity-logs/birb')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::FISH, false);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to fish, but couldn\'t find a quiet place to do so.', 'icons/activity-logs/confused');
        }
    }

    private function creditLackOfReflection(PetActivityLog $activityLog)
    {
        if($activityLog->getPet()->hasMerit(MeritEnum::NO_SHADOW_OR_REFLECTION) && mt_rand(1, 4) === 1)
            $activityLog->setEntry($activityLog->getEntry() . ' (Having no reflection is pretty useful!)');
    }

    private function nothingBiting(Pet $pet, int $percentChance, string $atLocationName): ?PetActivityLog
    {
        if($pet->hasMerit(MeritEnum::NO_SHADOW_OR_REFLECTION)) $percentChance /= 2;

        if(mt_rand(1, 100) <= $percentChance)
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, false);

            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing ' . $atLocationName . ', but nothing was biting.', 'icons/activity-logs/nothing-biting');
        }

        return null;
    }

    private function fishedMerchantFish(Pet $pet): PetActivityLog
    {
        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at a Stream, and caught a Fish... but wait: that\'s no ordinary Fish...', '')
            ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
        ;

        $this->inventoryService->petCollectsItem('Merchant Fish', $pet, $pet->getName() . ' fished this out of a Stream.', $activityLog);

        $pet->increaseEsteem(2);

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

        return $activityLog;
    }

    private function fishedSmallLake(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $nothingBiting = $this->nothingBiting($pet, 20, 'at a Small Lake');
        if($nothingBiting !== null) return $nothingBiting;

        if(mt_rand(1, 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getFishingBonus()->getTotal()) >= 5)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at a Small Lake, and caught a Mini Minnow.', 'items/tool/fishing-rod/crooked');
            $this->inventoryService->petCollectsItem('Fish', $pet, 'From a Mini Minnow that ' . $pet->getName() . ' fished at a Small Lake.', $activityLog);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

            $this->creditLackOfReflection($activityLog);
        }
        else if(mt_rand(1, 15) === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at a Small Lake, but nothing was biting, so ' . $pet->getName() . ' grabbed some Silica Grounds, instead.', '');
            $this->inventoryService->petCollectsItem('Silica Grounds', $pet, $pet->getName() . ' took this from a Small Lake.', $activityLog);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at a Small Lake, and almost caught a Mini Minnow, but it got away.', '');

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }

        return $activityLog;
    }

    private function fishedUnderBridge(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $nothingBiting = $this->nothingBiting($pet, 15, 'Under a Bridge');
        if($nothingBiting !== null) return $nothingBiting;

        if(mt_rand(1, 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getFishingBonus()->getTotal()) >= 6)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing Under a Bridge, and caught a Muscly Trout.', 'items/tool/fishing-rod/crooked');
            $this->inventoryService->petCollectsItem('Fish', $pet, 'From a Muscly Trout that ' . $pet->getName() . ' fished Under a Bridge.', $activityLog);

            if(mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal()) >= 15)
                $this->inventoryService->petCollectsItem('Scales', $pet, 'From a Muscly Trout that ' . $pet->getName() . ' fished Under a Bridge.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

            $this->creditLackOfReflection($activityLog);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);
        }
        else if(mt_rand(1, 4) === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing Under a Bridge, but all they got was an old can of food...', '');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

            $this->inventoryService->petCollectsItem('Canned Food', $pet, $pet->getName() . ' fished this out of a river under a bridge...', $activityLog);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, false);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing Under a Bridge, and almost caught a Muscly Trout, but it got away.', '');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, false);
        }

        return $activityLog;
    }

    private function fishedGallopingOctopus(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $fightSkill = mt_rand(1, 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() + $petWithSkills->getBrawl(false)->getTotal() + $petWithSkills->getStrength()->getTotal());

        if($fightSkill <= 3)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing, and started to reel something in, only to realize it was a huge Galloping Octopus! ' . $pet->getName() . ' was caught unawares, and took a tentacle slap to the face before running away! :(', '');
            $this->petExperienceService->spendTime($pet, mt_rand(30, 45), PetActivityStatEnum::HUNT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);

            $pet
                ->increaseSafety(-mt_rand(4, 8))
                ->increaseEsteem(-mt_rand(2, 4))
            ;
        }
        else if($fightSkill >= 18)
        {
            if(mt_rand(1, 2) === 1)
            {
                $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, true);
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing, and started to reel something in, only to realize it was a huge Galloping Octopus! ' . $pet->getName() . ' beat the creature back into the sea, but not before discerping one of its Tentacles!', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                ;
                $this->inventoryService->petCollectsItem('Tentacle', $pet, $pet->getName() . ' received this from a fight with a Galloping Octopus.', $activityLog);

                $pet
                    ->increaseSafety(mt_rand(1, 4))
                    ->increaseEsteem(mt_rand(1, 4))
                ;
            }
            else
            {
                $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::HUNT, true);
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing, and started to reel something in, only to realize it was a huge Galloping Octopus! ' . $pet->getName() . ' beat the creature back into the sea, but not before discerping two of its Tentacles!', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                ;
                $this->inventoryService->petCollectsItem('Tentacle', $pet, $pet->getName() . ' received this from a fight with a Galloping Octopus.', $activityLog);
                $this->inventoryService->petCollectsItem('Tentacle', $pet, $pet->getName() . ' received this from a fight with a Galloping Octopus.', $activityLog);

                $pet
                    ->increaseSafety(mt_rand(1, 4))
                    ->increaseEsteem(mt_rand(2, 6))
                ;
            }

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::BRAWL ]);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing, and started to reel something in, only to realize it was a huge Galloping Octopus! The two tussled for a while before breaking apart and cautiously retreating...', '');
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::HUNT, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ]);
        }

        return $activityLog;
    }

    private function fishedAlgae(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $nothingBiting = $this->nothingBiting($pet, 15, 'still-water pond');
        if($nothingBiting !== null) return $nothingBiting;

        $fishingSkill = mt_rand(1, 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() + $petWithSkills->getNature()->getTotal());

        if($fishingSkill >= 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at a still-water pond. There weren\'t any fish, but there was some Algae!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;

            $this->inventoryService->petCollectsItem('Algae', $pet, $pet->getName() . ' "fished" this from a still-water pond.', $activityLog);

            $pet->increaseEsteem(mt_rand(1, 4));

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::NATURE ]);
        }
        else
        {
            $message = ArrayFunctions::pick_one([
                'They saw a snail once, but that was about it. (And it was one of those crazy-poisonous types of snails! Ugh!)',
                'The most exciting thing that happened was that they got briefly stuck in the mud :|',
                'They almost caught something, but a bird swooped in and got it, first! >:(',
                'After over half an hour of nothing, they gave up out of sheer boredom >_>',
                'Nothing was biting, but at least it was relaxing??',
            ]);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried fishing at a still-water pond. ' . $message, '');

            $this->petExperienceService->spendTime($pet, mt_rand(30, 45), PetActivityStatEnum::FISH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }

        return $activityLog;
    }

    private function fishedRoadsideCreek(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $nothingBiting = $this->nothingBiting($pet, 20, 'at a Roadside Creek');
        if($nothingBiting !== null) return $nothingBiting;

        if(mt_rand(1, 3) === 1)
        {
            // toad
            if(mt_rand(1, 10 + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getFishingBonus()->getTotal()) >= 7)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at a Roadside Creek, and a Huge Toad bit the line! ' . $pet->getName() . ' used all their strength to reel it in!', '');
                $this->inventoryService->petCollectsItem('Toad Legs', $pet, 'From a Huge Toad that ' . $pet->getName() . ' fished at a Roadside Creek.', $activityLog);

                if(mt_rand(1, 20 + $petWithSkills->getNature()->getTotal()) >= 15)
                    $this->inventoryService->petCollectsItem('Toadstool', $pet, 'From a Huge Toad that ' . $pet->getName() . ' fished at a Roadside Creek.', $activityLog);

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);

                $this->creditLackOfReflection($activityLog);

                $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::FISH, true);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at a Roadside Creek, and a Huge Toad bit the line! ' . $pet->getName() . ' tried to reel it in, but it was too strong, and got away.', '');
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

                $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::FISH, false);
            }
        }
        else
        {
            // singing fish
            if(mt_rand(1, 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getFishingBonus()->getTotal()) >= 6)
            {
                $gotMusic = mt_rand(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMusic()->getTotal()) >= 10;

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at a Roadside Creek, and caught a Singing Fish!', $gotMusic ? 'items/music/note' : 'items/tool/fishing-rod/crooked');
                $this->inventoryService->petCollectsItem(mt_rand(1, 2) === 1 ? 'Plastic' : 'Fish', $pet, 'From a Singing Fish that ' . $pet->getName() . ' fished at a Roadside Creek.', $activityLog);

                if($gotMusic)
                    $this->inventoryService->petCollectsItem('Music Note', $pet, 'From a Singing Fish that ' . $pet->getName() . ' fished at a Roadside Creek.', $activityLog);

                $this->creditLackOfReflection($activityLog);

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);

                $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::FISH, true);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at a Roadside Creek, and almost caught a Singing Fish, but it got away.', '');
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

                $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::FISH, false);
            }
        }

        return $activityLog;
    }

    private function fishedWaterfallBasin(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $nothingBiting = $this->nothingBiting($pet, 20, 'in a Waterfall Basin');
        if($nothingBiting !== null) return $nothingBiting;

        if(mt_rand(1, 80) === 1 && $pet->hasMerit(MeritEnum::LUCKY))
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at a Waterfall Basin, and reeled in a Little Strongbox! Lucky~!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $this->inventoryService->petCollectsItem('Little Strongbox', $pet, $pet->getName() . ' was fishing in a Waterfall Basin, and one of these got caught on the line! Lucky~!', $activityLog);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::FISH, true);
        }
        else if(mt_rand(1, 80) === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at a Waterfall Basin, and reeled in a Little Strongbox!', '');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $this->inventoryService->petCollectsItem('Little Strongbox', $pet, $pet->getName() . ' was fishing in a Waterfall Basin, and one of these got caught on the line!', $activityLog);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::FISH, true);
        }
        else if(mt_rand(1, 5) === 1)
        {
            if(mt_rand(1, 2) === 1 && $pet->hasMerit(MeritEnum::SOOTHING_VOICE))
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at a Waterfall Basin. There, ' . $pet->getName() . '\'s humming caught the attention of a mermaid, who became fascinated by ' . $pet->getName() . '\'s Soothing Voice. After listening for a while, she gave ' . $pet->getName() . ' a Basket of Fish, and left.', '');
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::MUSIC ]);
                $this->inventoryService->petCollectsItem('Basket of Fish', $pet, $pet->getName() . ' received this from a Waterfall Basin mermaid who was enchanted by ' . $pet->getName() . '\'s Soothing Voice.', $activityLog);

                $this->petExperienceService->spendTime($pet, mt_rand(30, 45), PetActivityStatEnum::FISH, true);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at a Waterfall Basin, and reeled in a Mermaid Egg!', 'items/animal/egg-mermaid');
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
                $this->inventoryService->petCollectsItem('Mermaid Egg', $pet, $pet->getName() . ' was fishing in a Waterfall Basin, and one of these got caught on the line!', $activityLog);

                $this->petExperienceService->spendTime($pet, mt_rand(30, 45), PetActivityStatEnum::FISH, true);
            }
        }
        else
        {
            if(mt_rand(1, 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getFishingBonus()->getTotal()) >= 7)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing in a Waterfall Basin, and caught a Medium Minnow.', 'items/tool/fishing-rod/crooked');
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);

                $this->inventoryService->petCollectsItem('Fish', $pet, 'From a Medium Minnow that ' . $pet->getName() . ' fished in a Waterfall Basin.', $activityLog);

                if(mt_rand(1, 20 + $petWithSkills->getNature()->getTotal()) >= 10)
                    $this->inventoryService->petCollectsItem('Fish', $pet, 'From a Medium Minnow that ' . $pet->getName() . ' fished in a Waterfall Basin.', $activityLog);

                $this->creditLackOfReflection($activityLog);

                $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing in a Waterfall Basin, and almost caught a Medium Minnow, but it got away.', '');
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

                $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, false);
            }
        }

        return $activityLog;
    }

    private function fishedPlazaFountain(ComputedPetSkills $petWithSkills, int $bonusMoney): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if(mt_rand(1, 10 + $petWithSkills->getStealth()->getTotal() + $petWithSkills->getDexterity()->getTotal()) >= 10)
            $bonusMoney += mt_rand(1, 3);

        if($pet->hasMerit(MeritEnum::LUCKY) && mt_rand(1, 7) === 1)
        {
            $moneys = mt_rand(10, 15) + $bonusMoney;
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% fished around in the Plaza fountain while no one was looking, and grabbed ' . $moneys . ' moneys! Lucky~!', 'icons/activity-logs/moneys')
                ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
            ;
        }
        else
        {
            $moneys = mt_rand(2, 9) + $bonusMoney;
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% fished around in the Plaza fountain while no one was looking, and grabbed ' . $moneys . ' moneys.', 'icons/activity-logs/moneys');
        }

        if(mt_rand(1, 20) === 1)
            $this->transactionService->getMoney($pet->getOwner(), $moneys, $pet->getName() . ' fished this out of the Plaza fountain while no one was looking. (That seems like it shouldn\'t be allowed...)');
        else
            $this->transactionService->getMoney($pet->getOwner(), $moneys, $pet->getName() . ' fished this out of the Plaza fountain while no one was looking.');

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH ]);

        $this->petExperienceService->spendTime($pet, mt_rand(30, 45), PetActivityStatEnum::FISH, true);

        return $activityLog;
    }

    private function fishedFloodedPaddyField(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $nothingBiting = $this->nothingBiting($pet, 20, 'at a Flooded Paddy Field');
        if($nothingBiting !== null) return $nothingBiting;

        $foundRice = mt_rand(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 15;
        $foundNonLa = $foundRice && (mt_rand(1, 35) === 1);

        if(mt_rand(1, 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getFishingBonus()->getTotal()) >= 10)
        {
            if($foundRice)
            {
                if($foundNonLa)
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at a Flooded Paddy Field, caught a Crawfish, picked some Rice, and found a Nón Lá!', 'items/tool/fishing-rod/crooked');
                else
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at a Flooded Paddy Field, caught a Crawfish, and picked some Rice!', 'items/tool/fishing-rod/crooked');
            }
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at a Flooded Paddy Field, and caught a Crawfish.', 'items/tool/fishing-rod/crooked');

            $this->inventoryService->petCollectsItem('Fish', $pet, 'From a Crawfish that ' . $pet->getName() . ' fished at a Flooded Paddy Field.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

            $this->creditLackOfReflection($activityLog);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);
        }
        else
        {
            if($foundRice)
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at a Flooded Paddy Field, and almost caught a Crawfish, but it got away. There was plenty of Rice, around, though, so ' . $pet->getName() . ' grabbed some of that.', '');
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at a Flooded Paddy Field, and almost caught a Crawfish, but it got away.', '');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, false);
        }

        if($foundRice)
        {
            if($foundNonLa)
                $this->inventoryService->petCollectsItem('Nón Lá', $pet, $pet->getName() . ' found this at a Flooded Paddy Field while fishing.', $activityLog);

            $this->inventoryService->petCollectsItem('Rice', $pet, $pet->getName() . ' found this at a Flooded Paddy Field while fishing.', $activityLog);
            $this->petExperienceService->gainExp($pet, $foundNonLa ? 2 : 1, [ PetSkillEnum::NATURE ]);
        }

        return $activityLog;
    }

    private function fishedTheIsleOfRetreatingTeeth(Pet $pet): PetActivityLog
    {
        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at The Isle of Retreating Teeth. They weren\'t able to catch anything, but they did grab some Talo-- er, I mean, Teeth.', '')
            ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
        ;

        $this->inventoryService->petCollectsItem('Talon', $pet, $pet->getName() . ' got this from The Isle of Retreating Teeth.', $activityLog);
        $this->inventoryService->petCollectsItem('Talon', $pet, $pet->getName() . ' got this from The Isle of Retreating Teeth.', $activityLog);

        if(mt_rand(1, 2) === 2)
            $this->inventoryService->petCollectsItem('Talon', $pet, $pet->getName() . ' got this from The Isle of Retreating Teeth.', $activityLog);

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);

        $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);

        return $activityLog;
    }

    private function fishedFoggyLake(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $nothingBiting = $this->nothingBiting($pet, 20, 'at a Foggy Lake');
        if($nothingBiting !== null) return $nothingBiting;

        if(mt_rand(1, 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getFishingBonus()->getTotal()) >= 5)
        {
            if(mt_rand(1, 4) === 1)
            {
                if(mt_rand(1, 20 + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getIntelligence()->getTotal()) >= 15)
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at a Foggy Lake, caught a Ghost Fish, and harvested Quintessence from it.', 'items/resource/quintessence');
                    $this->inventoryService->petCollectsItem('Quintessence', $pet, 'From a Ghost Fish that ' . $pet->getName() . ' fished at a Foggy Lake.', $activityLog);
                    $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::UMBRA ]);
                }
                else
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at a Foggy Lake, and caught a Ghost Fish, but failed to harvest any Quintessence from it.', '');
                    $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::UMBRA ]);
                }
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at a Foggy Lake, and caught a Mung Fish.', 'items/tool/fishing-rod/crooked');
                $this->inventoryService->petCollectsItem('Beans', $pet, $pet->getName() . ' got this from a Mung Fish at a Foggy Lake.', $activityLog);
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);
            }

            $this->creditLackOfReflection($activityLog);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);
        }
        else
        {
            if(mt_rand(1, 15) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at a Foggy Lake, but nothing was biting, so ' . $pet->getName() . ' grabbed some Silica Grounds, instead.', '');
                $this->inventoryService->petCollectsItem('Silica Grounds', $pet, $pet->getName() . ' took this from a Foggy Lake.', $activityLog);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at a Foggy Lake, and almost caught something, but it got away.', '');
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            }

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, false);
        }

        return $activityLog;
    }

    public function fishedGhoti(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();

        $nothingBiting = $this->nothingBiting($pet, 20, 'at the foot of the Volcano');
        if($nothingBiting !== null) return $nothingBiting;

        if(mt_rand(1, 100) === 1 || ($pet->hasMerit(MeritEnum::LUCKY) && mt_rand(1, 100) === 1))
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at the foot of the Volcano; nothing was biting, but ' . $pet->getName() . ' found a piece of Firestone while they were out!', '');
            $this->inventoryService->petCollectsItem('Firestone', $pet, $pet->getName() . ' found this at the foot of the Volcano.', $activityLog);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }
        else if(mt_rand(1, 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getFishingBonus()->getTotal()) >= 10)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at the foot of the Volcano, and caught a Ghoti!', 'items/tool/fishing-rod/crooked');
            $this->inventoryService->petCollectsItem('Fish', $pet, 'From a Ghoti that ' . $pet->getName() . ' fished at the foot of the Volcano.', $activityLog);

            $extraItem = ArrayFunctions::pick_one([ 'Fish', 'Scales', 'Oil' ]);

            $this->inventoryService->petCollectsItem($extraItem, $pet, 'From a Ghoti that ' . $pet->getName() . ' fished at the foot of the Volcano.', $activityLog);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);

            $this->creditLackOfReflection($activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at the foot of the Volcano, and almost caught a Ghoti, but it got away.', '');

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }

        return $activityLog;
    }

    public function fishedCoralReef(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();

        // no chance of nothing biting at the coral reef!

        $possibleItems = [
            'Fish',
            'Fish',
            'Fish',
            'Seaweed',
            'Seaweed',
            'Silica Grounds',
            'Sand Dollar',
            'Scales',
            'Iron Ore',
            'Silver Ore',
        ];

        if(mt_rand(1, 50) === 1 || ($pet->hasMerit(MeritEnum::LUCKY) && mt_rand(1, 50) === 1))
        {
            $item = ArrayFunctions::pick_one([
                'Gold Bar', 'Very Strongbox', 'Rusty Rapier',
            ]);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at the Coral Reef, and spotted a ' . $item . '!', '');
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' found this at the Coral Reef.', $activityLog);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }
        else if(mt_rand(1, 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getFishingBonus()->getTotal()) >= 24)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at the Coral Reef, and caught all kinds of stuff!', 'items/tool/fishing-rod/crooked');

            for($x = 0; $x < 3; $x++)
                $this->inventoryService->petCollectsItem(ArrayFunctions::pick_one($possibleItems), $pet, $pet->getName() . ' got this while fishing at the Coral Reef.', $activityLog);

            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::FISH, true);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::NATURE ]);
        }
        else if(mt_rand(1, 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getFishingBonus()->getTotal()) >= 12)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at the Coral Reef, and caught a couple things!', 'items/tool/fishing-rod/crooked');

            for($x = 0; $x < 2; $x++)
                $this->inventoryService->petCollectsItem(ArrayFunctions::pick_one($possibleItems), $pet, $pet->getName() . ' got this while fishing at the Coral Reef.', $activityLog);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);
        }
        else
        {
            if(mt_rand(1, 2) === 1)
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at the Coral Reef, but there were a bunch of Hammerheads around.', '');
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing at the Coral Reef, but there were a bunch of Jellyfish around.', '');

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }

        return $activityLog;
    }

    private function fishedJellyfish(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $nothingBiting = $this->nothingBiting($pet, 20, 'way out on the pier');
        if($nothingBiting !== null) return $nothingBiting;

        if(mt_rand(1, 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getFishingBonus()->getTotal()) >= 12)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing way out on the pier, and caught a Jellyfish.', 'items/tool/fishing-rod/crooked');
            $this->inventoryService->petCollectsItem('Jellyfish Jelly', $pet, $pet->getName() . ' got this from a Jellyfish they caught way out on the pier.', $activityLog);

            if(mt_rand(1, 2) === 1)
                $this->inventoryService->petCollectsItem('Tentacle', $pet, $pet->getName() . ' got this from a Jellyfish they caught way out on the pier.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);

            $this->creditLackOfReflection($activityLog);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went fishing way out on the pier, and pulled up a Jellyfish, but it stung ' . $pet->getName() . ', and got away!', '');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

            $pet->increaseSafety(-mt_rand(4, 8));

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, false);
        }

        return $activityLog;
    }
}
