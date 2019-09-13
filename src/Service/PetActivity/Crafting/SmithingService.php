<?php
namespace App\Service\PetActivity\Crafting;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\PetService;
use App\Service\ResponseService;

class SmithingService
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

        if(array_key_exists('Silica Grounds', $quantities) && array_key_exists('Limestone', $quantities))
            $possibilities[] = [ $this, 'createGlass' ];

        if(array_key_exists('Glass', $quantities) && array_key_exists('Plastic', $quantities))
            $possibilities[] = [ $this, 'createFiberglass' ];

        if(mt_rand(1, 10 + $pet->getCrafts() + $pet->getIntelligence() + $pet->getSmithing()) >= 10)
        {
            if(array_key_exists('Iron Bar', $quantities))
            {
                $possibilities[] = [ $this, 'createIronKey' ];
                $possibilities[] = [ $this, 'createDumbbell' ];
                $possibilities[] = [ $this, 'createIronTongs' ];

                if(array_key_exists('Plastic', $quantities))
                {
                    if(array_key_exists('Yellow Dye', $quantities))
                        $possibilities[] = [ $this, 'createYellowScissors' ];

                    if(array_key_exists('Green Dye', $quantities))
                        $possibilities[] = [ $this, 'createGreenScissors' ];
                }

                // you have to be strong to use Dark Matter; pets with Eidetic Memory won't make the mistake of trying if they're not strong enough
                if(array_key_exists('Dark Matter', $quantities) && ($pet->getStrength() >= 4 || !$pet->hasMerit(MeritEnum::EIDETIC_MEMORY)))
                    $possibilities[] = [ $this, 'createHeavyHammer' ];
            }

            if(array_key_exists('Silver Bar', $quantities))
                $possibilities[] = [ $this, 'createSilverKey' ];

            if(array_key_exists('Gold Bar', $quantities))
                $possibilities[] = [ $this, 'createGoldKey' ];

            if(array_key_exists('Fiberglass', $quantities) && array_key_exists('Moon Pearl', $quantities) && array_key_exists('Gold Bar', $quantities))
                $possibilities[] = [ $this, 'createMoonhammer' ];

            if(array_key_exists('Silver Bar', $quantities) && array_key_exists('Glass', $quantities) && array_key_exists('Silica Grounds', $quantities))
                $possibilities[] = [ $this, 'createHourglass' ];
        }

        if(array_key_exists('Crooked Stick', $quantities) && array_key_exists('Iron Bar', $quantities))
            $possibilities[] = [ $this, 'createScythe' ];

        return $possibilities;
    }

    public function createHourglass(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + max($pet->getStamina(), $pet->getDexterity()) + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 3)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));

            $this->inventoryService->loseItem('Glass', $pet->getOwner(), 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::DEXTERITY, PetSkillEnum::CRAFTS, PetSkillEnum::STAMINA ]);
            $pet->increaseEsteem(-2);
            $pet->increaseSafety(-4);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried blowing Glass, but burnt themselves, and dropped the glass :(', 'icons/activity-logs/wounded');
        }
        else if($roll >= 15)
        {
            $this->petService->spendTime($pet, \mt_rand(45, 75));
            $this->inventoryService->loseItem('Iron Bar', $pet->getOwner(), 1);
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), 1);
            $this->inventoryService->loseItem('Silica Grounds', $pet->getOwner(), 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::DEXTERITY, PetSkillEnum::CRAFTS, PetSkillEnum::STAMINA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created an Hourglass.', '');
            $this->inventoryService->petCollectsItem('Hourglass', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::DEXTERITY, PetSkillEnum::CRAFTS, PetSkillEnum::STAMINA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a n Hourglass, but it\'s so detailed and fiddly! Ugh!', 'icons/activity-logs/confused');
        }
    }

    public function createGlass(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());
        if($roll <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->inventoryService->loseItem('Silica Grounds', $pet->getOwner(), 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-\mt_rand(2, 12));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Glass, but got burned while trying! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 13)
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75));
            $this->inventoryService->loseItem('Silica Grounds', $pet->getOwner(), 1);

            if(mt_rand(1, 3) === 1)
            {
                $this->inventoryService->loseItem('Limestone', $pet->getOwner(), 1);
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' melted Silica Grounds and Limestone into Glass.', 'items/mineral/silica-glass');
            }
            else
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' melted Silica Grounds and Limestone into Glass. (There\'s plenty of Limestone left over, though!)', 'items/mineral/silica-glass');

            $this->inventoryService->petCollectsItem('Glass', $pet, $pet->getName() . ' created this from Silica Grounds and Limestone.', $activityLog);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);

            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(45, 75));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Glass, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createFiberglass(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));

            if(mt_rand(1, 2) === 1)
                $this->inventoryService->loseItem('Plastic', $pet->getOwner(), 1);
            else
                $this->inventoryService->loseItem('Glass', $pet->getOwner(), 1);

            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-\mt_rand(2, 12));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Fiberglass, but got burned while trying! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 15)
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75));
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), 1);
            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), 1);

            $this->petService->gainExp($pet, 2, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' made Fiberglass from Glass and Plastic.', 'items/resource/fiberglass');
            $this->inventoryService->petCollectsItem('Fiberglass', $pet, $pet->getName() . ' created this from Glass and Plastic.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(45, 75));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Fiberglass, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createYellowScissors(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $pet->increaseEsteem(-1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);

            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('Plastic', $pet->getOwner(), 1);
                $pet->increaseSafety(-1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Yellow Scissors, but burnt the Plastic! :(', '');
            }
            else
            {
                $this->inventoryService->loseItem('Yellow Dye', $pet->getOwner(), 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Yellow Scissors, but accidentally spilled the Yellow Dye all over the place! :(', '');
            }
        }
        else if($roll >= 13)
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75));
            $this->inventoryService->loseItem('Iron Bar', $pet->getOwner(), 1);
            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), 1);
            $this->inventoryService->loseItem('Yellow Dye', $pet->getOwner(), 1);

            $this->petService->gainExp($pet, 2, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' made Yellow Scissors.', 'items/tool/scissors/yellow');
            $this->inventoryService->petCollectsItem('Yellow Scissors', $pet, $pet->getName() . ' created.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(45, 75));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make scissors, but getting the handle shape right is apparently frickin\' impossible >:(', 'icons/activity-logs/confused');
        }
    }

    public function createGreenScissors(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $pet->increaseEsteem(-1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);

            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('Plastic', $pet->getOwner(), 1);
                $pet->increaseSafety(-1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Green Scissors, but burnt the Plastic! :(', '');
            }
            else
            {
                $this->inventoryService->loseItem('Green Dye', $pet->getOwner(), 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Green Scissors, but accidentally spilled the Green Dye all over the place! :(', '');
            }
        }
        else if($roll >= 13)
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75));
            $this->inventoryService->loseItem('Iron Bar', $pet->getOwner(), 1);
            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), 1);
            $this->inventoryService->loseItem('Green Dye', $pet->getOwner(), 1);

            $this->petService->gainExp($pet, 2, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' made Green Scissors.', 'items/tool/scissors/green');
            $this->inventoryService->petCollectsItem('Green Scissors', $pet, $pet->getName() . ' created.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(45, 75));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make scissors, but getting the handle shape right is apparently frickin\' impossible >:(', 'icons/activity-logs/confused');
        }
    }

    public function createHeavyHammer(Pet $pet): PetActivityLog
    {
        if($pet->getStrength() < 4)
        {
            $this->petService->spendTime($pet, \mt_rand(10, 20));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::STRENGTH ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' wanted to make something out of Dark Matter, but the stuff\'s crazy-heavy, it turns out!', 'icons/activity-logs/confused');
        }
        else
        {
            $roll = \mt_rand(1, 20 + $pet->getIntelligence() + min($pet->getStrength(), $pet->getStamina()) + $pet->getCrafts() + $pet->getSmithing());

            if($roll <= 3)
            {
                $this->petService->spendTime($pet, \mt_rand(30, 60));
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STRENGTH, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);

                $pet
                    ->increaseSafety(-mt_rand(4, 8))
                    ->increaseEsteem(-mt_rand(1, 2))
                ;

                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Heavy Hammer, but dropped an Iron Bar on their toes!', '');
            }
            else if($roll <= 17)
            {
                $this->petService->spendTime($pet, \mt_rand(45, 75));
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STRENGTH, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Heavy Hammer, but the Dark Matter\'s was being especially difficult to work with! >:(', '');
            }
            else
            {
                $this->petService->spendTime($pet, \mt_rand(60, 75));
                $this->inventoryService->loseItem('Dark Matter', $pet->getOwner(), 1);
                $this->inventoryService->loseItem('Iron Bar', $pet->getOwner(), 1);

                $this->petService->gainExp($pet, 2, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STRENGTH, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem(3);

                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' made a Heavy Hammer from an Iron Bar and some Dark Matter!', 'items/tool/hammer/heavy');
                $this->inventoryService->petCollectsItem('Heavy Hammer', $pet, $pet->getName() . ' created this from an Iron Bar and some Dark Matter!', $activityLog);
                return $activityLog;
            }
        }
    }

    public function createScythe(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        $item = ArrayFunctions::pick_one([ 'Scythe', 'Garden Shovel' ]);

        if($roll <= 3)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));

            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a ' . $item . ', but broke the Crooked Stick! :(', 'icons/activity-logs/broke-stick');
        }
        else if($roll >= 13)
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75));
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), 1);
            $this->inventoryService->loseItem('Iron Bar', $pet->getOwner(), 1);

            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' made a ' . $item . ' from a Crooked Stick, and Iron Bar.', 'items/tool/scythe');
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' created this from a Crooked Stick, and Iron Bar.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(45, 75));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a ' . $item . ', but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createMoonhammer(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 3)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));

            $this->inventoryService->loseItem('Fiberglass', $pet->getOwner(), 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Moonhammer, but splintered the Fiberglass! :(', '');
        }
        else if($roll >= 20)
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75));
            $this->inventoryService->loseItem('Fiberglass', $pet->getOwner(), 1);
            $this->inventoryService->loseItem('Gold Bar', $pet->getOwner(), 1);
            $this->inventoryService->loseItem('Moon Pearl', $pet->getOwner(), 1);

            $this->petService->gainExp($pet, 3, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(3);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' made a Moonhammer!', 'items/tool/scythe');
            $this->inventoryService->petCollectsItem('Moonhammer', $pet, $pet->getName() . ' created this from Fiberglass, gold, and a Moon Pearl.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(45, 75));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make something out of Fiberglass, but wasn\'t happy with how it was turning out.', 'icons/activity-logs/confused');
        }
    }

    public function createIronBar(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());
        if($roll <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->inventoryService->loseItem('Iron Ore', $pet->getOwner(), 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-\mt_rand(2, 24));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to refine some Iron Ore, but got burned while trying! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 12)
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75));
            $this->inventoryService->loseItem('Iron Ore', $pet->getOwner(), 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' refined some Iron Ore into an Iron Bar.', 'items/element/iron-pure');
            $this->inventoryService->petCollectsItem('Iron Bar', $pet, $pet->getName() . ' refined this from Iron Ore.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(45, 75));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to refine Iron Ore into an Iron Bar, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createSilverBar(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());
        if($roll <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->inventoryService->loseItem('Silver Ore', $pet->getOwner(), 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-\mt_rand(2, 12));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to refine some Silver Ore, but got burned while trying! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 12)
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75));
            $this->inventoryService->loseItem('Silver Ore', $pet->getOwner(), 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' refined some Silver Ore into a Silver Bar.', 'items/element/silver-pure');
            $this->inventoryService->petCollectsItem('Silver Bar', $pet, $pet->getName() . ' refined this from Silver Ore.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(45, 75));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to refine Silver Ore into a Silver Bar, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createGoldBar(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());
        if($roll <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->inventoryService->loseItem('Gold Ore', $pet->getOwner(), 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-\mt_rand(2, 8));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to refine some Gold Ore, but got burned while trying! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 12)
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75));
            $this->inventoryService->loseItem('Gold Ore', $pet->getOwner(), 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' refined some Gold Ore into a Gold Bar.', 'items/element/gold-pure');
            $this->inventoryService->petCollectsItem('Gold Bar', $pet, $pet->getName() . ' refined this from Gold Ore.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(45, 75));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to refine Gold Ore into a Gold Bar, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createIronKey(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());
        if($roll <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->inventoryService->loseItem('Iron Bar', $pet->getOwner(), 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-\mt_rand(2, 24));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge an Iron Key, but got burned while trying! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 12)
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75));
            $this->inventoryService->loseItem('Iron Bar', $pet->getOwner(), 1);

            $keys = mt_rand(1, 5) === 1 ? 2 : 1;

            if($keys === 2)
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' forged *two* Iron Keys from an Iron Bar!', 'items/key/iron');
            else
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' forged an Iron Key from an Iron Bar.', 'items/key/iron');

            $this->inventoryService->petCollectsItem('Iron Key', $pet, $pet->getName() . ' forged this from an Iron Bar.', $activityLog);

            if($keys === 2)
                $this->inventoryService->petCollectsItem('Iron Key', $pet, $pet->getName() . ' forged this from an Iron Bar.', $activityLog);

            $this->petService->gainExp($pet, $keys, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem($keys === 1 ? 1 : 4);

            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(45, 75));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge an Iron Key from an Iron Bar, but couldn\'t get the shape right.', 'icons/activity-logs/confused');
        }
    }

    public function createDumbbell(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());
        if($roll <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->inventoryService->loseItem('Iron Bar', $pet->getOwner(), 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-\mt_rand(2, 24));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge a Dumbbell, but got burned while trying! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 12)
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75));
            $this->inventoryService->loseItem('Iron Bar', $pet->getOwner(), 1);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' forged a Dumbbell from an Iron Bar.', 'items/tool/dumbbell');

            $this->inventoryService->petCollectsItem('Dumbbell', $pet, $pet->getName() . ' forged this from an Iron Bar.', $activityLog);

            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);

            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(45, 75));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge a Dumbbell from an Iron Bar, but couldn\'t get the shape right.', 'icons/activity-logs/confused');
        }
    }

    public function createIronTongs(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());
        if($roll <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->inventoryService->loseItem('Iron Bar', $pet->getOwner(), 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-\mt_rand(2, 24));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge Iron Tongs, but got burned while trying! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 14)
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75));
            $this->inventoryService->loseItem('Iron Bar', $pet->getOwner(), 1);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' forged Iron Tongs from an Iron Bar.', 'items/tool/tongs');

            $this->inventoryService->petCollectsItem('Iron Tongs', $pet, $pet->getName() . ' forged this from an Iron Bar.', $activityLog);

            $this->petService->gainExp($pet, 2, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);

            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(45, 75));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge Iron Tongs from an Iron Bar, but couldn\'t get the shape right.', 'icons/activity-logs/confused');
        }
    }

    public function createSilverKey(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());
        $reRoll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->inventoryService->loseItem('Silver Bar', $pet->getOwner(), 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-\mt_rand(2, 12));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge a Silver Key, but got burned while trying! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 12)
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75));
            $this->inventoryService->loseItem('Silver Bar', $pet->getOwner(), 1);

            $keys = mt_rand(1, 7) === 1 ? 2 : 1;

            if($keys === 2)
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' forged *two* Silver Keys from a Silver Bar!', 'items/key/silver');
            else
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' forged a Silver Key from a Silver Bar.', 'items/key/silver');

            $this->inventoryService->petCollectsItem('Silver Key', $pet, $pet->getName() . ' forged this from a Silver Bar.', $activityLog);

            if($keys === 2)
                $this->inventoryService->petCollectsItem('Silver Key', $pet, $pet->getName() . ' forged this from a Silver Bar.', $activityLog);

            $this->petService->gainExp($pet, $keys, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem($keys === 1 ? 1 : 6);

            return $activityLog;
        }
        else if($reRoll >= 12)
        {
            $this->petService->spendTime($pet, \mt_rand(75, 90));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);

            $moneys = mt_rand(10, 20);
            $pet->getOwner()->increaseMoneys($moneys);
            $pet->increaseFood(-1);

            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge a Silver Key from a Silver Bar, but couldn\'t get the shape right, so just made ' . $moneys . ' Moneys worth of silver coins, instead.', 'icons/activity-logs/moneys');
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(45, 75));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge a Silver Key from a Silver Bar, but couldn\'t get the shape right.', 'icons/activity-logs/confused');
        }
    }

    public function createGoldKey(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());
        $reRoll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->inventoryService->loseItem('Gold Bar', $pet->getOwner(), 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-\mt_rand(2, 8));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge a Gold Key, but got burned while trying! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 12)
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75));
            $this->inventoryService->loseItem('Gold Bar', $pet->getOwner(), 1);

            $keys = mt_rand(1, 10) === 1 ? 2 : 1;

            if($keys === 2)
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' forged *two* Gold Keys from a Gold Bar!', 'items/key/gold');
            else
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' forged a Gold Key from a Gold Bar.', 'items/key/gold');

            $this->inventoryService->petCollectsItem('Gold Key', $pet, $pet->getName() . ' forged this from a Gold Bar.', $activityLog);

            if($keys === 2)
                $this->inventoryService->petCollectsItem('Gold Key', $pet, $pet->getName() . ' forged this from a Gold Bar.', $activityLog);

            $this->petService->gainExp($pet, $keys, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem($keys === 1 ? 1 : 10);

            return $activityLog;
        }
        else if($reRoll >= 12)
        {
            $this->petService->spendTime($pet, \mt_rand(75, 90));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);

            $moneys = mt_rand(20, 30);
            $pet->getOwner()->increaseMoneys($moneys);
            $pet->increaseFood(-1);

            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge a Gold Key from a Gold Bar, but couldn\'t get the shape right, so just made ' . $moneys . ' Moneys worth of gold coins, instead.', 'icons/activity-logs/moneys');
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(45, 75));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::STAMINA, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge a Gold Key from a Gold Bar, but couldn\'t get the shape right.', 'icons/activity-logs/confused');
        }
    }

}