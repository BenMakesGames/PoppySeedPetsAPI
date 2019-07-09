<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Model\PetChanges;
use App\Repository\ItemRepository;
use App\Service\InventoryService;
use App\Service\PetActivity\Crafting\RefiningService;
use App\Service\PetService;
use App\Service\ResponseService;

class CraftingService
{
    private $responseService;
    private $inventoryService;
    private $petService;
    private $itemRepository;
    private $refiningService;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetService $petService,
        ItemRepository $itemRepository, RefiningService $refiningService
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petService = $petService;
        $this->itemRepository = $itemRepository;
        $this->refiningService = $refiningService;
    }

    public function adventure(Pet $pet)
    {
        $quantities = $this->itemRepository->getInventoryQuantities($pet->getOwner(), 'name');

        $possibilities = [];

        if(array_key_exists('Fluff', $quantities))
        {
            $possibilities[] = [ $this, 'createStringFromFluff' ];

            if($quantities['Fluff']->quantity >= 2)
                $possibilities[] = [ $this, 'createWhiteCloth' ];
        }

        if(array_key_exists('Tea Leaves', $quantities))
        {
            if($quantities['Tea Leaves']->quantity >= 2)
                $possibilities[] = [ $this, 'createYellowDyeFromTeaLeaves' ];
        }

        if(array_key_exists('Scales', $quantities))
        {
            if($quantities['Scales']->quantity >= 2)
                $possibilities[] = [ $this, 'createGreenDyeFromScales' ];
        }

        if(array_key_exists('Crooked Stick', $quantities))
        {
            if(array_key_exists('String', $quantities))
                $possibilities[] = [ $this, 'createCrookedFishingRod' ];

            if(array_key_exists('White Cloth', $quantities))
                $possibilities[] = [ $this, 'createStereotypicalTorch' ];

            if($quantities['Crooked Stick']->quantity >= 2 && array_key_exists('String', $quantities) && $quantities['String']->quantity >= 2)
                $possibilities[] = [ $this, 'createWoodenSword' ];
        }

        if(array_key_exists('Crooked Fishing Rod', $quantities) && array_key_exists('Yellow Dye', $quantities) && array_key_exists('Green Dye', $quantities))
        {
            $possibilities[] = [ $this, 'createPaintedFishingRod' ];
        }

        // pets won't try any refining tasks if they don't feel sufficiently safe
        if($pet->getSafety() > 0)
        {
            if(array_key_exists('Iron Ore', $quantities))
            $possibilities[] = [ $this->refiningService, 'createIronBar' ];

            if(array_key_exists('Silver Ore', $quantities))
                $possibilities[] = [ $this->refiningService, 'createSilverBar' ];

            if(array_key_exists('Gold Ore', $quantities))
                $possibilities[] = [ $this->refiningService, 'createGoldBar' ];

            if(mt_rand(1, 10 + $pet->getSkills()->getCrafts() + $pet->getSkills()->getIntelligence()) >= 10)
            {
                if(array_key_exists('Iron Bar', $quantities))
                    $possibilities[] = [ $this->refiningService, 'createIronKey' ];

                if(array_key_exists('Silver Bar', $quantities))
                    $possibilities[] = [ $this->refiningService, 'createSilverKey' ];

                if(array_key_exists('Gold Bar', $quantities))
                    $possibilities[] = [ $this->refiningService, 'createGoldKey' ];
            }
        }

        if(count($possibilities) === 0)
        {
            $pet->spendTime(\mt_rand(30, 60));

            $this->responseService->createActivityLog($pet, $pet->getName() . ' wanted to make something, but couldn\'t find any materials to work with.');
            return;
        }

        $method = $possibilities[\mt_rand(0, count($possibilities) - 1)];

        $activityLog = null;
        $changes = new PetChanges($pet);

        $activityLog = $method($pet);

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));
    }

    private function createStringFromFluff(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getSkills()->getIntelligence() + $pet->getSkills()->getDexterity() + $pet->getSkills()->getCrafts());
        if($roll <= 2)
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->inventoryService->loseItem('Fluff', $pet->getOwner(), 1);
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'dexterity', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to spin some Fluff into String, but messed it up; the Fluff was wasted :(');
        }
        else if($roll >= 10)
        {
            $pet->spendTime(\mt_rand(45, 60));
            $this->inventoryService->loseItem('Fluff', $pet->getOwner(), 1);
            $this->inventoryService->petCollectsItem('String', $pet, $pet->getName() . ' spun this from Fluff.');
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'dexterity', 'crafts' ]);
            $pet->increaseEsteem(1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' spun some Fluff into String.');
        }
        else
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'dexterity', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to spin some Fluff into String, but couldn\'t figure it out.');
        }
    }

    private function createYellowDyeFromTeaLeaves(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getSkills()->getIntelligence() + $pet->getSkills()->getNature() + $pet->getSkills()->getCrafts());
        if($roll <= 2)
        {
            $pet->spendTime(\mt_rand(45, 60));
            $this->inventoryService->loseItem('Tea Leaves', $pet->getOwner(), 1);
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'nature', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to extract Yellow Dye from Tea Leaves, but messed it up, ruining the Tea Leaves :(');
        }
        else if($roll >= 12)
        {
            $pet->spendTime(\mt_rand(60, 75));
            $this->inventoryService->loseItem('Tea Leaves', $pet->getOwner(), 2);
            $this->inventoryService->petCollectsItem('Yellow Dye', $pet, $pet->getName() . ' extracted this from Tea Leaves.');
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'nature', 'crafts' ]);
            $pet->increaseEsteem(1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' extracted Yellow Dye from some Tea Leaves.');
        }
        else
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'nature', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' wanted to extract Yellow Dye from some Tea Leaves, but wasn\'t sure how to start.');
        }
    }

    private function createGreenDyeFromScales(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getSkills()->getIntelligence() + $pet->getSkills()->getNature() + $pet->getSkills()->getCrafts());
        if($roll <= 2)
        {
            $pet->spendTime(\mt_rand(45, 60));
            $this->inventoryService->loseItem('Scales', $pet->getOwner(), 1);
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'nature', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to extract Green Dye from Scales, but messed it up, ruining the Scales :(');
        }
        else if($roll >= 12)
        {
            $pet->spendTime(\mt_rand(60, 75));
            $this->inventoryService->loseItem('Scales', $pet->getOwner(), 2);
            $this->inventoryService->petCollectsItem('Green Dye', $pet, $pet->getName() . ' extracted this from Scales.');
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'nature', 'crafts' ]);
            $pet->increaseEsteem(1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' extracted Green Dye from some Scales.');
        }
        else
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'nature', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' wanted to extract Green Dye from some Scales, but wasn\'t sure how to start.');
        }
    }

    private function createWhiteCloth(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getSkills()->getIntelligence() + $pet->getSkills()->getDexterity() + $pet->getSkills()->getCrafts());
        if($roll <= 2)
        {
            $pet->spendTime(\mt_rand(45, 60));
            $this->inventoryService->loseItem('Fluff', $pet->getOwner(), 1);
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'dexterity', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to spin some Fluff into String, but messed it up; a Fluff was wasted :(');
        }
        else if($roll >= 15)
        {
            $pet->spendTime(\mt_rand(60, 75));
            $this->inventoryService->loseItem('Fluff', $pet->getOwner(), 2);
            $this->inventoryService->petCollectsItem('White Cloth', $pet, $pet->getName() . ' weaved this from Fluff.');
            $this->petService->gainExp($pet, 2, [ 'intelligence', 'dexterity', 'crafts' ]);
            $pet->increaseEsteem(1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' weaved some Fluff into White Cloth.');
        }
        else
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'dexterity', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to weave some Fluff into White Cloth, but couldn\'t figure it out.');
        }
    }

    private function createCrookedFishingRod(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getSkills()->getIntelligence() + $pet->getSkills()->getDexterity() + \max($pet->getSkills()->getCrafts(), $pet->getSkills()->getNature()));

        if($roll <= 3)
        {
            $pet->spendTime(\mt_rand(30, 60));
            if(\mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), 1);
                $this->petService->gainExp($pet, 1, [ 'intelligence', 'dexterity', 'crafts', 'nature' ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Crooked Fishing Rod, but broke the String :(');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), 1);
                $this->petService->gainExp($pet, 1, [ 'intelligence', 'dexterity', 'crafts',  'nature' ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Crooked Fishing Rod, but broke the Crooked Stick :(');

            }
        }
        else if($roll >= 12)
        {
            $pet->spendTime(\mt_rand(45, 60));
            $this->inventoryService->loseItem('String', $pet->getOwner(), 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), 1);
            $this->inventoryService->petCollectsItem('Crooked Fishing Rod', $pet, $pet->getName() . ' created this from String and a Crooked Stick.');
            $this->petService->gainExp($pet, 2, [ 'intelligence', 'dexterity', 'crafts',  'nature' ]);
            $pet->increaseEsteem(2);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Crooked Fishing Rod.');
        }
        else
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'dexterity', 'crafts',  'nature' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Crooked Fishing Rod, but couldn\'t figure it out.');
        }
    }

    private function createStereotypicalTorch(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getSkills()->getIntelligence() + $pet->getSkills()->getDexterity() + \max($pet->getSkills()->getCrafts(), $pet->getSkills()->getNature()));

        if($roll <= 2)
        {
            $pet->spendTime(\mt_rand(15, 30));
            if(\mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), 1);
                $this->petService->gainExp($pet, 1, [ 'intelligence', 'dexterity', 'crafts', 'nature' ]);
                $pet->increaseEsteem(-1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Stereotypical Torch, but accidentally tore the White Cloth into useless shapes :(');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), 1);
                $this->petService->gainExp($pet, 1, [ 'intelligence', 'dexterity', 'crafts',  'nature' ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Stereotypical Torch, but accidentally split the Crooked Stick :(');
            }
        }
        else if($roll >= 8)
        {
            $pet->spendTime(\mt_rand(30, 45));
            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), 1);
            $this->inventoryService->petCollectsItem('Stereotypical Torch', $pet, $pet->getName() . ' created this from White Cloth and a Crooked Stick.');
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'dexterity', 'crafts',  'nature' ]);
            $pet->increaseEsteem(2);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Stereotypical Torch.');
        }
        else
        {
            $pet->spendTime(\mt_rand(15, 45));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'dexterity', 'crafts',  'nature' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Stereotypical Torch, but couldn\'t figure it out.');
        }
    }

    private function createWoodenSword(Pet $pet)
    {
        $roll = \mt_rand(1, 20 + $pet->getSkills()->getIntelligence() + $pet->getSkills()->getDexterity() + \max($pet->getSkills()->getCrafts(), $pet->getSkills()->getBrawl()));

        if($roll <= 3)
        {
            $pet->spendTime(\mt_rand(30, 60));
            if(\mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), 1);
                $this->petService->gainExp($pet, 1, [ 'intelligence', 'dexterity', 'crafts', 'brawl' ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Wooden Sword, but broke the String :(');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), 1);
                $this->petService->gainExp($pet, 1, [ 'intelligence', 'dexterity', 'crafts',  'brawl' ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Wooden Sword, but broke the Crooked Stick :(');

            }
        }
        else if($roll >= 12)
        {
            $pet->spendTime(\mt_rand(45, 60));
            $this->inventoryService->loseItem('String', $pet->getOwner(), 2);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), 2);
            $this->inventoryService->petCollectsItem('Wooden Sword', $pet, $pet->getName() . ' created this from some String and two Crooked Sticks.');
            $this->petService->gainExp($pet, 2, [ 'intelligence', 'dexterity', 'crafts',  'brawl' ]);
            $pet->increaseEsteem(2);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Wooden Sword.');
        }
        else
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'dexterity', 'crafts',  'brawl' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Wooden Sword, but couldn\'t quite figure it out.');
        }
    }

    private function createPaintedFishingRod(Pet $pet)
    {
        $pet->spendTime(\mt_rand(45, 90));
        $this->inventoryService->loseItem('Crooked Fishing Rod', $pet->getOwner(), 1);
        $this->inventoryService->loseItem('Yellow Dye', $pet->getOwner(), 1);
        $this->inventoryService->loseItem('Green Dye', $pet->getOwner(), 1);
        $this->inventoryService->petCollectsItem('Painted Fishing Rod', $pet, $pet->getName() . ' painted this, using Yellow and Green Dye.');
        $this->petService->gainExp($pet, 1, [ 'intelligence', 'dexterity', 'crafts' ]);
        $pet->increaseEsteem(1);
        return $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Painted Fishing Rod.');
    }
}