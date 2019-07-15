<?php
namespace App\Service\PetActivity\Crafting;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Service\InventoryService;
use App\Service\PetService;
use App\Service\ResponseService;

class RefiningService
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

        if(array_key_exists('Iron Ore', $quantities))
            $possibilities[] = [ $this, 'createIronBar' ];

        if(array_key_exists('Silver Ore', $quantities))
            $possibilities[] = [ $this, 'createSilverBar' ];

        if(array_key_exists('Gold Ore', $quantities))
            $possibilities[] = [ $this, 'createGoldBar' ];

        if(array_key_exists('Sand', $quantities) && array_key_exists('Limestone', $quantities))
            $possibilities[] = [ $this, 'createGlass' ];

        if(array_key_exists('Glass', $quantities) && array_key_exists('Plastic', $quantities))
            $possibilities[] = [ $this, 'createFiberglass' ];

        if(mt_rand(1, 10 + $pet->getCrafts() + $pet->getIntelligence() + $pet->getSmithing()) >= 10)
        {
            if(array_key_exists('Iron Bar', $quantities))
                $possibilities[] = [ $this, 'createIronKey' ];

            if(array_key_exists('Silver Bar', $quantities))
                $possibilities[] = [ $this, 'createSilverKey' ];

            if(array_key_exists('Gold Bar', $quantities))
                $possibilities[] = [ $this, 'createGoldKey' ];
        }

        return $possibilities;
    }

    public function createGlass(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());
        if($roll <= 2)
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->inventoryService->loseItem('Sand', $pet->getOwner(), 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-\mt_rand(2, 12));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'stamina', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Glass, but got burned while trying! :(');
        }
        else if($roll >= 13)
        {
            $pet->spendTime(\mt_rand(60, 75));
            $this->inventoryService->loseItem('Sand', $pet->getOwner(), 1);

            if(mt_rand(1, 3) === 1)
            {
                $this->inventoryService->loseItem('Limestone', $pet->getOwner(), 1);
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' melted Sand and Limestone into Glass.');
            }
            else
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' melted Sand and Limestone into Glass. (There\'s plenty of Limestone left over, though!)');

            $this->inventoryService->petCollectsItem('Glass', $pet, $pet->getName() . ' created this from Sand and Limestone.');
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'stamina', 'crafts' ]);
            $pet->increaseEsteem(1);

            return $activityLog;
        }
        else
        {
            $pet->spendTime(\mt_rand(45, 75));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'stamina', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Glass, but couldn\'t figure it out.');
        }
    }

    public function createFiberglass(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 2)
        {
            $pet->spendTime(\mt_rand(30, 60));

            if(mt_rand(1, 2) === 1)
                $this->inventoryService->loseItem('Plastic', $pet->getOwner(), 1);
            else
                $this->inventoryService->loseItem('Glass', $pet->getOwner(), 1);

            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-\mt_rand(2, 12));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'stamina', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Fiberglass, but got burned while trying! :(');
        }
        else if($roll >= 15)
        {
            $pet->spendTime(\mt_rand(60, 75));
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), 1);
            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), 1);

            $this->inventoryService->petCollectsItem('Fiberglass', $pet, $pet->getName() . ' created this from Glass and Plastic.');
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'stamina', 'crafts' ]);
            $pet->increaseEsteem(1);

            return $this->responseService->createActivityLog($pet, $pet->getName() . ' made Fiberglass from Glass and Plastic.');
        }
        else
        {
            $pet->spendTime(\mt_rand(45, 75));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'stamina', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Fiberglass, but couldn\'t figure it out.');
        }
    }

    public function createIronBar(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());
        if($roll <= 2)
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->inventoryService->loseItem('Iron Ore', $pet->getOwner(), 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-\mt_rand(2, 24));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'stamina', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to refine some Iron Ore, but got burned while trying! :(');
        }
        else if($roll >= 12)
        {
            $pet->spendTime(\mt_rand(60, 75));
            $this->inventoryService->loseItem('Iron Ore', $pet->getOwner(), 1);
            $this->inventoryService->petCollectsItem('Iron Bar', $pet, $pet->getName() . ' refined this from Iron Ore.');
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'stamina', 'crafts' ]);
            $pet->increaseEsteem(1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' refined some Iron Ore into an Iron Bar.');
        }
        else
        {
            $pet->spendTime(\mt_rand(45, 75));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'stamina', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to refine Iron Ore into an Iron Bar, but couldn\'t figure it out.');
        }
    }

    public function createSilverBar(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());
        if($roll <= 2)
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->inventoryService->loseItem('Silver Ore', $pet->getOwner(), 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-\mt_rand(2, 12));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'stamina', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to refine some Silver Ore, but got burned while trying! :(');
        }
        else if($roll >= 12)
        {
            $pet->spendTime(\mt_rand(60, 75));
            $this->inventoryService->loseItem('Silver Ore', $pet->getOwner(), 1);
            $this->inventoryService->petCollectsItem('Silver Bar', $pet, $pet->getName() . ' refined this from Silver Ore.');
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'stamina', 'crafts' ]);
            $pet->increaseEsteem(1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' refined some Silver Ore into a Silver Bar.');
        }
        else
        {
            $pet->spendTime(\mt_rand(45, 75));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'stamina', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to refine Silver Ore into a Silver Bar, but couldn\'t figure it out.');
        }
    }

    public function createGoldBar(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());
        if($roll <= 2)
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->inventoryService->loseItem('Gold Ore', $pet->getOwner(), 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-\mt_rand(2, 8));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'stamina', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to refine some Gold Ore, but got burned while trying! :(');
        }
        else if($roll >= 12)
        {
            $pet->spendTime(\mt_rand(60, 75));
            $this->inventoryService->loseItem('Gold Ore', $pet->getOwner(), 1);
            $this->inventoryService->petCollectsItem('Gold Bar', $pet, $pet->getName() . ' refined this from Gold Ore.');
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'stamina', 'crafts' ]);
            $pet->increaseEsteem(1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' refined some Gold Ore into a Gold Bar.');
        }
        else
        {
            $pet->spendTime(\mt_rand(45, 75));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'stamina', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to refine Gold Ore into a Gold Bar, but couldn\'t figure it out.');
        }
    }

    public function createIronKey(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());
        if($roll <= 2)
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->inventoryService->loseItem('Iron Bar', $pet->getOwner(), 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-\mt_rand(2, 24));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'stamina', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge an Iron Key, but got burned while trying! :(');
        }
        else if($roll >= 12)
        {
            $pet->spendTime(\mt_rand(60, 75));
            $this->inventoryService->loseItem('Iron Bar', $pet->getOwner(), 1);

            $keys = mt_rand(1, 5) === 1 ? 2 : 1;

            $this->inventoryService->petCollectsItem('Iron Key', $pet, $pet->getName() . ' forged this from an Iron Bar.');

            if($keys === 2)
                $this->inventoryService->petCollectsItem('Iron Key', $pet, $pet->getName() . ' forged this from an Iron Bar.');

            $this->petService->gainExp($pet, $keys, [ 'intelligence', 'stamina', 'crafts' ]);
            $pet->increaseEsteem($keys === 1 ? 1 : 4);

            if($keys === 2)
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' forged *two* Iron Keys from an Iron Bar!');
            else
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' forged an Iron Key from an Iron Bar.');
        }
        else
        {
            $pet->spendTime(\mt_rand(45, 75));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'stamina', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge an Iron Key from an Iron Bar, but couldn\'t get the shape right.');
        }
    }

    public function createSilverKey(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());
        $reRoll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 2)
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->inventoryService->loseItem('Silver Bar', $pet->getOwner(), 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-\mt_rand(2, 12));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'stamina', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge a Silver Key, but got burned while trying! :(');
        }
        else if($roll >= 12)
        {
            $pet->spendTime(\mt_rand(60, 75));
            $this->inventoryService->loseItem('Silver Bar', $pet->getOwner(), 1);

            $keys = mt_rand(1, 7) === 1 ? 2 : 1;

            $this->inventoryService->petCollectsItem('Silver Key', $pet, $pet->getName() . ' forged this from a Silver Bar.');

            if($keys === 2)
                $this->inventoryService->petCollectsItem('Silver Key', $pet, $pet->getName() . ' forged this from a Silver Bar.');

            $this->petService->gainExp($pet, $keys, [ 'intelligence', 'stamina', 'crafts' ]);
            $pet->increaseEsteem($keys === 1 ? 1 : 6);

            if($keys === 2)
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' forged *two* Silver Keys from a Silver Bar!');
            else
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' forged a Silver Key from a Silver Bar.');
        }
        else if($reRoll >= 12)
        {
            $pet->spendTime(\mt_rand(75, 90));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'stamina', 'crafts' ]);

            $moneys = mt_rand(10, 20);
            $pet->getOwner()->increaseMoneys($moneys);
            $pet->increaseFood(-1);

            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge a Silver Key from a Silver Bar, but couldn\'t get the shape right, so just made ' . $moneys . ' Moneys worth of silver coins, instead.');
        }
        else
        {
            $pet->spendTime(\mt_rand(45, 75));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'stamina', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge a Silver Key from a Silver Bar, but couldn\'t get the shape right.');
        }
    }

    public function createGoldKey(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());
        $reRoll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 2)
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->inventoryService->loseItem('Gold Bar', $pet->getOwner(), 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-\mt_rand(2, 8));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'stamina', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge a Gold Key, but got burned while trying! :(');
        }
        else if($roll >= 12)
        {
            $pet->spendTime(\mt_rand(60, 75));
            $this->inventoryService->loseItem('Gold Bar', $pet->getOwner(), 1);

            $keys = mt_rand(1, 10) === 1 ? 2 : 1;

            $this->inventoryService->petCollectsItem('Gold Key', $pet, $pet->getName() . ' forged this from a Gold Bar.');

            if($keys === 2)
                $this->inventoryService->petCollectsItem('Gold Key', $pet, $pet->getName() . ' forged this from a Gold Bar.');

            $this->petService->gainExp($pet, $keys, [ 'intelligence', 'stamina', 'crafts' ]);
            $pet->increaseEsteem($keys === 1 ? 1 : 10);

            if($keys === 2)
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' forged *two* Gold Keys from a Gold Bar!');
            else
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' forged a Gold Key from a Gold Bar.');
        }
        else if($reRoll >= 12)
        {
            $pet->spendTime(\mt_rand(75, 90));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'stamina', 'crafts' ]);

            $moneys = mt_rand(20, 30);
            $pet->getOwner()->increaseMoneys($moneys);
            $pet->increaseFood(-1);

            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge a Gold Key from a Gold Bar, but couldn\'t get the shape right, so just made ' . $moneys . ' Moneys worth of gold coins, instead.');
        }
        else
        {
            $pet->spendTime(\mt_rand(45, 75));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'stamina', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge a Gold Key from a Gold Bar, but couldn\'t get the shape right.');
        }
    }

}