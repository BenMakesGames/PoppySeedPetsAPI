<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
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
        $maxSkill = 5 + $pet->getDexterity() + $pet->getNature() + $pet->getFishing() - $pet->getWhack();

        if($maxSkill > 11) $maxSkill = 11;
        else if($maxSkill < 1) $maxSkill = 1;

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
                $activityLog = $this->fishedPlazaFountain($pet);
                break;
        }

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));
    }

    private function failedToFish(Pet $pet): PetActivityLog
    {
        $pet->spendTime(mt_rand(30, 60));
        return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to fish, but couldn\'t find a quiet place to do so.');
    }

    private function nothingBiting(Pet $pet, int $percentChance, string $atLocationName): ?PetActivityLog
    {
        if(\mt_rand(1, 100) <= $percentChance)
        {
            $this->petService->gainExp($pet, 1, [ 'dexterity', 'nature' ]);

            $pet->spendTime(mt_rand(45, 60));

            return $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing ' . $atLocationName . ', but nothing was biting.');
        }

        return null;
    }

    private function fishedSmallLake(Pet $pet): PetActivityLog
    {
        $nothingBiting = $this->nothingBiting($pet, 20, 'at a Small Lake');
        if($nothingBiting !== null) return $nothingBiting;

        if(\mt_rand(1, 10 + $pet->getDexterity() + $pet->getNature() + $pet->getPerception() + $pet->getFishing()) >= 5)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Small Lake, and caught a Mini Minnow.');
            $this->inventoryService->petCollectsItem('Fish', $pet, 'From a Mini Minnow that ' . $pet->getName() . ' fished at a Small Lake.');
            $this->petService->gainExp($pet, 1, ['dexterity', 'nature', 'perception']);

            $pet->spendTime(mt_rand(45, 60));
        }
        else
        {
            if(mt_rand(1, 15) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Small Lake, but nothing was biting, so ' . $pet->getName() . ' grabbed some Silica Grounds, instead.');
                $this->inventoryService->petCollectsItem('Silica Grounds', $pet, $pet->getName() . ' took this from a Small Lake.');
                $this->petService->gainExp($pet, 1, ['dexterity', 'nature', 'perception']);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Small Lake, and almost caught a Mini Minnow, but it got away.');
                $this->petService->gainExp($pet, 1, ['dexterity', 'nature', 'perception']);
            }

            $pet->spendTime(mt_rand(45, 60));
        }

        return $activityLog;
    }

    private function fishedUnderBridge(Pet $pet): PetActivityLog
    {
        $nothingBiting = $this->nothingBiting($pet, 15, 'Under a Bridge');
        if($nothingBiting !== null) return $nothingBiting;

        if(\mt_rand(1, 10 + $pet->getDexterity() + $pet->getNature() + $pet->getStrength() + $pet->getFishing()) >= 6)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing Under a Bridge, and caught a Muscly Trout.');
            $this->inventoryService->petCollectsItem('Fish', $pet, 'From a Muscly Trout that ' . $pet->getName() . ' fished Under a Bridge.');

            if(\mt_rand(1, 20 + $pet->getIntelligence()) >= 15)
                $this->inventoryService->petCollectsItem('Scales', $pet, 'From a Muscly Trout that ' . $pet->getName() . ' fished Under a Bridge.');

            $this->petService->gainExp($pet, 1, ['dexterity', 'nature', 'strength']);

            $pet->spendTime(mt_rand(45, 60));
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing Under a Bridge, and almost caught a Muscly Trout, but it got away.');
            $this->petService->gainExp($pet, 1, ['dexterity', 'nature', 'strength']);

            $pet->spendTime(mt_rand(45, 60));
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
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Roadside Creek, and a Huge Toad bit the line! ' . $pet->getName() . ' used all their strength to reel it in!');
                $this->inventoryService->petCollectsItem('Toad Legs', $pet, 'From a Huge Toad that ' . $pet->getName() . ' fished at a Roadside Creek.');

                if(\mt_rand(1, 20 + $pet->getNature()) >= 15)
                    $this->inventoryService->petCollectsItem('Toadstool', $pet, 'From a Huge Toad that ' . $pet->getName() . ' fished at a Roadside Creek.');

                $this->petService->gainExp($pet, 2, [ 'dexterity', 'nature', 'stamina', 'strength' ]);

                $pet->spendTime(mt_rand(45, 75));
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Roadside Creek, and a Huge Toad bit the line! ' . $pet->getName() . ' tried to reel it in, but it was too strong, and got away.');
                $this->petService->gainExp($pet, 1, ['dexterity', 'nature', 'stamina', 'strength']);

                $pet->spendTime(mt_rand(45, 75));
            }
        }
        else
        {
            // singing fish
            if(\mt_rand(1, 10 + $pet->getDexterity() + $pet->getNature() + $pet->getPerception() + $pet->getFishing()) >= 6)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Roadside Creek, and caught a Singing Fish!');
                $this->inventoryService->petCollectsItem(mt_rand(1, 2) === 1 ? 'Plastic' : 'Fish', $pet, 'From a Singing Fish that ' . $pet->getName() . ' fished at a Roadside Creek.');

                if(\mt_rand(1, 20 + $pet->getPerception() + $pet->getMusic()) >= 10)
                    $this->inventoryService->petCollectsItem('Music Note', $pet, 'From a Singing Fish that ' . $pet->getName() . ' fished at a Roadside Creek.');

                $this->petService->gainExp($pet, 2, ['dexterity', 'nature', 'perception']);

                $pet->spendTime(mt_rand(30, 60));
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Roadside Creek, and almost caught a Singing Fish, but it got away.');
                $this->petService->gainExp($pet, 1, ['dexterity', 'nature', 'perception']);

                $pet->spendTime(mt_rand(30, 60));
            }
        }

        return $activityLog;
    }

    private function fishedWaterfallBasin(Pet $pet): PetActivityLog
    {
        $nothingBiting = $this->nothingBiting($pet, 20,  'in a Waterfall Basin');
        if($nothingBiting !== null) return $nothingBiting;

        if(\mt_rand(1, 100) === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Waterfall Basin, and reeled in a Little Strongbox!');
            $this->petService->gainExp($pet, 1, ['nature', 'perception']);
            $this->inventoryService->petCollectsItem('Little Strongbox', $pet, $pet->getName() . ' was fishing in a Waterfall Basin, and one of these got caught on the line!');

            $pet->spendTime(mt_rand(45, 75));
        }
        else if(\mt_rand(1, 5) === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing at a Waterfall Basin, and reeled in a Mermaid Egg!');
            $this->petService->gainExp($pet, 1, ['nature', 'perception']);
            $this->inventoryService->petCollectsItem('Mermaid Egg', $pet, $pet->getName() . ' was fishing in a Waterfall Basin, and one of these got caught on the line!');

            $pet->spendTime(mt_rand(30, 45));
        }
        else
        {
            if(\mt_rand(1, 10 + $pet->getDexterity() + $pet->getNature() + $pet->getPerception() + $pet->getFishing()) >= 7)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing in a Waterfall Basin, and caught a Medium Minnow.');
                $this->petService->gainExp($pet, 2, ['dexterity', 'nature', 'perception']);

                $this->inventoryService->petCollectsItem('Fish', $pet, 'From a Medium Minnow that ' . $pet->getName() . ' fished in a Waterfall Basin.');

                if(\mt_rand(1, 20 + $pet->getNature()) >= 10)
                    $this->inventoryService->petCollectsItem('Fish', $pet, 'From a Medium Minnow that ' . $pet->getName() . ' fished in a Waterfall Basin.');

                $pet->spendTime(mt_rand(45, 60));
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went fishing in a Waterfall Basin, and almost caught a Medium Minnow, but it got away.');
                $this->petService->gainExp($pet, 1, ['dexterity', 'nature', 'perception']);

                $pet->spendTime(mt_rand(45, 60));
            }
        }

        return $activityLog;
    }

    private function fishedPlazaFountain(Pet $pet): PetActivityLog
    {
        $moneys = \mt_rand(2, 9);
        $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' fished around in the Plaza Fountain, and grabbed ' . $moneys . ' moneys.');
        $this->petService->gainExp($pet, 1, [ 'perception' ]);
        $pet->getOwner()->increaseMoneys($moneys);

        $pet->spendTime(mt_rand(30, 45));

        return $activityLog;
    }
}