<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Service;

use App\Entity\MonthlyStoryAdventureStep;
use App\Entity\Pet;
use App\Entity\User;
use App\Entity\UserMonthlyStoryAdventureStepCompleted;
use App\Enum\LocationEnum;
use App\Enum\StoryAdventureTypeEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Functions\ArrayFunctions;
use App\Functions\DateFunctions;
use App\Model\ComputedPetSkills;
use App\Model\MonthlyStoryAdventure\AdventureResult;
use Doctrine\ORM\EntityManagerInterface;

class MonthlyStoryAdventureService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly EntityManagerInterface $em,
        private readonly IRandom $rng,
        private readonly HattierService $hattierService,
        private readonly Clock $clock
    )
    {
    }

    public function isStepCompleted(User $user, MonthlyStoryAdventureStep $step): bool
    {
        $completedStep = $this->em->getRepository(UserMonthlyStoryAdventureStepCompleted::class)->createQueryBuilder('c')
            ->select('COUNT(c.id) AS qty')
            ->andWhere('c.user=:user')
            ->andWhere('c.adventureStep=:adventureStep')
            ->setParameter('user', $user)
            ->setParameter('adventureStep', $step)
            ->getQuery()
            ->getSingleResult();

        return $completedStep['qty'] > 0;
    }

    public function isPreviousStepCompleted(User $user, MonthlyStoryAdventureStep $step): bool
    {
        $previousStep = $this->em->getRepository(MonthlyStoryAdventureStep::class)->createQueryBuilder('s')
            ->andWhere('s.step=:step')
            ->andWhere('s.adventure=:adventure')
            ->setParameter('step', $step->getPreviousStep())
            ->setParameter('adventure', $step->getAdventure()->getId())
            ->getQuery()
            ->getSingleResult()
        ;

        if(!$previousStep)
            throw new \Exception('Ben has made a terrible error: one of the story adventure steps could not be found. And it totally should have been.');

        return $this->isStepCompleted($user, $previousStep);
    }

    /**
     * @param Pet[] $pets
     */
    public function completeStep(User $user, MonthlyStoryAdventureStep $step, array $pets): string
    {
        $petSkills = array_map(fn(Pet $pet) => $pet->getComputedSkills(), $pets);

        $results = match ($step->getType())
        {
            StoryAdventureTypeEnum::CollectStone => $this->doCollectStone($user, $step, $petSkills),
            StoryAdventureTypeEnum::Gather => $this->doGather($user, $step, $petSkills),
            StoryAdventureTypeEnum::Hunt => $this->doHunt($user, $step, $petSkills),
            StoryAdventureTypeEnum::MineGold => $this->doMineGold($user, $step, $petSkills),
            StoryAdventureTypeEnum::RandomRecruit => $this->doRandomRecruit($user, $step, $petSkills),
            StoryAdventureTypeEnum::Story => $this->doStory($user, $step, $petSkills),
            StoryAdventureTypeEnum::TreasureHunt => $this->doTreasureHunt($user, $step, $petSkills),
            StoryAdventureTypeEnum::WanderingMonster => $this->doWanderingMonster($user, $step, $petSkills),
            default => throw new \Exception('Oh, dang: Ben forgot to implement this story adventure type! :('),
        };

        foreach($results->loot as $item)
            $this->inventoryService->receiveItem($item, $user, $user, $user->getName() . ' gave this to their pets during a game of ★Kindred.', LocationEnum::HOME);

        $this->markStepComplete($user, $step);

        return $results->text;
    }

    private function markStepComplete(User $user, MonthlyStoryAdventureStep $step): void
    {
        $completedStep = new UserMonthlyStoryAdventureStepCompleted($user, $step);

        $this->em->persist($completedStep);
    }

    private function getAdventureLoot(MonthlyStoryAdventureStep $step, array $pets, callable $petSkillFn, int $roll, string $freeLoot, array $lootTable): array
    {
        $loot = MonthlyStoryAdventureService::getFixedLoot($step);

        $loot[] = $freeLoot;

        $totalSkill = ArrayFunctions::sum($pets, $petSkillFn) + $roll - 10;

        $extraBits = floor($totalSkill / 5);

        for($i = 0; $i < $extraBits; $i++)
            $loot[] = $this->rng->rngNextFromArray($lootTable);

        return $loot;
    }

    private static function getFixedLoot(MonthlyStoryAdventureStep $step): array
    {
        if(!$step->getTreasure())
            return [];

        return match ($step->getTreasure())
        {
            'GoldChest' => [ 'Gold Chest' ],
            'BigBasicChest' => [ 'Handicrafts Supply Box' ],
            'CupOfLife' => [ 'Cup of Life' ],
            'TwilightChest' => [ 'Twilight Box' ],
            'TreasureMap' => [ 'Piece of Cetgueli\'s Map' ],
            'WrappedSword' => [ 'Wrapped Sword' ],
            'RubyChest' => [ 'Ruby Chest' ],
            'BoxOfOres' => [ 'Box of Ores' ],
            'CrystallizedQuint' => [ 'Quintessence' ],
            'Ship' => [ 'Paper Boat' ],
            'SkeletalRemains' => [ 'Dino Skull' ],
            'BlackFlag' => [ 'Black Flag' ],
            'ShalurianLighthouse' => [ 'Scroll of the Sea' ],
            'Rainbow' => [ 'Rainbow' ],
            'SmallMushrooms', 'LargeMushroom' => [ 'Toadstool' ],
            'PurpleGrass' => [ 'Quinacridone Magenta Dye' ],
            'EnormousTibia' => [ 'Stereotypical Bone' ],
            'FishBag' => [ 'Fish Bag' ],
            default => throw new \Exception("Bad Ben! He didn't code support for this adventure's treasure: \"{$step->getTreasure()}\"!"),
        };
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    private function describeAdventure(array $pets, MonthlyStoryAdventureStep $step, int $roll, array $loot): string
    {
        $text = $step->getNarrative() ?? '';

        if($roll > 0 && count($loot) > 0)
        {
            if($text != '') $text .= "\n\n";

            $text .= "(The pets roll for loot, and get a {$roll}! After adding their skill points, you award them " . ArrayFunctions::list_nice_sorted($loot) . '.)';
        }
        else if(count($loot) > 0)
        {
            if($text != '') $text .= "\n\n";

            $text .= "(You award your pets " . ArrayFunctions::list_nice_sorted($loot) . '!)';
        }

        if($step->getAura())
        {
            $auraText = $this->awardAura($pets, $step);

            if($text && $auraText) $text .= "\n\n" . $auraText;
        }

        return $text;
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    private function awardAura(array $pets, MonthlyStoryAdventureStep $step): ?string
    {
        /** @var ComputedPetSkills $petSkills */
        $petSkills = $this->rng->rngNextFromArray($pets);
        $pet = $petSkills->getPet();

        $unlocked = $this->hattierService->petMaybeUnlockAura(
            $pet,
            $step->getAura(),
            'While playing ★Kindred, %pet:' . $pet->getId() . '.name% was inspired to create a new hat style!',
            'While playing ★Kindred, %pet:' . $pet->getId() . '.name% was inspired to create a new hat style!',
            'While playing ★Kindred, %pet:' . $pet->getId() . '.name% was inspired to create a new hat style!'
        );

        if(!$unlocked)
            return null;

        if($pet->getOwner()->hasUnlockedFeature(UnlockableFeatureEnum::Hattier))
            return "(Inspired by the story, {$pet->getName()} created a new hat styling: {$step->getAura()->getName()}! Find it at the Hattier!)";
        else
            return "(Inspired by the story, {$pet->getName()} created a new hat styling?! What!? (The Hattier has been unlocked! Check it out in the menu!))";
    }


    /**
     * @param ComputedPetSkills[] $pets
     */
    private function doCollectStone(User $user, MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        $roll = $this->rng->rngNextInt(1, 20);

        $loot = $this->getAdventureLoot(
            $step,
            $pets,
            fn(ComputedPetSkills $pet) => $pet->getStrength()->getTotal() + $pet->getStamina()->getTotal() + $pet->getPerception()->getTotal() + $pet->getGatheringBonus()->getTotal(),
            $roll,
            'Rock',
            [
                'Rock', 'Rock',
                'Silica Grounds', 'Limestone',
                'Iron Ore', 'Gypsum'
            ],
        );

        $text = $this->describeAdventure($pets, $step, $roll, $loot);

        return new AdventureResult($text, $loot);
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    private function doGather(User $user, MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        $roll = $this->rng->rngNextInt(1, 20);

        $wheatOrCorn = DateFunctions::isCornMoon($this->clock->now) ? 'Corn' : 'Wheat';

        $loot = $this->getAdventureLoot(
            $step,
            $pets,
            fn(ComputedPetSkills $pet) => $pet->getDexterity()->getTotal() + $pet->getNature()->getTotal() + $pet->getGatheringBonus()->getTotal(),
            $roll,
            'Nature Box',
            [
                $wheatOrCorn, 'Rice', 'Orange', 'Naner', 'Red', 'Fluff', 'Crooked Stick', 'Coconut',
                'Blackberries', 'Blueberries', 'Sweet Beet'
            ],
        );

        $text = $this->describeAdventure($pets, $step, $roll, $loot);

        return new AdventureResult($text, $loot);
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    private function doHunt(User $user, MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        $roll = $this->rng->rngNextInt(1, 20);

        $loot = $this->getAdventureLoot(
            $step,
            $pets,
            fn(ComputedPetSkills $pet) => (int)ceil(($pet->getStrength()->getTotal() + $pet->getDexterity()->getTotal()) / 2) + $pet->getBrawl()->getTotal(),
            $roll,
            'Monster Box',
            [ 'Feathers', 'Fluff', 'Talon', 'Scales', 'Egg', 'Fish' ]
        );

        $text = $this->describeAdventure($pets, $step, $roll, $loot);

        return new AdventureResult($text, $loot);
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    private function doMineGold(User $user, MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        $roll = $this->rng->rngNextInt(1, 20);

        $loot = $this->getAdventureLoot(
            $step,
            $pets,
            fn(ComputedPetSkills $pet) => (int)ceil(($pet->getStrength()->getTotal() + $pet->getStamina()->getTotal()) / 2) + $pet->getNature()->getTotal() + $pet->getGatheringBonus()->getTotal(),
            $roll,
            'Gold Ore',
            [ 'Gold Ore', 'Gold Ore', 'Silver Ore', 'Iron Ore' ]
        );

        $text = $this->describeAdventure($pets, $step, $roll, $loot);

        return new AdventureResult($text, $loot);
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    private function doRandomRecruit(User $user, MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        $loot = MonthlyStoryAdventureService::getFixedLoot($step);

        $plushy = $this->rng->rngNextFromArray([
            // "Roy" Plushy is a special event item
            // Phoenix Plushy is a quest item
            'Bulbun Plushy',
            'Peacock Plushy',
            'Rainbow Dolphin Plushy',
            'Sneqo Plushy',
            'Catmouse Figurine',
            'Tentacat Figurine',
        ]);

        $recruitName = $this->rng->rngNextFromArray(self::StarKindredNames);

        $text = $step->getNarrative() ?? '';

        if($text != '') $text .= "\n\n";

        if(count($loot) > 0)
            $text .= "(You award your pets " . ArrayFunctions::list_nice_sorted($loot) . ", and a {$plushy} named {$recruitName} to represent the new recruit!)";
        else
            $text .= "(You award your pets a {$plushy} named {$recruitName} to represent the new recruit!)";

        $loot[] = $plushy;

        if($step->getAura())
        {
            $auraText = $this->awardAura($pets, $step);

            if($auraText) $text .= "\n\n" . $auraText;
        }

        return new AdventureResult($text, $loot);
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    private function doStory(User $user, MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        $loot = MonthlyStoryAdventureService::getFixedLoot($step);
        $text = $this->describeAdventure($pets, $step, 0, $loot);

        return new AdventureResult($text, $loot);
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    private function doTreasureHunt(User $user, MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        $loot = MonthlyStoryAdventureService::getFixedLoot($step);
        $text = $this->describeAdventure($pets, $step, 0, $loot);

        return new AdventureResult($text, $loot);
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    private function doWanderingMonster(User $user, MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        $roll = $this->rng->rngNextInt(1, 20);

        $loot = $this->getAdventureLoot(
            $step,
            $pets,
            fn(ComputedPetSkills $pet) => (int)ceil(($pet->getStrength()->getTotal() + $pet->getDexterity()->getTotal()) / 2) + $pet->getBrawl()->getTotal(),
            $roll,
            'Monster Box',
            [ 'Feathers', 'Fluff', 'Talon', 'Scales', 'Egg' ]
        );

        $text = $this->describeAdventure($pets, $step, $roll, $loot);

        return new AdventureResult($text, $loot);
    }

    // these names were copied from the StarKindred API code on 2022-08-28
    private const array StarKindredNames = [
        "Adaddu-Shalum", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Aho",
        "Akitu", // Babylonian New Year holiday
        "Albazi",
        "Amar-Sin", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Amata",
        "Amba-El", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Anshar", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Appan-Il",
        "Ardorach",
        "Arwia",
        "Ashlutum",
        "Asmaro",
        "Athra",
        "Balashi",
        "Barsawme",
        "Banunu",
        "Bel", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Beletsunu",
        "Belshazzar", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Belshimikka", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Bel-Shum-Usur", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Berosus",
        "Biridis", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Boram", // copilot-suggested
        "Caifas",
        "Celestia", // copilot-suggested
        "Cerasus-El", // copilot-suggested
        "Curus", // copilot-suggested
        "Dabra", // copilot-suggested
        "Dabra-Ea", // copilot-suggested w/ personal modification
        "Diimeritia",
        "Din-Turul", // I made this up; Din + -Turul suffix seen elsewhere
        "Dinu", // copilot-suggested
        "Doz", // copilot-suggested
        "Dwura",
        "Eannatum",
        "Ebru", // copilot-suggested
        "Ecna", // copilot-suggested
        "Eesho",
        "Edra", // copilot-suggested
        "Efra", // copilot-suggested
        "Ekka", // copilot-suggested
        "Ekran", // copilot-suggested
        "El", // copilot-suggested
        "Emmita",
        "Enheduana",
        "Enn", // copilot-suggested
        "Ettu",
        "Ezra", // copilot-suggested
        "Fara", // copilot-suggested
        "Fenra", // copilot-suggested
        "Fenra-Sin", // previous, plus a suffix I've seen before
        "Fenu", // copilot-suggested
        "Fenya", // copilot-suggested
        "Finna", // copilot-suggested
        "Firas", // copilot-suggested
        "Gabbara",
        "Gadatas",
        "Gemekaa",
        "Gewargis",
        "Goda", // copilot-suggested
        "Gomera", // copilot-suggested
        "Goram", // copilot-suggested
        "Gubaru",
        "Hammurabi",
        "Hann", // copilot-suggested
        "Hanuno",
        "Hara", // copilot-suggested
        "Hara-El", // copilot-suggested
        "Hebron", // copilot-suggested
        "Hemera", // copilot-suggested
        "Hesed", // copilot-suggested
        "Hisa", // copilot-suggested
        "Hod", // copilot-suggested
        "Hormuzd",
        "Hushmend", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Ia",
        "Iatum", // I made this one up
        "Ibbi-Adad",
        "Ibi", // extracted from a textsynth suggestion
        "Ibi-Atsi", // concatenated from two textsynth suggestions
        "Ibne", // copilot-suggested
        "Igal", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Igara", // copilot-suggested
        "Ili", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Ishep-Ana", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Ishme-Dagan", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Ishme-Ea",
        "Isimud", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Issavi",
        "Iwartas", // I made this one up
        "Izla",
        "Jabal", // copilot-suggested (and also biblical :P)
        "Jaram", // copilot-suggested
        "Jasen", // copilot-suggested
        "Jasen-El", // copilot-suggested
        "Jebe", // copilot-suggested
        "Jebre", // copilot-suggested
        "Job", // copilot-suggested (and also biblical :P)
        "Jod", // copilot-suggested
        "Jod-Aho", // copilot-suggested
        "Joshe", // copilot-suggested
        "Kabu", // copilot-suggested
        "Kalumtum",
        "Kan", // copilot-suggested
        "Khannah",
        "Khoshaba",
        "Ki", // copilot-suggested
        "Ko", // copilot-suggested
        "Ku-Aya",
        "Kugalis", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Laliya",
        "Lar-Aho", // copilot-suggested
        "Lilis",
        "Lilorach", // Lilis+ -orach suffix seen elsewhere
        "Lumiya", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Makara", // copilot-suggested
        "Malko",
        "Mazra", // copilot-suggested
        "Mekka", // copilot-suggested
        "Mylitta",
        "Nabu", // copilot-suggested
        "Nabua", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Nahrin",
        "Nahtum", // copilot-suggested
        "Nanshe-Kalum", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Naram-Sin",
        "Nazir",
        "Nebo", // copilot-suggested
        "Nebuchadnezzar",
        "Nektum", // copilot-suggested
        "Nesha", // copilot-suggested
        "Ninkurra", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Ninsun", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Nintu", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Nutesh",
        "Nur-Aya",
        "Odur", // copilot-suggested
        "Omarosa",
        "Oshana",
        "Pahtum", // copilot-suggested
        "Palkha",
        "Pardeeshur", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Puabi",
        "Puabu-Aya", // copilot-suggested
        "Rabbu",
        "Reshlutum", // I totally made this one up
        "Rimush",
        "Rishon", // copilot-suggested
        "Saba", // copilot-suggested
        "Samsi-Addu",
        "Samsu-Iluna", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Sarami-Zu", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Sarsurimutu", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Semiramis",
        "Shala-Kin", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Shalimoon",
        "Shamshi", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Shamsi-Adad", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Shu-Turul",
        "Sybella",
        "Tahira", // copilot-suggested
        "Takhana",
        "Tashlutum",
        "Teba", // copilot-suggested
        "Tebi", // copilot-suggested
        "Toram", // copilot-suggested
        "Tora", // copilot-suggested
        "Tu-Aya", // copilot-suggested
        "Ubbi-Adad", // copilot-suggested
        "Udun", // copilot-suggested
        "Ukubu",
        "Uru-Amurri", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Urukat", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Urukki", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Ushan", // copilot-suggested
        "Ushara", // copilot-suggested
        "Utu-Anu", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Uzuri", // from textsynth.com, with a prompt for Babylonian and Assyrian names
        "Waru", // I made this one up
        "Winhana", // I made this one up,
        "Yahatti-Il",
        "Yahtum", // copilot-suggested
        "Yonita",
        "Younan",
        "Zaia",
        "Zaiamoon", // I just combined Zaia + -moon from Shalimoon
        "Zaidu",
        "Zakiti",
        "Zakkum", // copilot-suggested
        "Zamir", // copilot-suggested
        "Zarai", // copilot-suggested
        "Zarath", // copilot-suggested
        "Zarath-Sin", // copilot-suggested
    ];
}