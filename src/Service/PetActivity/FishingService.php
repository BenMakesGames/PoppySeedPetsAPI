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
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\PetService;
use App\Service\ResponseService;

class FishingService
{
    private $responseService;
    private $inventoryService;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetService $petService
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petService = $petService;
    }

    public function adventure(Pet $pet)
    {
        $maxSkill = 5 + $pet->getDexterity() + $pet->getNature() + $pet->getFishing() - ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2);

        $maxSkill = NumberFunctions::constrain($maxSkill, 1, 19);

        $roll = \mt_rand(1, $maxSkill);

        $activityLog = null;
        $changes = new PetChanges($pet);

        switch($roll)
        {
            case 1:
                $activityLog = $this->failedToFish($pet);
                break;
            case 2:
            case 3:
            case 4:
                $activityLog = $this->fishedSmallLake($pet);
                break;
            case 5:
            case 6:
                $activityLog = $this->fishedUnderBridge($pet);
                break;
            case 7:
                $activityLog = $this->fishedRoadsideCreek($pet);
                break;
            case 8:
            case 9:
                $activityLog = $this->fishedWaterfallBasin($pet);
                break;
            case 10:
            case 11:
                $activityLog = $this->fishedPlazaFountain($pet, 0);
                break;
            case 12:
                $activityLog = $this->fishedFloodedPaddyField($pet);
                break;
            case 13:
                $activityLog = $this->fishedFoggyLake($pet);
                break;
            case 14:
            case 15:
                $activityLog = $this->fishedGhoti($pet);
                break;
            case 16:
                $activityLog = $this->fishedCoralReef($pet);
                break;
            case 17:
                $activityLog = $this->fishedPlazaFountain($pet, 2);
                break;
            case 18:
                $activityLog = $this->fishedGallopingOctopus($pet);
                break;
            case 19:
                $activityLog = $this->fishedAlgae($pet);
                break;
        }

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        if(mt_rand(1, 75) === 1)
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    private function failedToFish(Pet $pet): PetActivityLog
    {
        $this->petService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::FISH, false);
        return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to fish, but couldn\'t find a quiet place to do so.', 'icons/activity-logs/confused');
    }

    private function creditLackOfReflection(PetActivityLog $activityLog)
    {
        if($activityLog->getPet()->hasMerit(MeritEnum::NO_SHADOW_OR_REFLECTION) && mt_rand(1, 4) === 1)
            $activityLog->setEntry($activityLog->getEntry() . ' (Having no reflection is pretty useful!)');
    }

    private function nothingBiting(Pet $pet, int $percentChance, string $atLocationName): ?PetActivityLog
    {
        if($pet->hasMerit(MeritEnum::NO_SHADOW_OR_REFLECTION)) $percentChance /= 2;

        if(\mt_rand(1, 100) <= $percentChance)
        {
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

            $this->petService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, false);

            return $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing ' . $atLocationName . ', but nothing was biting.', 'icons/activity-logs/nothing-biting');
        }

        return null;
    }

    private function fishedSmallLake(Pet $pet): PetActivityLog
    {
        $nothingBiting = $this->nothingBiting($pet, 20, 'at a Small Lake');
        if($nothingBiting !== null) return $nothingBiting;

        if(\mt_rand(1, 10 + $pet->getDexterity() + $pet->getNature() + $pet->getPerception() + $pet->getFishing()) >= 5)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Small Lake, and caught a Mini Minnow.', 'items/tool/fishing-rod/crooked');
            $this->inventoryService->petCollectsItem('Fish', $pet, 'From a Mini Minnow that ' . $pet->getName() . ' fished at a Small Lake.', $activityLog);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

            $this->creditLackOfReflection($activityLog);
        }
        else if(mt_rand(1, 15) === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Small Lake, but nothing was biting, so ' . $pet->getName() . ' grabbed some Silica Grounds, instead.', '');
            $this->inventoryService->petCollectsItem('Silica Grounds', $pet, $pet->getName() . ' took this from a Small Lake.', $activityLog);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Small Lake, and almost caught a Mini Minnow, but it got away.', '');
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }

        return $activityLog;
    }

    private function fishedUnderBridge(Pet $pet): PetActivityLog
    {
        $nothingBiting = $this->nothingBiting($pet, 15, 'Under a Bridge');
        if($nothingBiting !== null) return $nothingBiting;

        if(\mt_rand(1, 10 + $pet->getDexterity() + $pet->getNature() + $pet->getStrength() + $pet->getFishing()) >= 6)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing Under a Bridge, and caught a Muscly Trout.', 'items/tool/fishing-rod/crooked');
            $this->inventoryService->petCollectsItem('Fish', $pet, 'From a Muscly Trout that ' . $pet->getName() . ' fished Under a Bridge.', $activityLog);

            if(\mt_rand(1, 20 + $pet->getIntelligence()) >= 15)
                $this->inventoryService->petCollectsItem('Scales', $pet, 'From a Muscly Trout that ' . $pet->getName() . ' fished Under a Bridge.', $activityLog);

            $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

            $this->creditLackOfReflection($activityLog);

            $this->petService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing Under a Bridge, and almost caught a Muscly Trout, but it got away.', '');
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

            $this->petService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, false);
        }

        return $activityLog;
    }

    private function fishedGallopingOctopus(Pet $pet): PetActivityLog
    {
        $fightSkill = mt_rand(1, 10 + $pet->getDexterity() + $pet->getFishing() + $pet->getBrawl() + $pet->getStrength());

        if($fightSkill <= 3)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing, and started to reel something in, only to realize it was a huge Galloping Octopus! ' . $pet->getName() . ' was caught unawares, and took a tentacle slap to the face before running away! :(', '');
            $this->petService->spendTime($pet, mt_rand(30, 45), PetActivityStatEnum::HUNT, false);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);

            $pet
                ->increaseSafety(-mt_rand(4, 8))
                ->increaseEsteem(-mt_rand(2, 4))
            ;
        }
        else if($fightSkill >= 18)
        {
            if(mt_rand(1, 2) === 1)
            {
                $this->petService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, true);
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing, and started to reel something in, only to realize it was a huge Galloping Octopus! ' . $pet->getName() . ' beat the creature back into the sea, but not before discerping one of its Tentacles!', '');
                $this->inventoryService->petCollectsItem('Tentacle', $pet, $pet->getName() . ' received this from a fight with a Galloping Octopus.', $activityLog);

                $pet
                    ->increaseSafety(mt_rand(1, 4))
                    ->increaseEsteem(mt_rand(1, 4))
                ;
            }
            else
            {
                $this->petService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::HUNT, true);
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing, and started to reel something in, only to realize it was a huge Galloping Octopus! ' . $pet->getName() . ' beat the creature back into the sea, but not before discerping two of its Tentacles!', '');
                $this->inventoryService->petCollectsItem('Tentacle', $pet, $pet->getName() . ' received this from a fight with a Galloping Octopus.', $activityLog);
                $this->inventoryService->petCollectsItem('Tentacle', $pet, $pet->getName() . ' received this from a fight with a Galloping Octopus.', $activityLog);

                $pet
                    ->increaseSafety(mt_rand(1, 4))
                    ->increaseEsteem(mt_rand(2, 6))
                ;
            }

            $this->petService->gainExp($pet, 3, [ PetSkillEnum::BRAWL ]);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing, and started to reel something in, only to realize it was a huge Galloping Octopus! The two tussled for a while before breaking apart and cautiously retreating...', '');
            $this->petService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::HUNT, false);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ]);
        }

        return $activityLog;
    }

    private function fishedAlgae(Pet $pet): PetActivityLog
    {
        $nothingBiting = $this->nothingBiting($pet, 15, 'still-water pond');
        if($nothingBiting !== null) return $nothingBiting;

        $fishingSkill = mt_rand(1, 10 + $pet->getDexterity() + $pet->getFishing() + $pet->getNature());

        if($fishingSkill >= 15)
        {
            $this->petService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a still-water pond. There weren\'t any fish, but there was some Algae!', '');

            $this->inventoryService->petCollectsItem('Algae', $pet, $pet->getName() . ' "fished" this from a still-water pond.', $activityLog);

            $pet->increaseEsteem(mt_rand(1, 4));

            $this->petService->gainExp($pet, 3, [ PetSkillEnum::NATURE ]);
        }
        else
        {
            $message = ArrayFunctions::pick_one([
                'They saw a snail once, but that was about it. (And it was one of those crazy-poisonous types of snails! Ugh!)',
                'The most exciting thing that happened was that their foot got stuck in the mud :|',
                'They almost caught something, but a bird swooped in and got it, first! >:(',
                'After over half an hour of nothing, they gave up out of sheer boredom >_>',
                'Nothing was biting, but at least it was relaxing??',
            ]);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried fishing at a still-water pond. ' . $message, '');

            $this->petService->spendTime($pet, mt_rand(30, 45), PetActivityStatEnum::FISH, false);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }

        return $activityLog;
    }

    private function fishedRoadsideCreek(Pet $pet): PetActivityLog
    {
        $nothingBiting = $this->nothingBiting($pet, 20, 'at a Roadside Creek');
        if($nothingBiting !== null) return $nothingBiting;

        if(mt_rand(1, 3) === 1)
        {
            // toad
            if(\mt_rand(1, 10 + $pet->getStamina() + $pet->getDexterity() + $pet->getStrength() + $pet->getFishing()) >= 7)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Roadside Creek, and a Huge Toad bit the line! ' . $pet->getName() . ' used all their strength to reel it in!', '');
                $this->inventoryService->petCollectsItem('Toad Legs', $pet, 'From a Huge Toad that ' . $pet->getName() . ' fished at a Roadside Creek.', $activityLog);

                if(\mt_rand(1, 20 + $pet->getNature()) >= 15)
                    $this->inventoryService->petCollectsItem('Toadstool', $pet, 'From a Huge Toad that ' . $pet->getName() . ' fished at a Roadside Creek.', $activityLog);

                $this->petService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);

                $this->creditLackOfReflection($activityLog);

                $this->petService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::FISH, true);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Roadside Creek, and a Huge Toad bit the line! ' . $pet->getName() . ' tried to reel it in, but it was too strong, and got away.', '');
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

                $this->petService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::FISH, false);
            }
        }
        else
        {
            // singing fish
            if(\mt_rand(1, 10 + $pet->getDexterity() + $pet->getNature() + $pet->getPerception() + $pet->getFishing()) >= 6)
            {
                $gotMusic = \mt_rand(1, 20 + $pet->getPerception() + $pet->getMusic()) >= 10;

                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Roadside Creek, and caught a Singing Fish!', $gotMusic ? 'items/music/note' : 'items/tool/fishing-rod/crooked');
                $this->inventoryService->petCollectsItem(mt_rand(1, 2) === 1 ? 'Plastic' : 'Fish', $pet, 'From a Singing Fish that ' . $pet->getName() . ' fished at a Roadside Creek.', $activityLog);

                if($gotMusic)
                    $this->inventoryService->petCollectsItem('Music Note', $pet, 'From a Singing Fish that ' . $pet->getName() . ' fished at a Roadside Creek.', $activityLog);

                $this->creditLackOfReflection($activityLog);

                $this->petService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);

                $this->petService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::FISH, true);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Roadside Creek, and almost caught a Singing Fish, but it got away.', '');
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

                $this->petService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::FISH, false);
            }
        }

        return $activityLog;
    }

    private function fishedWaterfallBasin(Pet $pet): PetActivityLog
    {
        $nothingBiting = $this->nothingBiting($pet, 20, 'in a Waterfall Basin');
        if($nothingBiting !== null) return $nothingBiting;

        if(\mt_rand(1, 80) === 1 && $pet->hasMerit(MeritEnum::LUCKY))
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Waterfall Basin, and reeled in a Little Strongbox! Lucky~!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
            ;
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $this->inventoryService->petCollectsItem('Little Strongbox', $pet, $pet->getName() . ' was fishing in a Waterfall Basin, and one of these got caught on the line! Lucky~!', $activityLog);

            $this->petService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::FISH, true);
        }
        else if(\mt_rand(1, 80) === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Waterfall Basin, and reeled in a Little Strongbox!', '');
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $this->inventoryService->petCollectsItem('Little Strongbox', $pet, $pet->getName() . ' was fishing in a Waterfall Basin, and one of these got caught on the line!', $activityLog);

            $this->petService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::FISH, true);
        }
        else if(\mt_rand(1, 5) === 1)
        {
            if(mt_rand(1, 2) === 1 && $pet->hasMerit(MeritEnum::SOOTHING_VOICE))
            {
                $reward = mt_rand(1, 10) === 1 ? 'Secret Seashell' : 'Moon Pearl';

                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Waterfall Basin. There, ' . $pet->getName() . '\'s humming caught the attention of a mermaid, who became fascinated by ' . $pet->getName() . '\'s Soothing Voice. After listening for a while, she gave ' . $pet->getName() . ' a ' . $reward . ', and left.', '');
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::MUSIC ]);
                $this->inventoryService->petCollectsItem($reward, $pet, $pet->getName() . ' received this from a Waterfall Basin mermaid who was enchanted by ' . $pet->getName() . '\'s Soothing Voice.', $activityLog);

                $this->petService->spendTime($pet, mt_rand(30, 45), PetActivityStatEnum::FISH, true);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Waterfall Basin, and reeled in a Mermaid Egg!', 'items/animal/egg-mermaid');
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
                $this->inventoryService->petCollectsItem('Mermaid Egg', $pet, $pet->getName() . ' was fishing in a Waterfall Basin, and one of these got caught on the line!', $activityLog);

                $this->petService->spendTime($pet, mt_rand(30, 45), PetActivityStatEnum::FISH, true);
            }
        }
        else
        {
            if(\mt_rand(1, 10 + $pet->getDexterity() + $pet->getNature() + $pet->getPerception() + $pet->getFishing()) >= 7)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing in a Waterfall Basin, and caught a Medium Minnow.', 'items/tool/fishing-rod/crooked');
                $this->petService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);

                $this->inventoryService->petCollectsItem('Fish', $pet, 'From a Medium Minnow that ' . $pet->getName() . ' fished in a Waterfall Basin.', $activityLog);

                if(\mt_rand(1, 20 + $pet->getNature()) >= 10)
                    $this->inventoryService->petCollectsItem('Fish', $pet, 'From a Medium Minnow that ' . $pet->getName() . ' fished in a Waterfall Basin.', $activityLog);

                $this->creditLackOfReflection($activityLog);

                $this->petService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing in a Waterfall Basin, and almost caught a Medium Minnow, but it got away.', '');
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

                $this->petService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, false);
            }
        }

        return $activityLog;
    }

    private function fishedPlazaFountain(Pet $pet, int $bonusMoney): PetActivityLog
    {
        if($pet->hasMerit(MeritEnum::LUCKY) && mt_rand(1, 7) === 1)
        {
            $moneys = \mt_rand(10, 15) + $bonusMoney;
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' fished around in the Plaza Fountain, and grabbed ' . $moneys . ' moneys! Lucky~!', 'icons/activity-logs/moneys')
                ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
            ;
            $pet->getOwner()->increaseMoneys($moneys);
        }
        else
        {
            $moneys = \mt_rand(2, 9) + $bonusMoney;
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' fished around in the Plaza Fountain, and grabbed ' . $moneys . ' moneys.', 'icons/activity-logs/moneys');
            $pet->getOwner()->increaseMoneys($moneys);
        }

        $this->petService->spendTime($pet, mt_rand(30, 45), PetActivityStatEnum::FISH, true);

        return $activityLog;
    }

    private function fishedFloodedPaddyField(Pet $pet): PetActivityLog
    {
        $nothingBiting = $this->nothingBiting($pet, 20, 'at a Flooded Paddy Field');
        if($nothingBiting !== null) return $nothingBiting;

        $foundRice = mt_rand(1, 20 + $pet->getPerception() + $pet->getNature() + $pet->getGathering()) >= 15;

        if(\mt_rand(1, 10 + $pet->getDexterity() + $pet->getNature() + $pet->getPerception() + $pet->getFishing()) >= 10)
        {
            if($foundRice)
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Flooded Paddy Field, caught a Crawfish, and picked some Rice!', 'items/tool/fishing-rod/crooked');
            else
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Flooded Paddy Field, and caught a Crawfish.', 'items/tool/fishing-rod/crooked');

            $this->inventoryService->petCollectsItem('Fish', $pet, 'From a Crawfish that ' . $pet->getName() . ' fished at a Flooded Paddy Field.', $activityLog);

            $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

            $this->creditLackOfReflection($activityLog);

            $this->petService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);
        }
        else
        {
            if($foundRice)
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Flooded Paddy Field, and almost caught a Crawfish, but it got away. There was plenty of Rice, around, though, so ' . $pet->getName() . ' grabbed some of that.', '');
            else
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Flooded Paddy Field, and almost caught a Crawfish, but it got away.', '');

            $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

            $this->petService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, false);
        }

        if($foundRice)
        {
            $this->inventoryService->petCollectsItem('Rice', $pet, $pet->getName() . ' found this at a Flooded Paddy Field while fishing.', $activityLog);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }

        return $activityLog;
    }

    private function fishedFoggyLake(Pet $pet): PetActivityLog
    {
        $nothingBiting = $this->nothingBiting($pet, 20, 'at a Foggy Lake');
        if($nothingBiting !== null) return $nothingBiting;

        if(\mt_rand(1, 10 + $pet->getDexterity() + $pet->getNature() + $pet->getPerception() + $pet->getFishing()) >= 5)
        {
            if(mt_rand(1, 4) === 1)
            {
                if(mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence()) >= 15)
                {
                    $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Foggy Lake, caught a Ghost Fish, and harvested Quintessence from it.', 'items/resource/quintessence');
                    $this->inventoryService->petCollectsItem('Quintessence', $pet, 'From a Ghost Fish that ' . $pet->getName() . ' fished at a Foggy Lake.', $activityLog);
                    $this->petService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::UMBRA ]);
                }
                else
                {
                    $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Foggy Lake, and caught a Ghost Fish, but failed to harvest any Quintessence from it.', '');
                    $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::UMBRA ]);
                }
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Foggy Lake, and caught a Mung Fish.', 'items/tool/fishing-rod/crooked');
                $this->inventoryService->petCollectsItem('Beans', $pet, $pet->getName() . ' got this from a Mung Fish at a Foggy Lake.', $activityLog);
                $this->petService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);
            }

            $this->creditLackOfReflection($activityLog);

            $this->petService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);
        }
        else
        {
            if(mt_rand(1, 15) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Foggy Lake Lake, but nothing was biting, so ' . $pet->getName() . ' grabbed some Silica Grounds, instead.', '');
                $this->inventoryService->petCollectsItem('Silica Grounds', $pet, $pet->getName() . ' took this from a Foggy Lake.', $activityLog);
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Foggy Lake, and almost caught something, but it got away.', '');
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            }

            $this->petService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, false);
        }

        return $activityLog;
    }

    public function fishedGhoti(Pet $pet)
    {
        $nothingBiting = $this->nothingBiting($pet, 20, 'at the foot of the Volcano');
        if($nothingBiting !== null) return $nothingBiting;

        if(mt_rand(1, 100) === 1 || ($pet->hasMerit(MeritEnum::LUCKY) && mt_rand(1, 100) === 1))
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at the foot of the Volcano; nothing was biting, but ' . $pet->getName() . ' found a piece of Firestone while they were out!', '');
            $this->inventoryService->petCollectsItem('Firestone', $pet, $pet->getName() . ' found this at the foot of the Volcano.', $activityLog);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }
        else if(\mt_rand(1, 10 + $pet->getDexterity() + $pet->getNature() + $pet->getPerception() + $pet->getFishing()) >= 10)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at the foot of the Volcano, and caught a Ghoti!', 'items/tool/fishing-rod/crooked');
            $this->inventoryService->petCollectsItem('Fish', $pet, 'From a Ghoti that ' . $pet->getName() . ' fished at the foot of the Volcano.', $activityLog);

            $extraItem = ArrayFunctions::pick_one([ 'Fish', 'Scales', 'Oil' ]);

            $this->inventoryService->petCollectsItem($extraItem, $pet, 'From a Ghoti that ' . $pet->getName() . ' fished at the foot of the Volcano.', $activityLog);

            $this->petService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);

            $this->creditLackOfReflection($activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at the foot of the Volcano, and almost caught a Ghoti, but it got away.', '');

            $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }

        return $activityLog;
    }

    public function fishedCoralReef(Pet $pet)
    {
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

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at the Coral Reef, and spotted a ' . $item . '!', '');
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' found this at the Coral Reef.', $activityLog);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }
        else if(\mt_rand(1, 10 + $pet->getDexterity() + $pet->getNature() + $pet->getPerception() + $pet->getFishing()) >= 24)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at the Coral Reef, and caught all kinds of stuff!', 'items/tool/fishing-rod/crooked');

            for($x = 0; $x < 3; $x++)
                $this->inventoryService->petCollectsItem(ArrayFunctions::pick_one($possibleItems), $pet, $pet->getName() . ' got this while fishing at the Coral Reef.', $activityLog);

            $this->petService->gainExp($pet, 3, [ PetSkillEnum::NATURE ]);
        }
        else if(\mt_rand(1, 10 + $pet->getDexterity() + $pet->getNature() + $pet->getPerception() + $pet->getFishing()) >= 12)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at the Coral Reef, and caught a couple things!', 'items/tool/fishing-rod/crooked');

            for($x = 0; $x < 2; $x++)
                $this->inventoryService->petCollectsItem(ArrayFunctions::pick_one($possibleItems), $pet, $pet->getName() . ' got this while fishing at the Coral Reef.', $activityLog);

            $this->petService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);
        }
        else
        {
            if(mt_rand(1, 2) === 1)
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at the Coral Reef, but there were a bunch of Hammerheads around.', '');
            else
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at the Coral Reef, but there were a bunch of Jellyfish around.', '');

            $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }

        return $activityLog;
    }
}