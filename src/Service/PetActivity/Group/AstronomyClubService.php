<?php
namespace App\Service\PetActivity\Group;

use App\Entity\Enchantment;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetGroup;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Model\PetChanges;
use App\Repository\EnchantmentRepository;
use App\Service\HattierService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\PetRelationshipService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;

class AstronomyClubService
{
    public const ACTIVITY_ICON = 'groups/astronomy';

    private $petExperienceService;
    private $em;
    private $inventoryService;
    private $petRelationshipService;
    private IRandom $squirrel3;
    private EnchantmentRepository $enchantmentRepository;
    private HattierService $hattierService;

    public function __construct(
        PetExperienceService $petExperienceService, EntityManagerInterface $em, InventoryService $inventoryService,
        PetRelationshipService $petRelationshipService, Squirrel3 $squirrel3, HattierService $hattierService,
        EnchantmentRepository $enchantmentRepository
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->em = $em;
        $this->inventoryService = $inventoryService;
        $this->petRelationshipService = $petRelationshipService;
        $this->squirrel3 = $squirrel3;
        $this->hattierService = $hattierService;
        $this->enchantmentRepository = $enchantmentRepository;
    }

    private const DICTIONARY = [
        'prefix' => [
            'The First', 'The Last', 'The Second', 'The Final'
        ],
        'suffix' => [
            'Axiom', 'Law', 'Theory', 'Proposal', 'System', 'Model', 'Problem', 'Solution', 'Paradox', 'Thesis',
            'Hypothesis', 'Survey',
        ],
        'adjective' => [
            'Absolute', 'Alpha', 'Attracting', 'Beta', 'Big', 'Colliding', 'Copernican', 'Cosmic', 'Delta', 'Elementary', 'Expanding',
            'Finite', 'Galilean', 'Gamma-ray', 'Gravitational', 'Heavy', 'Infinite', 'Large-scale', 'Local', 'Microwave', 'Omega',
            'Plank', 'Quantized', 'Quantum', 'Radio', 'Really Big', 'Rotating', 'Small', 'Small-scale', 'Strongly-interacting',
            'Theta', 'Timey-wimey', 'Universal', 'Vibrating', 'Weakly-interacting', 'X-ray',
        ],
        'color' => [
            'Red', 'White', 'Black', 'Yellow', 'Dark', 'Light', 'Blue'
        ],
        'noun' => [
            'Phenomenon', 'Sun', 'Star', 'Galaxy', 'Nebula', 'Nova', 'Sphere', 'Expanse', 'Void', 'Shift', 'Particle',
            'Positron', 'Spin', 'String', 'Brane', 'Energy', 'Mass', 'Graviton', 'Field', 'Limit', 'Horizon', 'Plurality',
            'Symmetry', 'Matter', 'Force', 'Parsec', 'Quark', 'Inflation',
        ],
        'nouns' => [
            'Suns', 'Stars', 'Galaxies', 'Novas', 'Circles', 'Shifts', 'Particles', 'Metals', 'Strings', 'Branes',
            'Masses', 'Gravitons', 'Fields', 'Symmetries', 'Forces', 'Quarks', 'Super-clusters',
        ],
        'number' => [
            'Two', 'Three', 'Many', 'Multiple', '11', 'Infinite', 'Billions and Billions of', '42',
        ]
    ];

    private function getPatternParts(string $pattern): array
    {
        $parts = explode(' ', $pattern);
        $newParts = [];

        foreach($parts as $part)
        {
            if($part[strlen($part) - 1] === '?')
            {
                if($this->squirrel3->rngNextInt(1, 2) === 1)
                    $part = substr($part, 0, strlen($part) - 1);
                else
                    continue;
            }

            if(strpos($part, '/') !== false)
                $part = $this->squirrel3->rngNextFromArray(explode('/', $part));

            $newParts[] = $part;
        }

        return $newParts;
    }

    public function generateGroupName(): string
    {
        $pattern = $this->squirrel3->rngNextFromArray([
            '%prefix%/The %adjective%/%color% %noun% %suffix%',
            '%prefix%/The %number% %adjective%? %nouns% %suffix%',
            'The? %adjective% %color%? %noun%/%nouns%',
            'The? %number% %adjective%/%color% %nouns%',
            '%color%/%adjective% %nouns% and %color%/%adjective% %nouns%',
        ]);

        $parts = $this->getPatternParts($pattern);

        return $this->generateNameFromParts($parts, self::DICTIONARY, 60);
    }

    private function generateNameFromParts(array $parts, $dictionary, $maxLength): string
    {
        while(true)
        {
            $newParts = [];
            $chosenWords = [];

            foreach($parts as $part)
            {
                if($part[0] === '%' && $part[strlen($part) - 1] === '%')
                {
                    $wordType = substr($part, 1, strlen($part) - 2);
                    $chosenWord = $this->squirrel3->rngNextFromArray($dictionary[$wordType]);

                    $chosenWords[$wordType] = $chosenWord;

                    $newParts[] = $chosenWord;
                }
                else
                    $newParts[] = $part;
            }

            $name = str_replace(['_', ' ,'], [' ', ','], implode(' ', $newParts));

            if(strlen($name) <= $maxLength)
                return $name;

            $longestWord = ArrayFunctions::max($chosenWords, function($a) {
                return strlen($a);
            });

            $longestWordType = array_search($longestWord, $chosenWords);

            $dictionary[$longestWordType] = array_filter($dictionary[$longestWordType], fn($word) =>
                strlen($word) < strlen($longestWord)
            );
        }
    }

