<?php
namespace App\Service\PetActivity;

use App\Entity\PetActivityLog;
use App\Enum\DistractionLocationEnum;
use App\Enum\EnumInvalidValueException;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetSkillEnum;
use App\Model\ComputedPetSkills;
use App\Repository\PetActivityLogTagRepository;
use App\Service\FieldGuideService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\WeatherService;
use Doctrine\ORM\EntityManagerInterface;

class GatheringDistractionService
{
    private IRandom $rng;
    private PetExperienceService $petExperienceService;
    private ResponseService $responseService;
    private FieldGuideService $fieldGuideService;
    private EntityManagerInterface $em;

    public function __construct(
        Squirrel3 $squirrel3, PetExperienceService $petExperienceService, ResponseService $responseService,
        FieldGuideService $fieldGuideService, EntityManagerInterface $em
    )
    {
        $this->rng = $squirrel3;
        $this->petExperienceService = $petExperienceService;
        $this->responseService = $responseService;
        $this->fieldGuideService = $fieldGuideService;
        $this->em = $em;
    }

    public function adventure(ComputedPetSkills $petWithSkills, string $location, string $whileDoingDescription): PetActivityLog
    {
        if(!DistractionLocationEnum::isAValue($location))
            throw new EnumInvalidValueException(DistractionLocationEnum::class, $location);

        $pet = $petWithSkills->getPet();

        $distraction = $this->rng->rngNextFromArray($this->getPossibleDistractions($petWithSkills, $location));

        $description = 'While %pet:' . $pet->getId() . '.name% was ' . $whileDoingDescription . ', ' . $distraction['description'];

        if($location === DistractionLocationEnum::VOLCANO)
        {
            $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Île Volcan', '%pet:' . $pet->getId() . '.name% went out ' . $location . '...');
        }

        $activityLog = $this->responseService->createActivityLog($pet, $description, '')
            ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Gathering' ]))
        ;

        $this->petExperienceService->gainExp($pet, 1, $distraction['skills'], $activityLog);

        return $activityLog;
    }

