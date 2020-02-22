<?php
namespace App\Service\PetActivity\Crafting;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\EnumInvalidValueException;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;

class MagicBindingService
{
    private $inventoryService;
    private $responseService;
    private $petExperienceService;

    public function __construct(
        InventoryService $inventoryService, ResponseService $responseService, PetExperienceService $petExperienceService
    )
    {
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->petExperienceService = $petExperienceService;
    }

    public function getCraftingPossibilities(Pet $pet, array $quantities): array
    {
        $possibilities = [];

        if(array_key_exists('Mermaid Egg', $quantities))
            $possibilities[] = [ $this, 'mermaidEggToQuint' ];

        if(array_key_exists('Magic Smoke', $quantities))
            $possibilities[] = [ $this, 'magicSmokeToQuint' ];

        if(array_key_exists('Wings', $quantities))
        {
            if(array_key_exists('Talon', $quantities) && array_key_exists('Paper', $quantities))
                $possibilities[] = [ $this, 'createSummoningScroll' ];

            if(array_key_exists('Painted Dumbbell', $quantities) && array_key_exists('Glass', $quantities) && array_key_exists('Quinacridone Magenta Dye', $quantities))
                $possibilities[] = [ $this, 'createSmilingWand' ];
        }

        if(array_key_exists('Quintessence', $quantities))
        {
            if(array_key_exists('Gold Trifecta', $quantities))
                $possibilities[] = [ $this, 'createGoldTriskaidecta' ];

            if(array_key_exists('Stereotypical Torch', $quantities))
                $possibilities[] = [ $this, 'createCrazyHotTorch' ];

            if(array_key_exists('Hourglass', $quantities))
                $possibilities[] = [ $this, 'createMagicHourglass' ];

            if(array_key_exists('Straw Broom', $quantities) && array_key_exists('Witch-hazel', $quantities))
                $possibilities[] = [ $this, 'createWitchsBroom' ];

            if(array_key_exists('Blackonite', $quantities))
                $possibilities[] = [ $this, 'createBunchOfDice' ];

            if(array_key_exists('Gold Tuning Fork', $quantities))
                $possibilities[] = [ $this, 'createAstralTuningFork' ];

            if(array_key_exists('Mirror', $quantities) && array_key_exists('Crooked Stick', $quantities))
                $possibilities[] = [ $this, 'createMagicMirror' ];

            if(array_key_exists('Feathers', $quantities))
                $possibilities[] = [ $this, 'createWings' ];

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

            if(array_key_exists('Moon Pearl', $quantities) && array_key_exists('Blunderbuss', $quantities) && array_key_exists('Crooked Stick', $quantities))
                $possibilities[] = [ $this, 'createIridescentHandCannon' ];

            if(array_key_exists('Dark Scales', $quantities) && array_key_exists('Double Scythe', $quantities))
                $possibilities[] = [ $this, 'createNewMoon' ];

            if(array_key_exists('Farmer\'s Multi-tool', $quantities) && array_key_exists('Smallish Pumpkin', $quantities))
                $possibilities[] = [ $this, 'createGizubisShovel' ];

            if(array_key_exists('Rapier', $quantities))
            {
                if(array_key_exists('Sunflower', $quantities) && array_key_exists('Dark Matter', $quantities))
                    $possibilities[] = [ $this, 'createNightAndDay' ];
            }

            if(array_key_exists('Iron Sword', $quantities))
            {
                if(array_key_exists('Musical Scales', $quantities))
                    $possibilities[] = [ $this, 'createDancingSword' ];
            }
        }

        return $possibilities;
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createCrazyHotTorch(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck === 1)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Stereotypical Torch', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Stereotypical Torch, but mishandled the Quintessence. The torch flared up, and ' . $pet->getName() . ' dropped the torch, breaking it :( Some Charcoal was left over, at least...', '');
            $this->inventoryService->petCollectsItem('Charcoal', $pet, $pet->getName() . ' accidentally created this while trying to enchant a Stereotypical Torch.', $activityLog);
            return $activityLog;
        }
        else if($umbraCheck === 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Stereotypical Torch, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 13)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Stereotypical Torch, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Stereotypical Torch', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' enchanted a Stereotypical Torch into a Crazy-hot Torch.', '');
            $this->inventoryService->petCollectsItem('Crazy-hot Torch', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createBunchOfDice(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a block of glowing dice, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a block of glowing dice, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $numberOfDice = mt_rand(3, 5);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Blackonite', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem($numberOfDice);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a block of glowing dice from a chunk of Blackonite, then gently tapped it to break the dice apart. ' . $numberOfDice . ' were made!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;

            for($x = 0; $x < $numberOfDice; $x++)
                $this->inventoryService->petCollectsItem(ArrayFunctions::pick_one([ 'Glowing Four-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Eight-sided Die' ]), $pet, $pet->getName() . ' got this from a block of glowing dice that they made.', $activityLog);

            return $activityLog;
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function mermaidEggToQuint(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getDexterity());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Mermaid Egg', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to extract Quintessence from a Mermaid Egg, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 12)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to extract Quintessence from a Mermaid Egg, but almost screwed it all up. ' . $pet->getName() . ' decided to take a break from it for a bit...', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Mermaid Egg', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' successfully extracted Quintessence from a Mermaid Egg.', '');

            $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' extracted this from a Mermaid Egg.', $activityLog);

            return $activityLog;
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function magicSmokeToQuint(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + floor(($pet->getUmbra() + $pet->getScience()) / 2) + $pet->getIntelligence() + $pet->getDexterity());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Magic Smoke', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(-1);

            if(mt_rand(1, 2) === 1)
            {
                $pet->increasePsychedelic(mt_rand(1, 3));
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to extract Quintessence from Magic Smoke, but accidentally breathed the smoke in :(', '');
            }
            else
            {
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to extract Quintessence from Magic Smoke, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
            }
        }
        else if($umbraCheck < 12)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to extract Quintessence from Magic Smoke, but almost screwed it all up. ' . $pet->getName() . ' decided to take a break from it for a bit...', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Magic Smoke', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::SCIENCE ]);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' successfully extracted Quintessence from Magic Smoke.', '');

            $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' extracted this from Magic Smoke.', $activityLog);

            return $activityLog;
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createMagicHourglass(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant an Hourglass, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant an Hourglass, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Hourglass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' enchanted an Hourglass. It\'s _magic_ now!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Magic Hourglass', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    /**
     * note: THIS method should be private, but most methods here must be public!
     * @throws EnumInvalidValueException
     */
    private function bindCeremonialTrident(Pet $pet, array $otherMaterials, string $makes): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Ceremonial Trident, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 20)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(46, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant an Ceremonial Trident, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            foreach($otherMaterials as $material)
                $this->inventoryService->loseItem($material, $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Ceremonial Trident', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' used a Ceremonial Trident to materialize the ' . $makes . '!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
            ;
            $this->inventoryService->petCollectsItem($makes, $pet, $pet->getName() . ' made this real.', $activityLog);
            return $activityLog;
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createCeremonyOfShadows(Pet $pet): PetActivityLog
    {
        return $this->bindCeremonialTrident($pet, [ 'Blackonite' ], 'Ceremony of Shadows');
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createCeremonyOfFire(Pet $pet): PetActivityLog
    {
        return $this->bindCeremonialTrident($pet, [ 'Firestone' ], 'Ceremony of Fire');
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createCeremonyOfSandAndSea(Pet $pet): PetActivityLog
    {
        return $this->bindCeremonialTrident($pet, [ 'Seaweed', 'Sand Dollar' ], 'Ceremony of Sand and Sea');
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createIridescentHandCannon(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());
        $craftsCheck = mt_rand(1, 20 + $pet->getCrafts() + $pet->getDexterity() + $pet->getIntelligence());

        if($craftsCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to extend a Blunderbuss but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
        }
        else if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Blunderbuss, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($craftsCheck < 10)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(46, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Blunderbuss, but didn\'t arrange the material components properly.', 'icons/activity-logs/confused');
        }
        else if($umbraCheck < 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(46, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Blunderbuss, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Blunderbuss', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Moon Pearl', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::UMBRA, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' made an Iridescent Hand Cannon by extending a Blunderbuss, and binding a Moon Pearl to it!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;
            $this->inventoryService->petCollectsItem('Iridescent Hand Cannon', $pet, $pet->getName() . ' bound a Moon Pearl to an extended Blunderbuss, making this!', $activityLog);
            return $activityLog;
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createSmilingWand(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());
        $craftsCheck = mt_rand(1, 20 + $pet->getCrafts() + $pet->getDexterity() + $pet->getIntelligence());

        if($craftsCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to decorate a Painted Dumbbell but broke the Glass :(', 'icons/activity-logs/broke-glass');
        }
        else if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Wings', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Painted Dumbbell, but accidentally disenchanted the Wings :(', '');
            $this->inventoryService->petCollectsItem('Feathers', $pet, 'Left over after ' . $pet->getName() . ' accidentally disenchanted some Wings...', $activityLog);
            return $activityLog;
        }
        else if($craftsCheck < 10)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(46, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Painted Dumbbell, but didn\'t arrange the material components properly.', 'icons/activity-logs/confused');
        }
        else if($umbraCheck < 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(46, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Painted Dumbbell, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Wings', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Quinacridone Magenta Dye', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Painted Dumbbell', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::UMBRA, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' made a Smiling Wand by decorating & enchanting a Painted Dumbbell!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;
            $this->inventoryService->petCollectsItem('Smiling Wand', $pet, $pet->getName() . ' made this by decorating & enchanting a Painted Dumbbell!', $activityLog);
            return $activityLog;
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createGizubisShovel(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1)
            {
                $pet->increaseEsteem(-2);
                $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Farmer\'s Multi-tool, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
            }
            else
            {
                $pet->increaseEsteem(-2);
                $this->inventoryService->loseItem('Smallish Pumpkin', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Gizubi\'s Shovel, but split the Smallish Pumpkin wrong, ruining the spell :(', '');
            }
        }
        else if($umbraCheck < 20)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Farmer\'s Multi-tool, but kept messing up the spell.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Smallish Pumpkin', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Farmer\'s Multi-tool', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(4);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' enchanted a Farmer\'s Multi-tool with one of Gizubi\'s rituals.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
            ;
            $this->inventoryService->petCollectsItem('Gizubi\'s Shovel', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }

    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createNewMoon(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1)
            {
                $pet->increaseEsteem(-2);
                $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Double Scythe, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
            }
            else
            {
                $pet->increaseEsteem(-2);
                $this->inventoryService->loseItem('Dark Scales', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make New Moon, but cracked the Dark Scales :(', '');
            }
        }
        else if($umbraCheck < 20)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Double Scythe, but kept messing up the spell.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Dark Scales', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Double Scythe', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(4);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' enchanted a Double Scythe with Umbral magic...', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
            ;
            $this->inventoryService->petCollectsItem('New Moon', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createNightAndDay(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1)
            {
                $pet->increaseEsteem(-2);
                $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Rapier, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
            }
            else
            {
                $pet->increaseEsteem(-2);
                $this->inventoryService->loseItem('Sunflower', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Night and Day, but accidentally tore the Sunflower :(', '');
            }
        }
        else if($umbraCheck < 20)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Rapier, but kept messing up the spell.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Sunflower', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Dark Matter', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Rapier', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(6);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' bound Night and Day...', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
            ;
            $this->inventoryService->petCollectsItem('Night and Day', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createDancingSword(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + max($pet->getPerception(), ceil($pet->getMusic() / 4)));

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            $pet->increaseEsteem(-2);

            if(mt_rand(1, 2) === 1)
            {
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
                $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);

                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant an Iron Sword, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
            }
            else
            {
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::MUSIC ]);
                $this->inventoryService->loseItem('Musical Scales', $pet->getOwner(), LocationEnum::HOME, 1);

                for($i = 0; $i < 6; $i++)
                    $this->inventoryService->petCollectsItem('Music Note', $pet, $pet->getName() . ' accidentally broke apart Musical Scales into Music Notes, of which this is one.', null);

                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to bind a Dancing Sword, but accidentally dropped the Musical Scales, scattering Music Notes everywhere, and breaking one.', '');
            }
        }
        else if($umbraCheck < 18)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::MUSIC ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant an Iron Sword, but kept messing up the song.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Musical Scales', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Iron Sword', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA, PetSkillEnum::MUSIC ]);
            $pet
                ->increaseEsteem(4)
                ->increaseSafety(2)
            ;
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' bound a Dancing Sword...', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
            ;
            $this->inventoryService->petCollectsItem('Dancing Sword', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createWitchsBroom(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

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
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Witch\'s Broom, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Straw Broom', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Witch-hazel', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' enchanted a broom into a Witch\'s Broom!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;
            $this->inventoryService->petCollectsItem('Witch\'s Broom', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createMagicMirror(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1)
            {
                $pet->increaseEsteem(-1);
                $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Mirror, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
            }
            else
            {
                $pet->increaseEsteem(-1);
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Mirror, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
            }
        }
        else if($umbraCheck < 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Mirror, but couldn\'t figure out a good enchantment...', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Mirror', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' bound a Magic Mirror!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;
            $this->inventoryService->petCollectsItem('Magic Mirror', $pet, $pet->getName() . ' bound this.', $activityLog);
            return $activityLog;
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createAstralTuningFork(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            $pet->increaseEsteem(-1);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Gold Tuning Fork, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make an Astral Tuning Fork, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Gold Tuning Fork', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' enchanted a regular old Gold Tuning Fork; now it\'s an _Astral_ Tuning Fork!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;
            $this->inventoryService->petCollectsItem('Astral Tuning Fork', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createWings(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            $pet->increaseEsteem(-1);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to bind some Wings, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to bind some Wings, but kept mixing up the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Feathers', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' bound some Wings.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;
            $this->inventoryService->petCollectsItem('Wings', $pet, $pet->getName() . ' bound this.', $activityLog);
            return $activityLog;
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createGoldTriskaidecta(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            $pet->increaseEsteem(-1);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Gold Trifecta, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Gold Trsikaidecta, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Gold Triangle', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' imbued a Gold Trifecta with the power of the number 13!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;
            $this->inventoryService->petCollectsItem('Gold Triskaidecta', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createGenericScroll(Pet $pet, string $uniqueIngredient, string $scroll): PetActivityLog
    {
        $craftsCheck = mt_rand(1, 20 + $pet->getCrafts() + $pet->getDexterity() + $pet->getIntelligence());
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($craftsCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Paper', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a ' . $scroll . ', but accidentally tore the Paper in the process :(', 'icons/activity-logs/torn-to-bits');
        }
        else if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a ' . $scroll . ', but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a ' . $scroll . ', but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Paper', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem($uniqueIngredient, $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a ' . $scroll . '.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem($scroll, $pet, $pet->getName() . ' bound this.', $activityLog);
            return $activityLog;
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createFruitScroll(Pet $pet): PetActivityLog
    {
        return $this->createGenericScroll($pet, 'Red', 'Scroll of Fruit');
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createFarmerScroll(Pet $pet): PetActivityLog
    {
        return $this->createGenericScroll($pet, 'Wheat Flower', 'Farmer\'s Scroll');
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createFlowerScroll(Pet $pet): PetActivityLog
    {
        return $this->createGenericScroll($pet, 'Rice Flower', 'Scroll of Flowers');
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createSeaScroll(Pet $pet): PetActivityLog
    {
        return $this->createGenericScroll($pet, 'Seaweed', 'Scroll of the Sea');
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createSilverScroll(Pet $pet): PetActivityLog
    {
        return $this->createGenericScroll($pet, 'Silver Bar', 'Minor Scroll of Riches');
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createGoldScroll(Pet $pet): PetActivityLog
    {
        return $this->createGenericScroll($pet, 'Gold Bar', 'Major Scroll of Riches');
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createMusicScroll(Pet $pet): PetActivityLog
    {
        return $this->createGenericScroll($pet, 'Musical Scales', 'Scroll of Songs');
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createSummoningScroll(Pet $pet): PetActivityLog
    {
        $craftsCheck = mt_rand(1, 20 + $pet->getCrafts() + $pet->getDexterity() + $pet->getIntelligence());
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($craftsCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Paper', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a Monster-summoning Scroll, but accidentally tore the Paper in the process :(', 'icons/activity-logs/torn-to-bits');
        }
        else if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Wings', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a Monster-summoning Scroll, but accidentally tore the Wings back into Feathers :(', '');
            $this->inventoryService->petCollectsItem('Feathers', $pet, $pet->getName() . ' accidentally tore some Wings, leaving only these Feathers.', $activityLog);
            return $activityLog;
        }
        else if($umbraCheck < 18)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a Monster-summoning Scroll, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Wings', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Paper', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Talon', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Monster-summoning Scroll.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
            ;
            $this->inventoryService->petCollectsItem('Monster-summoning Scroll', $pet, $pet->getName() . ' bound this.', $activityLog);
            return $activityLog;
        }
    }
}
