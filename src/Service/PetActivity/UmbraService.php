<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;
use App\Enum\PetSkillEnum;
use App\Enum\SpiritCompanionStarEnum;
use App\Functions\ArrayFunctions;
use App\Functions\NumberFunctions;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\PetService;
use App\Service\ResponseService;

class UmbraService
{
    private $petService;
    private $responseService;
    private $inventoryService;

    public function __construct(
        PetService $petService, ResponseService $responseService, InventoryService $inventoryService
    )
    {
        $this->petService = $petService;
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
    }

    public function adventure(Pet $pet)
    {
        $skill = 10 + $pet->getStamina() + $pet->getIntelligence() + $pet->getUmbra(); // psychedelics bonus is built into getUmbra()

        $skill = NumberFunctions::constrain($skill, 1, 12);

        $roll = mt_rand(1, $skill);

        $activityLog = null;
        $changes = new PetChanges($pet);

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
                $activityLog = $this->foundScragglyBush($pet);
                break;
            case 7:
            case 8:
                $activityLog = $this->helpedLostSoul($pet);
                break;
            case 9:
                $activityLog = $this->found2Moneys($pet);
                break;
            case 10:
            case 11:
                $activityLog = $this->fightEvilSpirit($pet);
                break;
            case 12:
                $activityLog = $this->foundNothing($pet, $roll);
                break;
            case 13:
                $activityLog = $this->found2Moneys($pet);
                break;
        }

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));
    }

    private function foundNothing(Pet $pet, int $roll): PetActivityLog
    {
        $exp = \ceil($roll / 10);

        $this->petService->gainExp($pet, $exp, [ PetSkillEnum::UMBRA ]);

        $this->petService->spendTime($pet, \mt_rand(45, 60));

        return $this->responseService->createActivityLog($pet, $pet->getName() . ' crossed into the Umbra, but the Storm was too harsh; ' . $pet->getName() . ' retreated before finding anything.', 'icons/activity-logs/confused');
    }

    private function foundScragglyBush(Pet $pet): PetActivityLog
    {
        $skill = mt_rand(1, 20 + $pet->getGathering() + $pet->getPerception() + $pet->getUmbra() + $pet->getIntelligence());

        if($skill >= 11)
        {
            $reward = mt_rand(1, 3);

            if($reward === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'In the Umbra, ' . $pet->getName() . ' found an outcropping of rocks where the full force of the Storm could not reach. Some Grandparoot was growing there; ' . $pet->getName() . ' took one.', 'items/veggie/grandparoot');
                $this->inventoryService->petCollectsItem('Grandparoot', $pet, $pet->getName() . ' pulled this up from between some rocks in the Umbra.', $activityLog);
            }
            else if($reward === 2)
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'In the Umbra, ' . $pet->getName() . ' found an outcropping of rocks where the full force of the Storm could not reach. A dry bush once grew there; ' . $pet->getName() . ' took a Crooked Stick from its remains.', 'items/plant/stick-crooked');
                $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' took this from the remains of a dead bush in the Umbra.', $activityLog);
            }
            else // if($reward === 3)
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'In the Umbra, ' . $pet->getName() . ' found an outcropping of rocks where the full force of the Storm could not reach. A small Blackberry bush was growing there; ' . $pet->getName() . ' took a few berries.', 'items/fruit/blackberries');
                $this->inventoryService->petCollectsItem('Blackberries', $pet, $pet->getName() . ' harvested these exceptionally-dark Blackberries from a rock-sheltered berry bush in the Umbra.', $activityLog);
            }

            $this->petService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $this->petService->spendTime($pet, \mt_rand(45, 60));

            return $activityLog;
        }
        else
        {
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            return $this->responseService->createActivityLog($pet, 'In the Umbra, ' . $pet->getName() . ' found an outcropping of rocks where the full force of the Storm could not reach. Some weeds were growing there, but nothing of value.', 'icons/activity-logs/confused');
        }
    }

    private function helpedLostSoul(Pet $pet): PetActivityLog
    {
        $hasRelevantSpirit = $pet->getSpiritCompanion() !== null && $pet->getSpiritCompanion()->getStar() === SpiritCompanionStarEnum::ALTAIR;

        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getUmbra());

        $this->petService->spendTime($pet, \mt_rand(45, 60));

        $rewards = [ 'Quintessence' => 'some', 'Music Note' => 'a', 'Ginger' => 'some', 'Oil' => 'some', 'Silica Grounds' => 'some' ];
        $reward = array_rand($rewards);

        if($roll >= 14)
        {
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' met a friendly spirit lost in the Umbra. ' . $pet->getName() . ' was able to point the way; the spirit was very thankful, and insisted that ' . $pet->getName() . ' take ' . $rewards[$reward] . ' ' . $reward . '.', '');
                $this->inventoryService->petCollectsItem($reward, $pet, $pet->getName() . ' received this from a friendly spirit as thanks for helping it navigate the Umbra.', $activityLog);
                $pet->increaseEsteem(1);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' met a friendly spirit lost in the Umbra. ' . $pet->getName() . ' was able to point the way; the spirit was very thankful, and wished ' . $pet->getName() . ' well.', '');
                $pet->increaseEsteem(4);
            }

            return $activityLog;
        }
        else if($hasRelevantSpirit && $roll >= 11)
        {
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' met a friendly spirit lost in the Umbra. ' . $pet->getName() . ' was able to point the way; the spirit was very thankful, and insisted that ' . $pet->getName() . ' take ' . $rewards[$reward] . ' ' . $reward . '.', '');
                $this->inventoryService->petCollectsItem($reward, $pet, $pet->getName() . ' received this from a friendly spirit as thanks for helping it navigate the Umbra.', $activityLog);
                $pet->increaseEsteem(1);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' met a friendly spirit lost in the Umbra. ' . $pet->getName() . ' was able to point the way; the spirit was very thankful, and wished ' . $pet->getName() . ' well.', '');
                $pet->increaseEsteem(4);
            }

            return $activityLog;
        }
        else
        {
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            if($hasRelevantSpirit)
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' met a friendly spirit lost in the Umbra. It asked for directions, but ' . $pet->getName() . ' and ' . $pet->getSpiritCompanion()->getName() . ' didn\'t know how to help.', 'icons/activity-logs/confused');
            else
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' met a friendly spirit lost in the Umbra. It asked for directions, but ' . $pet->getName() . ' didn\'t know how to help.', 'icons/activity-logs/confused');
        }
    }

    private function found2Moneys(Pet $pet): PetActivityLog
    {
        $this->petService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

        if($pet->hasMerit(MeritEnum::LUCKY) && mt_rand(1, 80) === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . $pet->getName() . ' walked along a dark river for a while. On its shore, ' . $pet->getName() . ' spotted a Little Strongbox! Lucky~!', '');

            $this->inventoryService->petCollectsItem('Little Strongbox', $pet, $pet->getName() . ' found this on the shores of a dark river in the Umbra.', $activityLog);

            return $activityLog;
        }

        if(mt_rand(1, 100) === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . $pet->getName() . ' walked along a dark river for a while. On its shore, ' . $pet->getName() . ' spotted a Little Strongbox, and took it!', '');

            $this->inventoryService->petCollectsItem('Little Strongbox', $pet, $pet->getName() . ' found this on the shores of a dark river in the Umbra.', $activityLog);

            return $activityLog;
        }

        if($pet->hasMerit(MeritEnum::LUCKY))
            $die = ArrayFunctions::pick_one([ 'Glowing Four-sided Die', 'Glowing Six-sided Die', 'Glowing Eight-sided Die' ]);
        else
            $die = ArrayFunctions::pick_one([ 'Glowing Four-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Eight-sided Die' ]);

        if($pet->hasMerit(MeritEnum::LUCKY) && mt_rand(1, 50) === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . $pet->getName() . ' walked along a dark river for a while. On its shore, ' . $pet->getName() . ' spotted a ' . $die . '! Lucky~!', '');

            $this->inventoryService->petCollectsItem($die, $pet, $pet->getName() . ' found this on the shores of a dark river in the Umbra.', $activityLog);

            return $activityLog;
        }

        if(mt_rand(1, 80) === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . $pet->getName() . ' walked along a dark river for a while. On its shore, ' . $pet->getName() . ' spotted a ' . $die . ', and took it!', '');

            $this->inventoryService->petCollectsItem($die, $pet, $pet->getName() . ' found this on the shores of a dark river in the Umbra.', $activityLog);

            return $activityLog;
        }

        $pet->getOwner()->increaseMoneys(2);

        return $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . $pet->getName() . ' walked along a dark river for a while. On its shore, ' . $pet->getName() . ' spotted 2~~m~~. No one else was around, so...', 'icons/activity-logs/moneys');
    }

    private function fightEvilSpirit(Pet $pet): PetActivityLog
    {
        $skill = 20 + $pet->getBrawl() + $pet->getUmbra() + $pet->getIntelligence() + $pet->getDexterity();

        $roll = mt_rand(1, $skill);

        if($roll >= 13)
        {
            $prizes = [
                'Silica Grounds', 'Quintessence', 'Aging Powder', 'Fluff'
            ];

            if(mt_rand(1, 100) === 1)
                $prize = 'Blackonite';
            else if(mt_rand(1, 50) === 1)
                $prize = 'Spirit Polymorph Potion Recipe';
            else
                $prize = ArrayFunctions::pick_one($prizes);

            $this->petService->gainExp($pet, 2, [ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);
            $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . $pet->getName() . ' encountered a super gross-looking mummy dragging its long arms through the Umbral sand. It screeched and swung wildly; but ' . $pet->getName() . ' beat it back, and claimed its ' . $prize . '!', '');
            $this->inventoryService->petCollectsItem($prize, $pet, $pet->getName() . ' defeated a gross-looking mummy with crazy-long arms, and took this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, 'While exploring the Umbra, ' . $pet->getName() . ' encountered a super gross-looking mummy dragging its long arms through the Umbral sand. It screeched and swung wildly; ' . $pet->getName() . ' made a hasty retreat.', '');
        }
    }
}