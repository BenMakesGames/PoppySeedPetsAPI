<?php
namespace App\Service\PetActivity\Crafting;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
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

        if(array_key_exists('Mermaid Egg', $quantities))
            $possibilities[] = [ $this, 'mermaidEggToQuint' ];

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

                if(array_key_exists('Talon', $quantities) && array_key_exists('Feathers', $quantities))
                    $possibilities[] = [ $this, 'createSummoningScroll' ];
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

            if(array_key_exists('Moon Pearl', $quantities) && array_key_exists('Blunderbuss', $quantities) && array_key_exists('Crooked Stick', $quantities))
                $possibilities[] = [ $this, 'createIridescentHandCannon' ];
        }

        return $possibilities;
    }

    public function createCrazyHotTorch(Pet $pet): PetActivityLog
    {
        $umbraCheck = \mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck === 1)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Stereotypical Torch', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Stereotypical Torch, but mishandled the Quintessence. The torch flared up, and ' . $pet->getName() . ' dropped the torch, breaking it :( Some Charcoal was left over, at least...', '');
            $this->inventoryService->petCollectsItem('Charcoal', $pet, $pet->getName() . ' accidentally created this while trying to enchant a Stereotypical Torch.', $activityLog);
            return $activityLog;
        }
        else if($umbraCheck === 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Stereotypical Torch, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 13)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Stereotypical Torch, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Stereotypical Torch', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' enchanted a Stereotypical Torch into a Crazy-hot Torch.', '');
            $this->inventoryService->petCollectsItem('Crazy-hot Torch', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    public function createBunchOfDice(Pet $pet): PetActivityLog
    {
        $umbraCheck = \mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a block of glowing dice, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 15)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a block of glowing dice, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $numberOfDice = mt_rand(3, 5);

            $this->petService->spendTime($pet, \mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Blackonite', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem($numberOfDice);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a block of glowing dice from a chunk of Blackonite, then gently tapped it to break the dice apart. ' . $numberOfDice . ' were made!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;

            for($x = 0; $x < $numberOfDice; $x++)
                $this->inventoryService->petCollectsItem(ArrayFunctions::pick_one([ 'Glowing Four-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Eight-sided Die' ]), $pet, $pet->getName() . ' got this from a block of glowing dice that they made.', $activityLog);

            return $activityLog;
        }
    }

    public function mermaidEggToQuint(Pet $pet): PetActivityLog
    {
        $umbraCheck = \mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getDexterity());

        if($umbraCheck <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Mermaid Egg', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to extract Quintessence from a Mermaid Egg, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 12)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to extract Quintessence from a Mermaid Egg, but almost screwed it all up. ' . $pet->getName() . ' decided to take a break from it for a bit...', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Mermaid Egg', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' successfully extracted Quintessence from a Mermaid Egg.', '');

            $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' extracted this from a Mermaid Egg.', $activityLog);

            return $activityLog;
        }
    }

    public function createMagicHourglass(Pet $pet): PetActivityLog
    {
        $umbraCheck = \mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant an Hourglass, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 15)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant an Hourglass, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Hourglass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' enchanted an Hourglass. It\'s _magic_ now!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Magic Hourglass', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    // note: THIS method should be private, but most methods here must be public!
    private function bindCeremonialTrident(Pet $pet, array $otherMaterials, string $makes): PetActivityLog
    {
        $umbraCheck = \mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Ceremonial Trident, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 20)
        {
            $this->petService->spendTime($pet, \mt_rand(46, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant an Ceremonial Trident, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            foreach($otherMaterials as $material)
                $this->inventoryService->loseItem($material, $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Ceremonial Trident', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' used a Ceremonial Trident to materialize the ' . $makes . '!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
            ;
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

    public function createIridescentHandCannon(Pet $pet): PetActivityLog
    {
        $umbraCheck = \mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());
        $craftsCheck = \mt_rand(1, 20 + $pet->getCrafts() + $pet->getDexterity() + $pet->getIntelligence());

        if($craftsCheck <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to extend a Blunderbuss but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
        }
        else if($umbraCheck <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Blunderbuss, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($craftsCheck < 10)
        {
            $this->petService->spendTime($pet, \mt_rand(46, 60), PetActivityStatEnum::CRAFT, false);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Blunderbuss, but didn\'t arrange the material components properly.', 'icons/activity-logs/confused');
        }
        else if($umbraCheck < 16)
        {
            $this->petService->spendTime($pet, \mt_rand(46, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Blunderbuss, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petService->spendTime($pet, \mt_rand(60, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Blunderbuss', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Moon Pearl', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 4, [ PetSkillEnum::UMBRA, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' made an Iridescent Hand Cannon by extending a Blunderbuss, and binding a Moon Pearl to it!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;
            $this->inventoryService->petCollectsItem('Iridescent Hand Cannon', $pet, $pet->getName() . ' bound a Moon Pearl to an extended Blunderbuss, making this!', $activityLog);
            return $activityLog;
        }

    }

    public function createWitchsBroom(Pet $pet): PetActivityLog
    {
        $umbraCheck = \mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

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
            $this->petService->spendTime($pet, \mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Witch\'s Broom, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Straw Broom', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Witch-hazel', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' enchanted a broom into a Witch\'s Broom!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;
            $this->inventoryService->petCollectsItem('Witch\'s Broom', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    public function createGenericScroll(Pet $pet, string $uniqueIngredient, string $scroll): PetActivityLog
    {
        $umbraCheck = \mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Paper', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a ' . $scroll . ', but accidentally tore the Paper in the process :(', 'icons/activity-logs/torn-to-bits');
        }
        else if($umbraCheck <= 3)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a ' . $scroll . ', but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 15)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a ' . $scroll . ', but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Paper', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem($uniqueIngredient, $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a ' . $scroll . '.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
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

    public function createSummoningScroll(Pet $pet): PetActivityLog
    {
        $umbraCheck = \mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Paper', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a Monster-summoning Scroll, but accidentally tore the Paper in the process :(', 'icons/activity-logs/torn-to-bits');
        }
        else if($umbraCheck <= 3)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a Monster-summoning Scroll, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 18)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a Monster-summoning Scroll, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Paper', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Talon', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Feathers', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Monster-summoning Scroll.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
            ;
            $this->inventoryService->petCollectsItem('Monster-summoning Scroll', $pet, $pet->getName() . ' bound this.', $activityLog);
            return $activityLog;
        }
    }
}