    private function getPossibleDistractions(ComputedPetSkills $petWithSkills, string $location): array
    {
        $weather = WeatherService::getWeather(new \DateTimeImmutable(), $petWithSkills->getPet());
        $distractions = [];
        $anyRain = $weather->getRainfall() > 0;
        $anyClouds = $weather->getClouds() > 0;
        $isNight = $weather->isNight;

        if(
            ($location === DistractionLocationEnum::WOODS && !$anyRain) ||
            ($location === DistractionLocationEnum::IN_TOWN && !$anyRain) ||
            $location === DistractionLocationEnum::UNDERGROUND
        )
        {
            $distractions[] = [
                'description' => 'they saw a spider making a crazy-huge web! They watched for a while before returning home.',
                'skills' => [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ],
            ];
        }

        if(($location === DistractionLocationEnum::WOODS || $location === DistractionLocationEnum::IN_TOWN) && !$anyRain)
        {
            $description = $isNight
                ? 'they saw an army of ants attacking a beehive! (A sneak attack at night?!) They watched for a while before returning home.'
                : 'they saw an army of ants attacking a beehive! They watched for a while before returning home.'
            ;

            $distractions[] = [
                'description' => $description,
                'skills' => [ PetSkillEnum::NATURE, PetSkillEnum::BRAWL ],
            ];
        }

        if(($location === DistractionLocationEnum::WOODS || $location === DistractionLocationEnum::IN_TOWN) && !$anyRain)
        {
            $winner = $this->rng->rngNextFromArray([
                'mantis', 'spider'
            ]);

            $distractions[] = [
                'description' => 'they saw a large spider fighting a praying mantis! After a long fight, the ' . $winner . ' claimed victory! (And a meal!)',
                'skills' => [ PetSkillEnum::NATURE, PetSkillEnum::BRAWL ],
            ];
        }

        if($location === DistractionLocationEnum::IN_TOWN && !$anyRain && $isNight)
        {
            $description = $this->rng->rngNextInt(1, 4) === 1
                ? 'they saw a praying mantis sitting on a street light, snatching up gnats and other small insects. It seemed like a pretty OP strat until a bat swooped by and nabbed the mantis!'
                : 'they saw a praying mantis sitting on a street light, snatching up gnats and other small insects with ease.'
            ;

            $distractions[] = [
                'description' => $description,
                'skills' => [ PetSkillEnum::NATURE, PetSkillEnum::BRAWL ],
            ];
        }

        if($location === DistractionLocationEnum::WOODS || $location === DistractionLocationEnum::BEACH)
        {
            $distractions[] = [
                'description' => 'they saw a HUGE turtle on the edge of the woods, just chillin\' and grazin\'.',
                'skills' => [ PetSkillEnum::NATURE ],
            ];
        }

        if(($location === DistractionLocationEnum::WOODS || $location === DistractionLocationEnum::IN_TOWN) && $isNight)
        {
            $rummageTarget = $this->rng->rngNextFromArray($location === DistractionLocationEnum::WOODS
                ? [ 'around a bush', 'around a fallen log' ]
                : [ 'through some trash', 'around a bush' ]
            );

            $darkness = $anyRain ? "darkness; any sound of struggle was drowned out by the rain" : "darkness";

            $distractions[] = [
                'description' => "they saw a raccoon rummaging {$rummageTarget}, when a huge owl swooped in and grabbed the raccoon! The raccoon let out a short cry as it was carried away, into the {$darkness}...",
                'skills' => [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH, PetSkillEnum::BRAWL ],
            ];
        }

        if($location === DistractionLocationEnum::BEACH && !$isNight)
        {
            $distractions[] = [
                'description' => 'they saw some seagulls smashing clams against the rocks' . ($anyRain ? ' in the rain' : '') . '! They watched for a while - from a safe distance - before returning home.',
                'skills' => [ PetSkillEnum::NATURE, PetSkillEnum::BRAWL ],
            ];
        }

        if($location === DistractionLocationEnum::BEACH && !$isNight)
        {
            $distractions[] = [
                'description' => 'they saw a pair of crabs under a rock, grooming one another, and eating the various bits picked off one another!',
                'skills' => [ PetSkillEnum::NATURE ],
            ];
        }

        if($location === DistractionLocationEnum::UNDERGROUND)
        {
            $distractions[] = [
                'description' => 'they saw an army of ants traveling through the tunnels. They followed for a while, but never found out where the army was headed...',
                'skills' => [ PetSkillEnum::NATURE ],
            ];
        }

        if($location === DistractionLocationEnum::UNDERGROUND)
        {
            $prey = $this->rng->rngNextFromArray([
                'slug',
                'worm'
            ]);

            $distractions[] = [
                'description' => "they saw a glowworm eating a {$prey}; the {$prey} wasn't dead yet, but it was clearly heading that way... (Oof! Brutal!)",
                'skills' => [ PetSkillEnum::NATURE, PetSkillEnum::BRAWL ],
            ];
        }

        if($location === DistractionLocationEnum::UNDERGROUND && $this->rng->rngNextInt(1, 5) === 1)
        {
            $distractions[] = [
                'description' => 'they saw a really funny stalagmite formation-- or, wait, are they stalactites? ("Stalagmites might make it..." but what does _that_ mean?!? Stupid, useless mnemonic!)',
                'skills' => [ PetSkillEnum::NATURE ],
            ];
        }

        if($location === DistractionLocationEnum::IN_TOWN)
        {
            if($anyRain)
                $description = 'they spotted a family of raccoons taking shelter from the rain under someone\'s back porch. They watched for a while as the raccoons talked and cleaned each other, but eventually got bored and returned home.';
            else
                $description = 'they spotted a family of raccoons walking across someone\'s backyard! They followed for a while, until the raccoons went into the sewers...';

            $distractions[] = [
                'description' => $description,
                'skills' => [ PetSkillEnum::NATURE ],
            ];
        }

        if($location === DistractionLocationEnum::IN_TOWN && !$isNight)
        {
            $retreatingAnimals = $this->rng->rngNextFromArray([
                'A large raccoon',
                'Some deer',
                'A pair of wild goats'
            ]);

            $distractions[] = [
                'description' => 'they watched a small flock of geese cross a road' . ($anyRain ? ' in the rain' : '') . '. ' . $retreatingAnimals . ' on the other side of the road, seeing the approaching geese, ran away.',
                'skills' => [ PetSkillEnum::NATURE ]
            ];
        }

        if($location === DistractionLocationEnum::IN_TOWN && !$anyRain && !$isNight)
        {
            $distractions[] = [
                'description' => 'they spotted a couple scientists chatting over a meal. They listened for a while before returning home.',
                'skills' => [ PetSkillEnum::SCIENCE ],
            ];
        }

        if($location === DistractionLocationEnum::WOODS || $location === DistractionLocationEnum::UNDERGROUND)
        {
            if($location === DistractionLocationEnum::WOODS)
            {
                if($anyRain)
                    $description = 'they caught a glimpse of a shadowy figure moving through the rain!';
                else
                    $description = 'they caught a glimpse of a shadowy figure moving amidst the trees!';
            }
            else
                $description = 'they caught a glimpse of a shadowy figure moving through the tunnels!';

            $distractions[] = [
                'description' => $description . ' They stalked it for a while, but lost the trail before ever getting a good look at it...',
                'skills' => [ PetSkillEnum::STEALTH, PetSkillEnum::ARCANA ],
            ];
        }

        if(($location === DistractionLocationEnum::BEACH || $location === DistractionLocationEnum::WOODS) && !$anyRain && !$anyClouds && !$isNight)
        {
            $distractions[] = [
                'description' => 'they saw a family of huge lizards bathing in a pool. They watched for a while - from a safe distance - before returning home.',
                'skills' => [ PetSkillEnum::STEALTH, PetSkillEnum::NATURE ],
            ];
        }

        if($location === DistractionLocationEnum::VOLCANO && !$isNight)
        {
            $distractions[] = [
                'description' => 'they saw a family of huge lizards lounging on steaming rocks' . ($anyRain ? ', apparently completely unconcerned about the rain' : '') . '. They watched for a while - from a safe distance - before returning home.',
                'skills' => [ PetSkillEnum::STEALTH, PetSkillEnum::NATURE ],
            ];
        }

        if($location === DistractionLocationEnum::VOLCANO)
        {
            $distractions[] = [
                'description' => 'they saw an army of ants traveling amidst steaming rocks. They followed for a while, but never found out where the army was headed...',
                'skills' => [ PetSkillEnum::NATURE ],
            ];

            $distractions[] = [
                'description' => 'they watched as a plume of smoke escaped from the volcano\'s top. They investigated for a while, looking for the source of the smoke, or any signs of geologic activity, but didn\'t find anything conclusive...',
                'skills' => [ PetSkillEnum::SCIENCE ],
            ];
        }

        if($location === DistractionLocationEnum::BEACH && !$isNight)
        {
            $distractions[] = [
                'description' => 'they saw a seagull flying low over the ocean. Suddenly, a shark jumped out, and snatched it!',
                'skills' => [ PetSkillEnum::NATURE, PetSkillEnum::BRAWL, PetSkillEnum::STEALTH ],
            ];
        }

        if($location === DistractionLocationEnum::BEACH)
        {
            $distractions[] = [
                'description' => 'they saw a fox running into the woods, seagull egg in maw! Sneaky thief!',
                'skills' => [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH ],
            ];
        }

        if($location === DistractionLocationEnum::BEACH)
        {
            if($anyRain)
                $description = 'through the rain they saw some breaching whales out on the ocean. They watched for a while before returning home.';
            else if($isNight)
                $description = 'they saw some breaching whales out on the night ocean. They watched for a while before returning home.';
            else
                $description = 'they saw some breaching whales out on the ocean. They watched for a while before returning home.';

            $distractions[] = [
                'description' => $description,
                'skills' => [ PetSkillEnum::NATURE ],
            ];
        }

        return $distractions;
    }
}