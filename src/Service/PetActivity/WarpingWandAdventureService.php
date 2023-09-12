<?php

namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\GuildEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ActivityHelpers;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\PetQuestRepository;
use App\Repository\SpiceRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;

class WarpingWandAdventureService
{
    private IRandom $rng;
    private PetQuestRepository $petQuestRepository;
    private ResponseService $responseService;
    private InventoryService $inventoryService;
    private PetExperienceService $petExperienceService;
    private PetActivityLogTagRepository $petActivityLogTagRepository;
    private SpiceRepository $spiceRepository;
    private EntityManagerInterface $em;

    public function __construct(
        Squirrel3 $rng, PetQuestRepository $petQuestRepository, ResponseService $responseService,
        InventoryService $inventoryService, PetExperienceService $petExperienceService,
        PetActivityLogTagRepository $petActivityLogTagRepository, SpiceRepository $spiceRepository,
        EntityManagerInterface $em
    )
    {
        $this->rng = $rng;
        $this->petQuestRepository = $petQuestRepository;
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->petActivityLogTagRepository = $petActivityLogTagRepository;
        $this->spiceRepository = $spiceRepository;
        $this->em = $em;
    }

    public function adventure(ComputedPetSkills $petWithSkills)
    {
        $changes = new PetChanges($petWithSkills->getPet());

        $adventures = [
            [ $this, 'manoirDeChocolat' ],
            [ $this, 'walledGarden' ],
            [ $this, 'icyMoon' ],
            [ $this, 'ruinedSettlement' ],
            [ $this, 'kellis' ],
            [ $this, 'elfhame' ],
            [ $this, 'nothingness' ],
            [ $this, 'beanstalkCastle' ],
            [ $this, 'insideAWhale' ],
            [ $this, 'insideTheWand' ],
            [ $this, 'insideThemselves' ],
        ];

        /** @var PetActivityLog $activityLog */
        $activityLog = $this->rng->rngNextFromArray($adventures)($petWithSkills);

        $activityLog
            ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ->setChanges($changes->compare($petWithSkills->getPet()))
            ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Adventure!' ]))
        ;
    }

    private function getDescriptionStart(Pet $pet): string
    {
        return ActivityHelpers::PetName($pet) . '\'s ' . $pet->getTool()->getFullItemName() . ' suddenly vibrated, ';
    }

    public function manoirDeChocolat(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $petFurthestRoom = $this->petQuestRepository->findOrCreate($pet, 'Chocolate Mansion Furthest Room', 0);

        if($petFurthestRoom->getValue() === 0)
        {
            $description = $this->getDescriptionStart($pet) . 'and they found themselves standing on the roof of a building... a building made of chocolate! They grabbed one of the "shingles", and when they looked up again, they were back home!';
            $itemComment = $pet->getName() . ' broke this "shingle" off the roof of a chocolate building!';
        }
        else
        {
            $description = $this->getDescriptionStart($pet) . 'and they found themselves standing on the roof of Le Manoir de Chocolat! They grabbed one of the "shingles", and when they looked up again, they were back home!';
            $itemComment = $pet->getName() . ' broke this "shingle" off the roof of le Manoir de Chocolat.';
        }

        $activityLog = $this->responseService->createActivityLog($pet, $description, '')
            ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Le Manoir de Chocolat' ]))
        ;

        $this->inventoryService->petCollectsItem('Chocolate Bar', $pet, $itemComment, $activityLog);

        $this->petExperienceService->spendTime($pet, 1, PetActivityStatEnum::GATHER, true);

        return $activityLog;
    }

    public function walledGarden(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        // TODO: the Project-E one
    }

    public function kellis(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $loot = $this->rng->rngNextFromArray([
            [ 'spice' => 'Feseekh', 'item' => 'Fermented Fish' ],
            [ 'spice' => 'Nutmeg-laden', 'item' => 'Yellowy Lime' ],
            [ 'spice' => 'Fortified', 'item' => 'Wheat' ],
            [ 'spice' => null, 'item' => 'Carrot Preserves' ],
        ]);

        $spice = $loot['spice'] ? $this->spiceRepository->findOneByName($loot['spice']) : null;

        $activityLog = $this->responseService->createActivityLog(
            $pet,
            $this->getDescriptionStart($pet) . 'and they found themselves in a busy market it what looked like an ancient Egyptian city! They wandered around lost for a while, until a friendly merchant spotted them, and gave them ' . $loot['item'] . '! As soon as ' . ActivityHelpers::PetName($pet) . ' took it, they were returned home!',
            ''
        );

        $this->inventoryService->petCollectsEnhancedItem($loot['item'], null, $spice, $pet, $pet->getName() . ' wandered around what looked like an ancient Egyptian market for a while, until someone gave them this.', $activityLog);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::OTHER, null);

