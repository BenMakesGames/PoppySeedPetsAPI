<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Model\PetChanges;
use App\Repository\ItemRepository;
use App\Service\InventoryService;
use App\Service\PetActivity\Crafting\PlasticPrinterService;
use App\Service\PetActivity\Crafting\SmithingService;
use App\Service\PetActivity\Crafting\MagicBindingService;
use App\Service\PetService;
use App\Service\ResponseService;

class CraftingService
{
    private $responseService;
    private $inventoryService;
    private $petService;
    private $itemRepository;
    private $smithingService;
    private $magicBindingService;
    private $plasticPrinterService;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetService $petService,
        ItemRepository $itemRepository, SmithingService $smithingService, MagicBindingService $magicBindingService,
        PlasticPrinterService $plasticPrinterService
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petService = $petService;
        $this->itemRepository = $itemRepository;
        $this->smithingService = $smithingService;
        $this->magicBindingService = $magicBindingService;
        $this->plasticPrinterService = $plasticPrinterService;
    }

    public function getCraftingPossibilities(Pet $pet): array
    {
        $quantities = $this->itemRepository->getInventoryQuantities($pet->getOwner(), LocationEnum::HOME, 'name');

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
                $possibilities[] = [ $this, 'extractFromScales' ];

            if(array_key_exists('Talon', $quantities) && array_key_exists('Wooden Sword', $quantities))
                $possibilities[] = [ $this, 'createSnakebite' ];
        }

        if(array_key_exists('Crooked Stick', $quantities))
        {
            if(array_key_exists('String', $quantities))
            {
                $possibilities[] = [ $this, 'createCrookedFishingRod' ];

                if(array_key_exists('Talon', $quantities))
                    $possibilities[] = [ $this, 'createHuntingSpear' ];

                if(array_key_exists('Hunting Spear', $quantities))
                    $possibilities[] = [ $this, 'createVeryLongSpear' ];

                if(array_key_exists('Overly-long Spear', $quantities))
                    $possibilities[] = [ $this, 'createRidiculouslyLongSpear' ];

                if(array_key_exists('Wheat', $quantities) || array_key_exists('Rice', $quantities))
                    $possibilities[] = [ $this, 'createStrawBroom' ];
            }

            if(array_key_exists('White Cloth', $quantities))
                $possibilities[] = [ $this, 'createStereotypicalTorch' ];

            if(array_key_exists('Toadstool', $quantities) && array_key_exists('Quintessence', $quantities))
                $possibilities[] = [ $this, 'createChampignon' ];

            if(array_key_exists('String', $quantities))
                $possibilities[] = [ $this, 'createWoodenSword' ];

            if(array_key_exists('Glass', $quantities))
                $possibilities[] = [ $this, 'createRusticMagnifyingGlass' ];

            if(array_key_exists('Sweet Beet', $quantities) && array_key_exists('Glue', $quantities))
                $possibilities[] = [ $this, 'createSweetBeat' ];
        }

        if(array_key_exists('Glue', $quantities) && array_key_exists('White Cloth', $quantities))
            $possibilities[] = [ $this, 'createFabricMache' ];

        if(array_key_exists('String', $quantities) && array_key_exists('Glass', $quantities))
            $possibilities[] = [ $this, 'createGlassPendulum' ];

        if(array_key_exists('String', $quantities) && array_key_exists('Paper', $quantities) && array_key_exists('Silver Key', $quantities))
            $possibilities[] = [ $this, 'createBenjaminFranklin' ];

        if(array_key_exists('Hunting Spear', $quantities) && array_key_exists('Feathers', $quantities))
            $possibilities[] = [ $this, 'createDecoratedSpear' ];

        if(array_key_exists('Decorated Spear', $quantities) && array_key_exists('Quintessence', $quantities))
            $possibilities[] = [ $this, 'createVeilPiercer' ];

        if(array_key_exists('Crooked Fishing Rod', $quantities) && array_key_exists('Yellow Dye', $quantities) && array_key_exists('Green Dye', $quantities))
            $possibilities[] = [ $this, 'createPaintedFishingRod' ];

        if(array_key_exists('Plastic Idol', $quantities) && array_key_exists('Yellow Dye', $quantities))
            $possibilities[] = [ $this, 'createGoldIdol' ];

        if(array_key_exists('Fiberglass', $quantities))
            $possibilities[] = [ $this, 'createSimpleFiberglassItem' ];

        // pets won't try any smithing tasks if they don't feel sufficiently safe
        if($pet->getSafety() > 0)
            $possibilities = array_merge($possibilities, $this->smithingService->getCraftingPossibilities($pet, $quantities));

        if(array_key_exists('3D Printer', $quantities) && array_key_exists('Plastic', $quantities))
            $possibilities = array_merge($possibilities, $this->plasticPrinterService->getCraftingPossibilities($pet, $quantities));

        if(array_key_exists('Rusty Blunderbuss', $quantities) && ($pet->getSmithing() >= 5 || $pet->getCrafts() >= 5))
            $possibilities[] = [ $this, 'repairRustyBlunderbuss' ];

        if(array_key_exists('Rusty Rapier', $quantities) && ($pet->getSmithing() >= 5 || $pet->getCrafts() >= 5))
            $possibilities[] = [ $this, 'repairRustyRapier' ];

        if(mt_rand(1, 20 + $pet->getUmbra()) >= 15)
            $possibilities = array_merge($possibilities, $this->magicBindingService->getCraftingPossibilities($pet, $quantities));

        return $possibilities;
    }

    public function adventure(Pet $pet, array $possibilities): PetActivityLog
    {
        if(count($possibilities) === 0)
            throw new \InvalidArgumentException('possibilities must contain at least one item.');

        $method = ArrayFunctions::pick_one($possibilities);

        $activityLog = null;
        $changes = new PetChanges($pet);

        /** @var PetActivityLog $activityLog */
        $activityLog = $method($pet);

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        return $activityLog;
    }

    public function createSimpleFiberglassItem(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());

        $item = ArrayFunctions::pick_one([ 'Fiberglass Flute' ]);

        if($roll <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));

            $this->inventoryService->loseItem('Fiberglass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a ' . $item . ', but shattered the Fiberglass! :(', '');
        }
        else if($roll >= 14)
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75));
            $this->inventoryService->loseItem('Fiberglass', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' made a ' . $item . ' from Fiberglass.', '');
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' created this from Fiberglass.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(45, 75));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a ' . $item . ', but the Fiberglass wasn\'t cooperating.', 'icons/activity-logs/confused');
        }
    }

    private function createSweetBeat(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());
        if($roll <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->inventoryService->loseItem('Sweet Beet', $pet->getOwner(), LocationEnum::HOME, 1);
                $pet->increaseEsteem(-1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Sweet Beat, but the Glue got all over the beet, wasting both :(', '');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Sweet Beat, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
            }
        }
        else if($roll >= 15)
        {
            $this->petService->spendTime($pet, \mt_rand(45, 75));
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Sweet Beet', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Sweet Beat.', 'items/resource/string');
            $this->inventoryService->petCollectsItem('Sweet Beat', $pet, $pet->getName() . ' created this by gluing a Sweet Beet to a Stick. Because that makes sense.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to create a Sweet Beat, but wasn\'t able to make any meaningful progress.', 'icons/activity-logs/confused');
        }
    }

    private function createStringFromFluff(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());
        if($roll <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->inventoryService->loseItem('Fluff', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to spin some Fluff into String, but messed it up; the Fluff was wasted :(', '');
        }
        else if($roll >= 10)
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->inventoryService->loseItem('Fluff', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' spun some Fluff into String.', 'items/resource/string');
            $this->inventoryService->petCollectsItem('String', $pet, $pet->getName() . ' spun this from Fluff.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to spin some Fluff into String, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    private function createYellowDyeFromTeaLeaves(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getNature() + $pet->getCrafts());
        if($roll <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->inventoryService->loseItem('Tea Leaves', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to extract Yellow Dye from Tea Leaves, but messed it up, ruining the Tea Leaves :(', '');
        }
        else if($roll >= 12)
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75));
            $this->inventoryService->loseItem('Tea Leaves', $pet->getOwner(), LocationEnum::HOME, 2);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' extracted Yellow Dye from some Tea Leaves.', 'items/resource/dye-yellow');
            $this->inventoryService->petCollectsItem('Yellow Dye', $pet, $pet->getName() . ' extracted this from Tea Leaves.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' wanted to extract Yellow Dye from some Tea Leaves, but wasn\'t sure how to start.', 'icons/activity-logs/confused');
        }
    }

    private function extractFromScales(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getNature() + $pet->getCrafts());
        $itemName = mt_rand(1, 2) === 1 ? 'Green Dye' : 'Glue';

        if($roll <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->inventoryService->loseItem('Scales', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog(
                $pet,
                $pet->getName() . ' tried to extract ' . $itemName . ' from Scales, but messed it up, ruining the Scales :(',
                ''
            );
        }
        else if($roll >= 20)
        {
            $this->petService->spendTime($pet, \mt_rand(60, 90));
            $this->inventoryService->loseItem('Scales', $pet->getOwner(), LocationEnum::HOME, 2);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' extracted Green Dye _and_ Glue from some Scales!', 'items/animal/scales');
            $this->inventoryService->petCollectsItem('Green Dye', $pet, $pet->getName() . ' extracted this from Scales.', $activityLog);
            $this->inventoryService->petCollectsItem('Glue', $pet, $pet->getName() . ' extracted this from Scales.', $activityLog);
            return $activityLog;
        }
        else if($roll >= 12)
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75));
            $this->inventoryService->loseItem('Scales', $pet->getOwner(), LocationEnum::HOME, 2);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' extracted ' . $itemName . ' from some Scales.', 'items/animal/scales');
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' extracted this from Scales.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' wanted to extract ' . $itemName . ' from some Scales, but wasn\'t sure how to start.', 'icons/activity-logs/confused');
        }
    }

    private function createWhiteCloth(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());
        if($roll <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->inventoryService->loseItem('Fluff', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to spin some Fluff into String, but messed it up; a Fluff was wasted :(', '');
        }
        else if($roll >= 15)
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75));
            $this->inventoryService->loseItem('Fluff', $pet->getOwner(), LocationEnum::HOME, 2);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' weaved some Fluff into White Cloth.', '');
            $this->inventoryService->petCollectsItem('White Cloth', $pet, $pet->getName() . ' weaved this from Fluff.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to weave some Fluff into White Cloth, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    private function createCrookedFishingRod(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + \max($pet->getCrafts(), $pet->getNature()));

        if($roll <= 3)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            if(\mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Crooked Fishing Rod, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Crooked Fishing Rod, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');

            }
        }
        else if($roll >= 12)
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Crooked Fishing Rod.', '');
            $this->inventoryService->petCollectsItem('Crooked Fishing Rod', $pet, $pet->getName() . ' created this from String and a Crooked Stick.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Crooked Fishing Rod, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    private function createStereotypicalTorch(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());

        if($roll <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(15, 30));
            if(\mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem(-1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Stereotypical Torch, but accidentally tore the White Cloth into useless shapes :(', 'icons/activity-logs/torn-to-bits');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Stereotypical Torch, but accidentally split the Crooked Stick :(', 'icons/activity-logs/broke-stick');
            }
        }
        else if($roll >= 8)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 45));
            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Stereotypical Torch.', '');
            $this->inventoryService->petCollectsItem('Stereotypical Torch', $pet, $pet->getName() . ' created this from White Cloth and a Crooked Stick.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(15, 45));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Stereotypical Torch, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    private function createFabricMache(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());

        if($roll <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-2);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make some Fabric Mâché, but messed it all up, ruining the White Cloth and wasting the Glue :(', 'icons/activity-logs/torn-to-bits');
        }
        else if($roll >= 14)
        {
            $possibleItems = [ 'Fabric Mâché Basket' ];
            $item = ArrayFunctions::pick_one($possibleItems);

            $this->petService->spendTime($pet, \mt_rand(60, 75));
            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a ' . $item . '.', '');
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' created this from White Cloth and Glue.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make some Fabric Mâché, but couldn\'t come up with a good pattern.', 'icons/activity-logs/confused');
        }
    }

    private function createChampignon(Pet $pet): PetActivityLog
    {
        $quintessenceHandling = \mt_rand(1, 10 + $pet->getUmbra());

        if($quintessenceHandling <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Champignon, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }

        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());

        if($roll <= 3)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));

            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Champignon, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
        }
        else if($roll >= 15)
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Toadstool', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS,  PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Champignon.', '');
            $this->inventoryService->petCollectsItem('Champignon', $pet, $pet->getName() . ' created this from a Crooked Stick, Toadstool, and bit of Quintessence.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS,  PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Champignon, but couldn\'t quite figure it out.', 'icons/activity-logs/confused');
        }
    }

    private function createWoodenSword(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + \max($pet->getCrafts(), $pet->getBrawl()));

        if($roll <= 3)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            if(\mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Wooden Sword, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Wooden Sword, but broke the Crooked Stick :( Like, more than the one time needed.', 'icons/activity-logs/broke-stick');

            }
        }
        else if($roll >= 12)
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Wooden Sword.', '');
            $this->inventoryService->petCollectsItem('Wooden Sword', $pet, $pet->getName() . ' created this from some String and a Crooked Stick.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Wooden Sword, but couldn\'t quite figure it out.', 'icons/activity-logs/confused');
        }
    }

    private function createGlassPendulum(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());

        if($roll <= 3)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));

            if(mt_rand(1, 20 + $pet->getDexterity() + $pet->getStamina()) >= 18)
            {
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem(2);
                $pet->increaseSafety(-4);

                if(mt_rand(1, 4) === 1)
                {
                    $pet->increaseEsteem(2);
                    return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to cut a piece of glass, but cut themselves, instead! :( They managed to save the glass, though! ' . $pet->getName() . ' is kind of proud of that.', 'icons/activity-logs/wounded');
                }
                else
                    return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to cut a piece of glass, but cut themselves, instead! :( They managed to save the glass, though!', 'icons/activity-logs/wounded');
            }
            else
            {
                $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem(-2);
                $pet->increaseSafety(-4);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to cut a piece of glass, but cut themselves, instead, and dropped the glass :(', 'icons/activity-logs/wounded');
            }
        }
        else if($roll >= 15)
        {
            $this->petService->spendTime($pet, \mt_rand(45, 75));
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' cut some Glass to look like a gem, and made a Glass Pendulum.', '');
            $this->inventoryService->petCollectsItem('Glass Pendulum', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $pet->increaseSafety(-1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Glass Pendulum, but almost cut themselves on the glass, and gave up.', 'icons/activity-logs/confused');
        }
    }

    private function createRusticMagnifyingGlass(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());

        if($roll <= 3)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));

            if(mt_rand(1, 20 + $pet->getDexterity() + $pet->getStamina()) >= 18)
            {
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem(2);
                $pet->increaseSafety(-4);

                if(mt_rand(1, 4) === 1)
                {
                    $pet->increaseEsteem(2);
                    return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to make a lens from a piece of glass, and cut themselves! :( They managed to save the glass, though! ' . $pet->getName() . ' is kind of proud of that.', 'icons/activity-logs/wounded');
                }
                else
                    return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to make a lens from a piece of glass, and cut themselves! :( They managed to save the glass, though!', 'icons/activity-logs/wounded');
            }
            else
            {
                $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem(-2);
                $pet->increaseSafety(-4);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to make a lens from a piece of glass, but cut themselves, and dropped the glass :(', 'icons/activity-logs/wounded');
            }
        }
        else if($roll >= 13)
        {
            $this->petService->spendTime($pet, \mt_rand(45, 75));
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a "Rustic" Magnifying Glass.', '');
            $this->inventoryService->petCollectsItem('"Rustic" Magnifying Glass', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a magnifying glass, but almost broke the glass, and gave up.', 'icons/activity-logs/confused');
        }
    }

    private function createBenjaminFranklin(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());

        if($roll <= 3)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            if(\mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a kite, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $this->inventoryService->loseItem('Paper', $pet->getOwner(), LocationEnum::HOME, 1);
                $pet->increaseEsteem(-2);
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a kite, but tore the Paper :(', '');
            }
        }
        else if($roll >= 17)
        {
            $this->petService->spendTime($pet, \mt_rand(45, 75));
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Paper', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Silver Key', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created Benjamin Franklin. (A kite, not the person.)', '');
            $this->inventoryService->petCollectsItem('Benjamin Franklin', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a kite, but couldn\'t come up with a good design...', 'icons/activity-logs/confused');
        }
    }

    private function createHuntingSpear(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + \max($pet->getCrafts(), $pet->getBrawl()));

        if($roll <= 3)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            if(\mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Hunting Spear, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Hunting Spear, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
            }
        }
        else if($roll >= 13)
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Talon', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Hunting Spear.', '');
            $this->inventoryService->petCollectsItem('Hunting Spear', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Hunting Spear, but couldn\'t quite figure it out.', 'icons/activity-logs/confused');
        }
    }

    private function createStrawBroom(Pet $pet): PetActivityLog
    {
        $craftsCheck = \mt_rand(1, 20 + $pet->getCrafts() + $pet->getDexterity() + $pet->getIntelligence());

        if($craftsCheck <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            if(\mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a broom, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a broom, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
            }
        }
        else if($craftsCheck >= 13)
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);

            if($this->inventoryService->loseItem('Rice', $pet->getOwner(), LocationEnum::HOME, 1) === 0)
                $this->inventoryService->loseItem('Wheat', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Straw Broom.', '');
            $this->inventoryService->petCollectsItem('Straw Broom', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a broom, but couldn\'t quite figure it out.', 'icons/activity-logs/confused');
        }
    }

    private function createVeryLongSpear(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + \max($pet->getCrafts(), $pet->getBrawl()));

        if($roll <= 3)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            if(\mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to extend a Hunting Spear, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to extend a Hunting Spear, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
            }
        }
        else if($roll >= 14)
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Hunting Spear', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created an Overly-long Spear.', '');
            $this->inventoryService->petCollectsItem('Overly-long Spear', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to extend a Hunting Spear, but couldn\'t quite figure it out.', 'icons/activity-logs/confused');
        }
    }

    private function createRidiculouslyLongSpear(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + \max($pet->getCrafts(), $pet->getBrawl()));

        if($roll <= 3)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            if(\mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to extend an Overly-long Spear, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to extend an Overly-long Spear, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
            }
        }
        else if($roll >= 16)
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Overly-long Spear', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a - like - CRAZY-long Spear. It\'s really rather silly.', '');
            $this->inventoryService->petCollectsItem('This is Getting Ridiculous', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' considered extending an Overly-long Spear, but then thought that maybe that was going a bit overboard.', 'icons/activity-logs/confused');
        }
    }

    private function createDecoratedSpear(Pet $pet)
    {
        $roll = \mt_rand(1, 20 + $pet->getDexterity() + $pet->getCrafts());

        if($roll >= 12)
        {
            $this->petService->spendTime($pet, \mt_rand(15, 30));
            $this->inventoryService->loseItem('Feathers', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Hunting Spear', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Decorated Spear.', '');
            $this->inventoryService->petCollectsItem('Decorated Spear', $pet, $pet->getName() . ' decorated a Hunting Spear with Feathers to make this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(15, 30));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to decorate a Hunting Spear with Feathers, but couldn\'t get the look just right.', 'icons/activity-logs/confused');
        }
    }

    private function createSnakebite(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getDexterity() + $pet->getCrafts());

        if($roll <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-2);
            $pet->increaseSafety(-4);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to craft Snakebite, but cut themself on a Talon!', 'icons/activity-logs/wounded');
        }
        else if($roll >= 15)
        {
            $this->petService->spendTime($pet, \mt_rand(45, 75));
            $this->inventoryService->loseItem('Talon', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Scales', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Wooden Sword', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Snakebite sword.', '');
            $this->inventoryService->petCollectsItem('Snakebite', $pet, $pet->getName() . ' made this by improving a Wooden Sword.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to improve a Wooden Sword into Snakebite, but failed.', 'icons/activity-logs/confused');
        }
    }

    private function createVeilPiercer(Pet $pet): PetActivityLog
    {
        $umbraCheck = \mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence());
        $craftsCheck = \mt_rand(1, 20 + $pet->getCrafts() + $pet->getDexterity() + $pet->getIntelligence());

        if($umbraCheck <= 3)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchanted a Decorated Spear, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($craftsCheck < 15)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried enchant a Decorated Spear, but couldn\'t get an enchantment to stick.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Decorated Spear', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' enchanted a Decorated Spear to be a Veil-piercer.', '');
            $this->inventoryService->petCollectsItem('Veil-piercer', $pet, $pet->getName() . ' made this by enchanting a Decorated Spear.', $activityLog);
            return $activityLog;
        }
    }

    private function createPaintedFishingRod(Pet $pet): PetActivityLog
    {
        $this->petService->spendTime($pet, \mt_rand(45, 90));
        $this->inventoryService->loseItem('Crooked Fishing Rod', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->inventoryService->loseItem('Yellow Dye', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->inventoryService->loseItem('Green Dye', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
        $pet->increaseEsteem(1);
        $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Painted Fishing Rod.', '');
        $this->inventoryService->petCollectsItem('Painted Fishing Rod', $pet, $pet->getName() . ' painted this, using Yellow and Green Dye.', $activityLog);
        return $activityLog;
    }

    private function createGoldIdol(Pet $pet): PetActivityLog
    {
        $this->petService->spendTime($pet, \mt_rand(45, 90));
        $this->inventoryService->loseItem('Plastic Idol', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->inventoryService->loseItem('Yellow Dye', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
        $pet->increaseEsteem(1);
        $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a "Gold" Idol.', '');
        $this->inventoryService->petCollectsItem('"Gold" Idol', $pet, $pet->getName() . ' painted this, using Yellow Dye.', $activityLog);
        return $activityLog;
    }

    private function repairRustyBlunderbuss(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());

        if($roll === 1 && !$pet->hasMerit(MeritEnum::LUCKY))
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->inventoryService->loseItem('Rusty Blunderbuss', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-4);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to repair a Rusty Blunderbuss, but accidentally broke it beyond repair :(', '');
        }
        else if($roll >= 18)
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75));
            $this->inventoryService->loseItem('Rusty Blunderbuss', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' repaired a Rusty Blunderbuss. It\'s WAY less rusty now!', '');
            $this->inventoryService->petCollectsItem('Blunderbuss', $pet, $pet->getName() . ' repaired this Rusty Blunderbuss.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' spent a while trying to repair a Rusty Blunderbuss, but wasn\'t able to make any progress.', 'icons/activity-logs/confused');
        }
    }

    private function repairRustyRapier(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + \max($pet->getCrafts(), $pet->getBrawl()));

        if($roll === 1 && !$pet->hasMerit(MeritEnum::LUCKY))
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->inventoryService->loseItem('Rusty Rapier', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            $pet->increaseEsteem(-4);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to repair a Rusty Rapier, but accidentally broke it beyond repair :(', '');
        }
        else if($roll >= 14)
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75));
            $this->inventoryService->loseItem('Rusty Rapier', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' repaired a Rusty Rapier. It\'s WAY less rusty now!', '');
            $this->inventoryService->petCollectsItem('Rapier', $pet, $pet->getName() . ' repaired this Rapier.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' spent a while trying to repair a Rusty Rapier, but wasn\'t able to make any progress.', 'icons/activity-logs/confused');
        }
    }
}