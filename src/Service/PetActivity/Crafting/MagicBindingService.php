<?php
namespace App\Service\PetActivity\Crafting;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\PetService;
use App\Service\ResponseService;

class MagicBindingService
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
        $possibilities = [];

        if(array_key_exists('Quintessence', $quantities))
        {
            if(array_key_exists('Stereotypical Torch', $quantities))
                $possibilities[] = [ $this, 'createCrazyHotTorch' ];

            if(array_key_exists('Hourglass', $quantities))
                $possibilities[] = [ $this, 'createMagicHourglass' ];

            if(array_key_exists('Straw Broom', $quantities))
                $possibilities[] = [ $this, 'createWitchsBroom' ];

            if(array_key_exists('Blackonite', $quantities))
                $possibilities[] = [ $this, 'createBunchOfDice' ];

            // magic scrolls
            if(array_key_exists('Paper', $quantities))
            {
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
            }

            if(array_key_exists('Ceremonial Trident', $quantities))
            {
                if(array_key_exists('Seaweed', $quantities) && array_key_exists('Sand Dollar', $quantities))
                    $possibilities[] = [ $this, 'createCeremonyOfSandAndSea' ];

                if(array_key_exists('Blackonite', $quantities))
                    $possibilities[] = [ $this, 'createCeremonyOfShadows' ];

                if(array_key_exists('Firestone', $quantities))
                    $possibilities[] = [ $this, 'createCeremonyOfFire' ];
            }
        }

        return $possibilities;
    }

    public function createCrazyHotTorch(Pet $pet): PetActivityLog
    {
        $umbraCheck = \mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence());
        $craftsCheck = \mt_rand(1, 20 + $pet->getCrafts() + $pet->getDexterity() + $pet->getIntelligence());

        if($umbraCheck === 1)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Stereotypical Torch', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Stereotypical Torch, but mishandled the Quintessence. The torch flared up, and ' . $pet->getName() . ' dropped the torch, breaking it :( Some Charcoal was left over, at least...', '');
            $this->inventoryService->petCollectsItem('Charcoal', $pet, $pet->getName() . ' accidentally created this while trying to enchant a Stereotypical Torch.', $activityLog);
            return $activityLog;
        }
        else if($umbraCheck === 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Stereotypical Torch, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($craftsCheck < 13)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::DEXTERITY, PetSkillEnum::INTELLIGENCE, PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Stereotypical Torch, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Stereotypical Torch', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::DEXTERITY, PetSkillEnum::INTELLIGENCE, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' enchanted a Stereotypical Torch into a Crazy-hot Torch.', '');
            $this->inventoryService->petCollectsItem('Crazy-hot Torch', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    public function createBunchOfDice(Pet $pet): PetActivityLog
    {
        $umbraCheck = \mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence());

        if($umbraCheck <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a block of glowing dice, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 15)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a block of glowing dice, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $numberOfDice = mt_rand(3, 5);

            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Blackonite', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem($numberOfDice);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a block of glowing dice from a chunk of Blackonite, then gently tapped it to break the dice apart. ' . $numberOfDice . ' were made!', '');

            for($x = 0; $x < $numberOfDice; $x++)
                $this->inventoryService->petCollectsItem(ArrayFunctions::pick_one([ 'Glowing Four-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Eight-sided Die' ]), $pet, $pet->getName() . ' got this from a block of glowing dice that they made.', $activityLog);

            return $activityLog;
        }
    }

    public function createMagicHourglass(Pet $pet): PetActivityLog
    {
        $umbraCheck = \mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence());

        if($umbraCheck <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant an Hourglass, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 15)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant an Hourglass, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Hourglass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' enchanted an Hourglass. It\'s _magic_ now!', '');
            $this->inventoryService->petCollectsItem('Magic Hourglass', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    private function bindCeremonialTrident(Pet $pet, array $otherMaterials, string $makes)
    {
        $umbraCheck = \mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence());
        $craftsCheck = \mt_rand(1, 20 + $pet->getCrafts() + $pet->getDexterity() + $pet->getIntelligence());

        if($umbraCheck <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Ceremonial Trident, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($craftsCheck < 20)
        {
            $this->petService->spendTime($pet, \mt_rand(46, 60));
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::UMBRA, PetSkillEnum::CRAFTS, PetSkillEnum::DEXTERITY ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant an Ceremonial Trident, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75));
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            foreach($otherMaterials as $material)
                $this->inventoryService->loseItem($material, $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Ceremonial Trident', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 3, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::UMBRA, PetSkillEnum::CRAFTS, PetSkillEnum::DEXTERITY ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' used a Ceremonial Trident to materialize the ' . $makes . '!', '');
            $this->inventoryService->petCollectsItem($makes, $pet, $pet->getName() . ' made this real.', $activityLog);
            return $activityLog;
        }

    }

    public function createCeremonyOfShadows(Pet $pet): PetActivityLog
    {
        return $this->bindCeremonialTrident($pet, [ 'Blackonite' ], 'Ceremony of Shadows');
    }

    public function createCeremonyOfFire(Pet $pet): PetActivityLog
    {
        return $this->bindCeremonialTrident($pet, [ 'Firestone' ], 'Ceremony of Fire');
    }

    public function createCeremonyOfSandAndSea(Pet $pet): PetActivityLog
    {
        return $this->bindCeremonialTrident($pet, [ 'Seaweed', 'Sand Dollar' ], 'Ceremony of Sand and Sea');
    }

    public function createWitchsBroom(Pet $pet): PetActivityLog
    {
        $umbraCheck = \mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence());

        if($umbraCheck <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1)
            {
                $pet->increaseEsteem(-1);
                $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Witch\'s Broom, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
            }
            else
            {
                $pet->increaseEsteem(-1);
                $this->inventoryService->loseItem('Witch-hazel', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Witch\'s Broom, but snapped the Witch-hazel in half :(', '');
            }
        }
        else if($umbraCheck < 14)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Witch\'s Broom, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Straw Broom', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Witch-hazel', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' enchanted a broom into a Witch\'s Broom!', '');
            $this->inventoryService->petCollectsItem('Witch\'s Broom', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    public function createGenericScroll(Pet $pet, string $uniqueIngredient, string $scroll): PetActivityLog
    {
        $umbraCheck = \mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence());
        $craftsCheck = \mt_rand(1, 20 + $pet->getCrafts() + $pet->getDexterity() + $pet->getIntelligence());

        if($craftsCheck <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->inventoryService->loseItem('Paper', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::DEXTERITY, PetSkillEnum::INTELLIGENCE ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a ' . $scroll . ', but accidentally tore the Paper in the process :(', 'icons/activity-logs/torn-to-bits');
        }
        else if($umbraCheck <= 3)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a ' . $scroll . ', but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($craftsCheck < 15)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::DEXTERITY, PetSkillEnum::INTELLIGENCE, PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a ' . $scroll . ', but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Paper', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem($uniqueIngredient, $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::DEXTERITY, PetSkillEnum::INTELLIGENCE, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a ' . $scroll . '.', '');
            $this->inventoryService->petCollectsItem($scroll, $pet, $pet->getName() . ' bound this.', $activityLog);
            return $activityLog;
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