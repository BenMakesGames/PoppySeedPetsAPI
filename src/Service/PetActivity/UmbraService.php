<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\PetSkillEnum;
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

        $skill = NumberFunctions::constrain($skill, 1, 10);

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
                $activityLog = $this->foundBasicStuff($pet);
        }

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));
    }

    private function foundNothing(Pet $pet, int $roll): PetActivityLog
    {
        $exp = \ceil($roll / 10);

        $this->petService->gainExp($pet, $exp, [ PetSkillEnum::UMBRA, PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA ]);

        $pet->spendTime(\mt_rand(45, 60));

        return $this->responseService->createActivityLog($pet, $pet->getName() . ' crossed into the Umbra, but the Storm was too harsh; ' . $pet->getName() . ' retreated before finding anything.', 'icons/activity-logs/confused');
    }

    private function foundBasicStuff(Pet $pet): PetActivityLog
    {
        $skill = mt_rand(1, 20 + $pet->getGathering() + $pet->getPerception() + $pet->getUmbra() + $pet->getIntelligence());

        if($skill >= 11)
        {
            $reward = mt_rand(1, 3);

            if($reward === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found an outcropping of rocks where the full force of the Storm could not reach. A small Tea Bush was growing there; ' . $pet->getName() . ' took a few.', 'items/veggie/tea-leaves');
                $this->inventoryService->petCollectsItem('Tea Leaves', $pet, $pet->getName() . ' harvested this from an Umbral Tea Bush.', $activityLog);
            }
            else if($reward === 2)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found an outcropping of rocks where the full force of the Storm could not reach. A dry bush once grew there; ' . $pet->getName() . ' took a Crooked Stick from its remains.', 'items/plant/stick-crooked');
                $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' took this from the remains of a bush in the Umbra.', $activityLog);
            }
            else // if($reward === 3)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found an outcropping of rocks where the full force of the Storm could not reach. A Blackberry bush was flourishing there; ' . $pet->getName() . ' took a few.', 'items/fruit/blackberries');
                $this->inventoryService->petCollectsItem('Blackberries', $pet, $pet->getName() . ' harvested these exceptionally-dark Blackberries from a rock-sheltered bush in the Umbra.', $activityLog);
            }

            $this->petService->gainExp($pet, 1, [ PetSkillEnum::PERCEPTION, PetSkillEnum::UMBRA, PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::PERCEPTION ]);
            $pet->spendTime(\mt_rand(45, 60));

            return $activityLog;
        }
        else
        {
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::PERCEPTION, PetSkillEnum::UMBRA, PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::PERCEPTION ]);
            $pet->spendTime(\mt_rand(45, 60));
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' found an outcropping of rocks where the full force of the Storm could not reach. Some weeds were growing there, but nothing of value.', 'icons/activity-logs/confused');
        }
    }
}