    public function meet(PetGroup $group)
    {
        $groupSize = count($group->getMembers());

        $skill = 0;
        $progress = $this->squirrel3->rngNextInt(20, 35 + $groupSize * 2);
        /** @var PetChanges[] $petChanges */ $petChanges = [];

        foreach($group->getMembers() as $pet)
        {
            $petWithSkills = $pet->getComputedSkills();
            $petChanges[$pet->getId()] = new PetChanges($pet);

            $roll = $this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getScience()->getTotal());

            $this->petExperienceService->gainExp($pet, max(1, floor($roll / 5)), [ PetSkillEnum::SCIENCE ]);

            $skill += $roll;
        }

        $group
            ->increaseProgress($progress)
            ->increaseSkillRollTotal($skill)
        ;

        if($group->getProgress() >= 100)
        {
            // we're expecting a very-maximum of 30 * 5 = 150. this will be exceptionally unlikely, however
            $reward = $this->squirrel3->rngNextInt(0, $group->getSkillRollTotal());

            $group
                ->clearProgress()
                ->increaseNumberOfProducts()
            ;

            $messageTemplate = '%pet% discovered %this% while exploring the cosmos with %group%!';

            if($reward < 10)
            {
                $item = 'Silica Grounds';
                $description = 'a cloud of space dust';

                if($this->squirrel3->rngNextInt(1, 20) === 1)
                    $description .= '-- I mean, Silica Grounds';
            }
            else if($reward < 20) // 10%
            {
                $item = 'NUL';
                $description = 'some old radio transmissions from Earth';
            }
            else if($reward < 25) // 5%
            {
                $item = 'Tentacle';
                $description = 'a tentacle';
                $messageTemplate = '%pet% discovered %this% while exploring the cosmos with %group%! (H-- how did that get there??)';
            }
            else if($reward < 40) // 15%
            {
                $item = 'Planetary Ring';
                $description = 'a Planetary Ring';
            }
            else if($reward < 45) // 5%
            {
                $item = 'Space Junk';
                $description = 'some Space Junk';
            }
            else if($reward < 50) // 5%
            {
                $item = 'Paper';
                $description = 'a Paper';
                $messageTemplate = '%group% wrote %this% based on their findings.';
            }
            else if($reward < 55) // 5%
            {
                $item = 'Everice';
                $description = 'a cube of Everice';
            }
            else if($reward < 65) // 10%
            {
                $item = 'Tiny Black Hole';
                $description = 'a Tiny Black Hole';
            }
            else
            {
                $item = 'Dark Matter';
                $description = 'some Dark Matter';
            }

            $astralEnchantment = $this->enchantmentRepository->findOneByName('Astral');

            foreach($group->getMembers() as $member)
            {
                $member->increaseEsteem($this->squirrel3->rngNextInt(3, 6));

                $activityLog = (new PetActivityLog())
                    ->setPet($member)
                    ->setEntry($this->formatMessage($messageTemplate, $member, $group, $description))
                    ->setIcon(self::ACTIVITY_ICON)
                    ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                    ->setChanges($petChanges[$member->getId()]->compare($member))
                ;

                $this->inventoryService->petCollectsItem($item, $member, $this->formatMessage($messageTemplate, $member, $group, 'this'), $activityLog);

                $this->maybeUnlockAuraAfterMakingDiscovery(
                    $member, $activityLog, $astralEnchantment, $description, $group->getName(),
                );

                $this->em->persist($activityLog);
            }
        }
        else
        {
            foreach($group->getMembers() as $member)
            {
                if($this->squirrel3->rngNextInt(1, 3) === 1)
                    $member->increaseLove($this->squirrel3->rngNextInt(2, 4));
                else
                    $member->increaseEsteem($this->squirrel3->rngNextInt(2, 4));

                $activityLog = (new PetActivityLog())
                    ->setPet($member)
                    ->setEntry($member->getName() . ' explored the cosmos with ' . $group->getName() . '.')
                    ->setIcon(self::ACTIVITY_ICON)
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM)
                    ->setChanges($petChanges[$member->getId()]->compare($member))
                ;

                $this->em->persist($activityLog);
            }
        }

        $this->petRelationshipService->groupGathering(
            $group->getMembers(),
            '%p1% and %p2% talked a little while exploring the cosmos together for ' . $group->getName() . '.',
            '%p1% and %p2% avoided talking as much as possible while exploring the cosmos together for ' . $group->getName() . '.',
            'Met during a ' . $group->getName() . ' meeting.',
            '%p1% met %p2% during a ' . $group->getName() . ' meeting.',
            100
        );

        $group->setLastMetOn();
    }

    private function maybeUnlockAuraAfterMakingDiscovery(Pet $pet, PetActivityLog $activityLog, Enchantment $enchantment, string $discoveredItemDescription, string $groupName)
    {
        if(!$pet->hasMerit(MeritEnum::BEHATTED) || $this->hattierService->userHasUnlocked($pet->getOwner(), $enchantment))
            return;

        $this->hattierService->unlockAuraDuringPetActivity(
            $pet,
            $activityLog,
            $enchantment,
            '(Wow! Space is incredible! You know what\'s also incredible? SPACE ON A HAT!)',
            '(Wow! Space is incredible!)',
            ActivityHelpers::PetName($pet) . ' was inspired by ' . $discoveredItemDescription . ' they found in space with ' . $groupName . '.'
        );
    }

    private function formatMessage(string $template, Pet $member, PetGroup $group, string $findings)
    {
        return str_replace(
            [
                '%pet%',
                '%group%',
                '%this%',
            ],
            [
                $member->getName(),
                $group->getName(),
                $findings
            ],
            $template
        );
    }
}
