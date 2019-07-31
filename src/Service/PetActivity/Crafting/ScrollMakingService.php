<?php
namespace App\Service\PetActivity\Crafting;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\PetSkillEnum;
use App\Service\InventoryService;
use App\Service\PetService;
use App\Service\ResponseService;

class ScrollMakingService
{
    private $inventoryService;
    private $petService;
    private $responseService;

    public function __construct(
        InventoryService $inventoryService, PetService $petService, ResponseService $responseService
    )
    {
        $this->inventoryService = $inventoryService;
        $this->petService = $petService;
        $this->responseService = $responseService;
    }

    public function getCraftingPossibilities(Pet $pet, array $quantities): array
    {
        if(!array_key_exists('Paper', $quantities) || !array_key_exists('Quintessence', $quantities))
            return [];

        $possibilities = [];

        if(array_key_exists('Red', $quantities))
            $possibilities[] = [ $this, 'createFruitScroll' ];

        if(array_key_exists('Wheat Flower', $quantities))
            $possibilities[] = [ $this, 'createFarmerScroll' ];

        if(array_key_exists('Rice Flower', $quantities))
            $possibilities[] = [ $this, 'createFlowerScroll' ];

        if(array_key_exists('Seaweed', $quantities))
            $possibilities[] = [ $this, 'createSeaScroll' ];

        if(array_key_exists('Silver Bar', $quantities))
            $possibilities[] = [ $this, 'createSilverScroll' ];

        if(array_key_exists('Gold Bar', $quantities))
            $possibilities[] = [ $this, 'createGoldScroll' ];

        if(array_key_exists('Musical Scales', $quantities))
            $possibilities[] = [ $this, 'createMusicScroll' ];

        return $possibilities;
    }

    public function createGenericScroll(Pet $pet, string $uniqueIngredient, string $scroll): PetActivityLog
    {
        $umbraCheck = \mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence());
        $craftsCheck = \mt_rand(1, 20 + $pet->getCrafts() + $pet->getDexterity() + $pet->getIntelligence());

        if($craftsCheck <= 2)
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->inventoryService->loseItem('Paper', $pet->getOwner(), 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::DEXTERITY, PetSkillEnum::INTELLIGENCE ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried create a ' . $scroll . ', but accidentally tore the Paper in the process :(', 'icons/activity-logs/torn-to-bits');
        }
        else if($umbraCheck <= 3)
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried create a ' . $scroll . ', but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($craftsCheck < 15)
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::DEXTERITY, PetSkillEnum::INTELLIGENCE, PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried create a ' . $scroll . ', but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $pet->spendTime(\mt_rand(45, 60));
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), 1);
            $this->inventoryService->loseItem('Paper', $pet->getOwner(), 1);
            $this->inventoryService->loseItem($uniqueIngredient, $pet->getOwner(), 1);
            $this->inventoryService->petCollectsItem($scroll, $pet, $pet->getName() . ' bound this.');
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::DEXTERITY, PetSkillEnum::INTELLIGENCE, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' created a ' . $scroll . '.', '');
        }
    }

    public function createFruitScroll(Pet $pet): PetActivityLog
    {
        return $this->createGenericScroll($pet, 'Red', 'Scroll of Fruit');
    }

    public function createFarmerScroll(Pet $pet): PetActivityLog
    {
        return $this->createGenericScroll($pet, 'Wheat Flower', 'Farmer\'s Scroll');
    }

    public function createFlowerScroll(Pet $pet): PetActivityLog
    {
        return $this->createGenericScroll($pet, 'Rice Flower', 'Scroll of Flowers');
    }

    public function createSeaScroll(Pet $pet): PetActivityLog
    {
        return $this->createGenericScroll($pet, 'Seaweed', 'Scroll of the Sea');
    }

    public function createSilverScroll(Pet $pet): PetActivityLog
    {
        return $this->createGenericScroll($pet, 'Silver Bar', 'Minor Scroll of Riches');
    }

    public function createGoldScroll(Pet $pet): PetActivityLog
    {
        return $this->createGenericScroll($pet, 'Gold Bar', 'Major Scroll of Riches');
    }

    public function createMusicScroll(Pet $pet): PetActivityLog
    {
        return $this->createGenericScroll($pet, 'Musical Scales', 'Scroll of Songs');
    }
}