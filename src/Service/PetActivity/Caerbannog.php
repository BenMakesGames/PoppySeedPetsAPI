<?php
namespace App\Service\PetActivity;

use App\Entity\PetActivityLog;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ActivityHelpers;
use App\Functions\AdventureMath;
use App\Functions\ArrayFunctions;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\ItemRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;

class Caerbannog
{
    private EntityManagerInterface $em;
    private IRandom $rng;
    private InventoryService $inventoryService;
    private PetExperienceService $petExperienceService;
    private ResponseService $responseService;
    private ItemRepository $itemRepository;

    public function __construct(
        EntityManagerInterface $em, Squirrel3 $rng, InventoryService $inventoryService, ItemRepository $itemRepository,
        PetExperienceService $petExperienceService, ResponseService $responseService
    )
    {
        $this->em = $em;
        $this->rng = $rng;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->responseService = $responseService;
        $this->itemRepository = $itemRepository;
    }

    public function adventure(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->em->remove($pet->getTool());
        $pet->setTool(null);

        $changes = new PetChanges($pet);

        // TODO: other things the pet can do in here?
        $activityLog = $this->fightEvilRabbit($petWithSkills);

        $activityLog
            ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Adventure!' ]))
            ->setChanges($changes->compare($pet))
        ;

        if(AdventureMath::petAttractsBug($this->rng, $pet, 75))
            $this->inventoryService->petAttractsRandomBug($pet);

        return $activityLog;
    }

    public function fightEvilRabbit(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $possibleLoot = [ 'Carrot', 'Crooked Stick', 'Wheat', 'Wheat', 'Dandelion', 'Coriander Flower', 'Mint', 'Fluff' ];

        $petName = ActivityHelpers::PetName($pet);

        $roll = $this->rng->rngNextInt(1, 20) + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl()->getTotal();

        $loot[] = $this->rng->rngNextFromArray($possibleLoot);

        // ==================
        // no special drops on holidays, or else the "correct" way to play is to never use Carrot Keys EXCEPT on holidays!
        // ==================

        if($roll >= 10)
        {
            $loot[] = $this->rng->rngNextFromArray($possibleLoot);
            $exp = 2;

            if($roll >= 15)
            {
                $loot[] = $this->rng->rngNextFromArray($possibleLoot);
                $exp++;
            }

            if($roll >= 20)
            {
                $loot[] = $this->rng->rngNextFromArray($possibleLoot);
                $exp++;
            }

            if($roll >= 40)
            {
                $loot[] = 'Blue Plastic Egg';
                $exp++;
            }
            else
            {
                if($roll >= 25)
                {
                    $loot[] = $this->rng->rngNextFromArray($possibleLoot);
                    $exp++;
                }

                if($roll >= 30)
                {
                    $loot[] = $this->rng->rngNextFromArray($possibleLoot);
                    $exp++;
                }
            }

            $pet->increaseEsteem(ceil($exp / 2) * 2);
            $activityLog = $this->responseService->createActivityLog($pet, $petName . ' went to the Caerbannog Cave, and encountered one of the terrifying creatures living there! ' . $petName . ' proved victorious, returning home with ' . ArrayFunctions::list_nice($loot) . '!', 'items/key/carrot')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Fighting' ]))
            ;
            $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            $pet->increaseSafety(-2);
            $lootItem = $this->itemRepository->deprecatedFindOneByName($loot[0]);

            $activityLog = $this->responseService->createActivityLog($pet, $petName . ' went to the Caerbannog Cave, and encountered one of the terrifying creatures living there, and was forced to flee! (They grabbed ' . $lootItem->getNameWithArticle() . ' on their way out, at least!)', 'items/key/carrot')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Fighting' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
        }

        foreach($loot as $lootName)
            $this->inventoryService->petCollectsItem($lootName, $pet, $pet->getName() . ' looted this from the Caerbannog Cave.', $activityLog);

        return $activityLog;
    }
}