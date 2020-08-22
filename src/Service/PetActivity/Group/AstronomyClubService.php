<?php
namespace App\Service\PetActivity\Group;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetGroup;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\PetRelationshipService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

class AstronomyClubService
{
    private $petExperienceService;
    private $em;
    private $inventoryService;
    private $responseService;
    private $petRelationshipService;

    public function __construct(
        PetExperienceService $petExperienceService, EntityManagerInterface $em, InventoryService $inventoryService,
        ResponseService $responseService, PetRelationshipService $petRelationshipService
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->em = $em;
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->petRelationshipService = $petRelationshipService;
    }

    private const PREFIX_LIST = [
        'The First', 'The Last', 'The Second', 'The Final',
    ];

    private const SUFFIX_LIST = [
        'Law', 'Theory', 'Proposal', 'Axiom', 'System', 'Model', 'Problem', 'Solution', 'Paradox', 'Thesis',
        'Hypothesis'
    ];

    private const ADJECTIVE_LIST = [
        'Big', 'Really Big', 'Alpha', 'Beta', 'Theta', 'Omega', 'Delta', 'Heavy', 'Strongly-interacting',
        'Vibrating', 'Elementary', 'Rotating', 'Plank', 'Absolute', 'Small', 'Weakly-interacting', 'Large-scale',
        'Small-scale', 'Microwave', 'Radio', 'X-ray', 'Gamma-ray',
    ];

    private const COLOR_LIST = [
        'Red', 'White', 'Black', 'Yellow', 'Dark', 'Light',
    ];

    private const SINGULAR_NOUN_LIST = [
        'Phenomenon', 'Sun', 'Star', 'Galaxy', 'Nebula', 'Nova', 'Sphere', 'Expanse', 'Void', 'Shift', 'Particle',
        'Positron', 'Spin', 'String', 'Brane', 'Energy', 'Mass', 'Graviton', 'Field', 'Limit', 'Horizon', 'Plurality',
        'Symmetry', 'Matter', 'Force', 'Parsec',
    ];

    private const PLURAL_NOUN_LIST = [
        'Suns', 'Stars', 'Galaxies', 'Novas', 'Circles', 'Shifts', 'Particles', 'Metals', 'Strings', 'Branes',
        'Masses', 'Gravitons', 'Fields', 'Symmetries', 'Forces',
    ];

    private const NUMBER_LIST = [
        'Two', 'Three', 'Many', 'Multiple', '11', 'Infinite', 'Billions and Billions of', '42',
    ];

    public function generateGroupName(): string
    {
        $pattern = ArrayFunctions::pick_one([
            '%prefix%/The %adjective%/%color% %noun% %suffix%',
            '%prefix%/The %number% %adjective%? %nouns% %suffix%',
            'The? %adjective% %color%? %noun%/%nouns%',
            'The? %number% %adjective%/%color% %nouns%',
            '%color%/%adjective% %nouns% and %color%/%adjective% %nouns%',
        ]);

        $parts = explode(' ', $pattern);
        $newParts = [];
        foreach($parts as $part)
        {
            if($part[strlen($part) - 1] === '?')
            {
                if(mt_rand(1, 2) === 1)
                    $part = substr($part, 0, strlen($part) - 1);
                else
                    continue;
            }

            if(strpos($part, '/') !== false)
                $part = ArrayFunctions::pick_one(explode('/', $part));

            if($part === '%noun%')
                $newParts[] = ArrayFunctions::pick_one(self::SINGULAR_NOUN_LIST);
            else if($part === '%nouns%')
                $newParts[] = ArrayFunctions::pick_one(self::PLURAL_NOUN_LIST);
            else if($part === '%adjective%')
                $newParts[] = ArrayFunctions::pick_one(self::ADJECTIVE_LIST);
            else if($part === '%color%')
                $newParts[] = ArrayFunctions::pick_one(self::COLOR_LIST);
            else if($part === '%number%')
                $newParts[] = ArrayFunctions::pick_one(self::NUMBER_LIST);
            else if($part === '%suffix%')
                $newParts[] = ArrayFunctions::pick_one(self::SUFFIX_LIST);
            else if($part === '%prefix%')
                $newParts[] = ArrayFunctions::pick_one(self::PREFIX_LIST);
            else
                $newParts[] = $part;
        }

        return str_replace(['_', ' ,'], [' ', ','], implode(' ', $newParts));
    }

    public function meet(PetGroup $group)
    {
        $groupSize = count($group->getMembers());

        $skill = 0;
        $progress = mt_rand(20, 35 + $groupSize * 2);
        /** @var PetChanges[] $petChanges */ $petChanges = [];

        foreach($group->getMembers() as $pet)
        {
            $petChanges[$pet->getId()] = new PetChanges($pet);

            $roll = mt_rand(1, 10 + $pet->getScience());

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
            $reward = mt_rand(0, $group->getSkillRollTotal());

            $group
                ->clearProgress()
                ->increaseNumberOfProducts()
            ;

            $messageTemplate = '%pet% discovered %this% while exploring the cosmos with %group%!';

            if($reward < 10)
            {
                $item = 'Silica Grounds';
                $description = 'a cloud of space dust';

                if(mt_rand(1, 20) === 1)
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
                $item = 'Moon Pearl';
                $description = 'Moon Pearls';
            }
            else if($reward < 50) // 5%
            {
                $item = 'Paper';
                $description = 'Paper';
                $messageTemplate = '%group% wrote this Paper based on their findings.';
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

            foreach($group->getMembers() as $member)
            {
                if($item !== null)
                {
                    $member->increaseEsteem(mt_rand(3, 6));

                    $activityLog = (new PetActivityLog())
                        ->setPet($member)
                        ->setEntry($this->formatMessage($messageTemplate, $member, $group, $description))
                        ->setIcon('')
                        ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                        ->setChanges($petChanges[$member->getId()]->compare($member))
                    ;

                    $this->inventoryService->petCollectsItem($item, $member, $this->formatMessage($messageTemplate, $member, $group, 'this'), $activityLog);
                }
                else
                {
                    if(mt_rand(1, 3) === 1)
                        $member->increaseLove(mt_rand(2, 4));
                    else
                        $member->increaseEsteem(mt_rand(2, 4));

                    $activityLog = (new PetActivityLog())
                        ->setPet($member)
                        ->setEntry($group->getName() . ' surveyed a portion of the sky, but didn\'t find anything interesting there...')
                        ->setIcon('')
                        ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM)
                        ->setChanges($petChanges[$member->getId()]->compare($member))
                    ;
                }

                $this->em->persist($activityLog);
            }
        }
        else
        {
            foreach($group->getMembers() as $member)
            {
                if(mt_rand(1, 3) === 1)
                    $member->increaseLove(mt_rand(2, 4));
                else
                    $member->increaseEsteem(mt_rand(2, 4));

                $activityLog = (new PetActivityLog())
                    ->setPet($member)
                    ->setEntry($member->getName() . ' explored the cosmos with ' . $group->getName() . '.')
                    ->setIcon('')
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