        return $activityLog;
    }

    public function icyMoon(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($petWithSkills->getStamina()->getTotal() < 5)
        {
            $pet->increaseSafety(-$this->rng->rngNextInt(6, 10));

            $activityLog = $this->responseService->createActivityLog(
                $pet,
                $this->getDescriptionStart($pet) . 'and they found themselves in an icy cave! The air was thin, and they were having trouble breathing... they closed their eyes, and when they opened them again, they were back home! (Weird! Scary!)',
                ''
            );

            $this->petExperienceService->spendTime($pet, 2, PetActivityStatEnum::OTHER, null);
        }
        else
        {
            $loot = $this->rng->rngNextFromArray([
                [ 'a piece of', 'Striped Microcline' ],
                [ 'some', 'Alien Tissue' ],
                [ 'a', 'Green Bow' ],
                [ 'some', 'Blackonite' ],
                [ 'a', 'Cool Mint Scepter' ],
                [ 'a', 'Frostbite' ],
                [ 'a', 'Rusted, Busted Mechanism' ],
                [ 'a', 'Horizon Mirror' ],
                [ 'an', 'Iridescent Hand Cannon' ],
                [ 'a', 'Gold Ring' ],
            ]);

            $activityLog = $this->responseService->createActivityLog(
                $pet,
                $this->getDescriptionStart($pet) . 'and they found themselves in an icy cave! The air was thin, but they calmed down, slowed their breathing, and started looking for a way out. As they were exploring, they stumbled upon ' . $loot[0] . ' ' . $loot[1] . ' lodged in the ice! After prying it free, they found themselves back home again!',
                ''
            );

            $this->inventoryService->petCollectsItem($loot[1], $pet, $pet->getName() . ' found this in an Icy Cave that their warping wand transported them to!', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(10, 15), PetActivityStatEnum::OTHER, null);
        }

        return $activityLog;
    }

    public function ruinedSettlement(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        // TODO: remains of 1725 russian settlement on the island (where rusted, busted mechanism is found)
    }

    public function elfhame(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        // TODO: def do something unique if the pet has a fairy godmother...
        // if the player doesn't have a fireplace yet, call that out!?
        // fairy swarm hat??!
    }

    public function nothingness(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $description = $this->getDescriptionStart($pet) . 'and they found themselves floating in a dark void! At first ' . ActivityHelpers::PetName($pet) . ' panics, ';

        if($pet->hasMerit(MeritEnum::BALANCE))
        {
            $description = 'but they quickly calm down, and begin to mediate... When they open their eyes a few minutes later, they\'re back home!';
            $time = $this->rng->rngNextInt(5, 10);
            $skill = PetSkillEnum::UMBRA;
            $pet->increaseEsteem($this->rng->rngNextInt(4, 8));
        }
        else if($pet->isInGuild(GuildEnum::INNER_SANCTUM))
        {
            $description = 'but then they remember their Inner Sanctum training, and begin to meditate... When they open their eyes a few minutes later, they\'re back home!';
            $time = $this->rng->rngNextInt(5, 10);
            $skill = PetSkillEnum::UMBRA;
            $pet->increaseEsteem($this->rng->rngNextInt(4, 8));
        }
        else if($pet->hasMerit(MeritEnum::FORCE_OF_WILL))
        {
            $description = 'but then they remember: like all sentient creatures, their mind - their will - is capable of bending the universe. After focusing on the space for a moment, they\'re returned home!';
            $time = $this->rng->rngNextInt(5, 10);
            $skill = PetSkillEnum::UMBRA;
            $pet->increaseEsteem($this->rng->rngNextInt(4, 8));
        }
        else if($pet->hasMerit(MeritEnum::FORCE_OF_NATURE))
        {
            $description = 'but then they remember that there\'s no restraint that can\'t be broken. It only takes moments to find a weak point in the void, allowing ' . ActivityHelpers::PetName($pet) . ' to break through, and return home!';
            $time = $this->rng->rngNextInt(5, 10);
            $skill = PetSkillEnum::BRAWL;
            $pet->increaseEsteem($this->rng->rngNextInt(4, 8));
        }
        else if($pet->hasMerit(MeritEnum::MODERATION))
        {
            $description = 'but they eventually calm down, and begin to mediate... When they open their eyes a few minutes later, they\'re back home!';
            $time = $this->rng->rngNextInt(20, 30);
            $skill = PetSkillEnum::UMBRA;
            $pet->increaseEsteem($this->rng->rngNextInt(4, 8));
        }
        else if($pet->hasMerit(MeritEnum::MIND_OVER_MATTER))
        {
            $description = 'but then they remember: like all sentient creatures, their mind - their will - is capable of bending the universe. After focusing on the space for a while, they\'re returned home!';
            $time = $this->rng->rngNextInt(20, 30);
            $skill = PetSkillEnum::UMBRA;
            $pet->increaseEsteem($this->rng->rngNextInt(4, 8));
        }
        else if($pet->hasMerit(MeritEnum::MATTER_OVER_MIND))
        {
            $description = 'but then they remember that there\'s no restraint that can\'t be broken. After searching for a while, they find a weak point in the void, and break through, returning home!';
            $time = $this->rng->rngNextInt(20, 30);
            $skill = PetSkillEnum::BRAWL;
            $pet->increaseEsteem($this->rng->rngNextInt(4, 8));
        }
        else
        {
            $description .= 'but after a while, they realize there\'s nothing they can do. They can\'t even tell if they\'re moving in this space! After nearly an hour passes, they\'re inexplicably returned home, wand still in hand.';
            $time = $this->rng->rngNextInt(45, 60);
            $pet->increaseSafety(-$this->rng->rngNextInt(4, 8));
            $skill = null;
        }

        $activityLog = $this->responseService->createActivityLog($pet, $description, '');

        $this->petExperienceService->spendTime($pet, $time, PetActivityStatEnum::OTHER, $skill !== null);

        if($skill)
            $this->petExperienceService->gainExp($pet, 2, [ $skill ], $activityLog);

        return $activityLog;
    }

    public function beanstalkCastle(ComputedPetSkills $petWithSkills): PetActivityLog
    {

    }

    public function insideAWhale(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $loot = $this->rng->rngNextFromArray([
            'Fish',
            'Filthy Cloth',
            'Seaweed',
            'Secret Seashell',
            'Crooked Stick',
            'Paper Boat',
            'Cast Net',
        ]);

        $activityLog = $this->responseService->createActivityLog(
            $pet,
            $this->getDescriptionStart($pet) . 'and they found themselves inside the belly of... a whale! And they were on the move! There was a lot of stuff inside; ' . ActivityHelpers::PetName($pet) . ' picked up a Rancid ' . $loot . ', and was suddenly back home!',
            ''
        );

        $rancid = $this->spiceRepository->findOneByName('Rancid');

        $this->inventoryService->petCollectsEnhancedItem($loot, null, $rancid, $pet, $pet->getName() . ' found this inside the belly of a whale!', $activityLog);
        $this->petExperienceService->spendTime($pet, 2, PetActivityStatEnum::OTHER, null);

        return $activityLog;
    }

    public function insideTheWand(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->em->remove($pet->getTool());

        $pet
            ->setTool(null)
            ->setScale(20)
            ->increaseSafety(-$this->rng->rngNextInt(4, 8))
        ;

        $activityLog = $this->responseService->createActivityLog(
            $pet,
            $this->getDescriptionStart($pet) . 'and they found themselves... _inside the wand?!_ They braced themselves as the wand - no longer held by anyone - tumbled to the ground, and shattered! ' . ActivityHelpers::PetName($pet) . ' crawled out of the debris, now super-tiny!',
            ''
        );

        $this->petExperienceService->spendTime($pet, 1, PetActivityStatEnum::OTHER, null);

        $this->inventoryService->receiveItem('Glass', $pet->getOwner(), $pet->getOwner(), 'The remains of a shattered warping wand...', LocationEnum::HOME);
        $this->inventoryService->receiveItem('Gravitational Waves', $pet->getOwner(), $pet->getOwner(), 'The remains of a shattered warping wand...', LocationEnum::HOME);
        $this->inventoryService->receiveItem('Crooked Stick', $pet->getOwner(), $pet->getOwner(), 'The remains of a shattered warping wand...', LocationEnum::HOME);

        return $activityLog;
    }

    public function insideThemselves(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        // TODO: heart dimension-style
    }
}