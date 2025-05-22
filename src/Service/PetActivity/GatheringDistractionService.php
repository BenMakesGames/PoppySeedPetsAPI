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


namespace App\Service\PetActivity;

use App\Entity\PetActivityLog;
use App\Enum\DistractionLocationEnum;
use App\Enum\EnumInvalidValueException;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\PetSkillEnum;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\ComputedPetSkills;
use App\Service\FieldGuideService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\WeatherService;
use Doctrine\ORM\EntityManagerInterface;

class GatheringDistractionService
{
    public function __construct(
        private readonly IRandom $rng,
        private readonly PetExperienceService $petExperienceService,
        private readonly FieldGuideService $fieldGuideService,
        private readonly EntityManagerInterface $em
    )
    {
    }

    public function adventure(ComputedPetSkills $petWithSkills, DistractionLocationEnum $location, string $whileDoingDescription): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $distraction = $this->rng->rngNextFromArray($this->getPossibleDistractions($petWithSkills, $location));

        $description = 'While %pet:' . $pet->getId() . '.name% was ' . $whileDoingDescription . ', ' . $distraction['description'];

        if($location === DistractionLocationEnum::Volcano)
        {
            $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Île Volcan', '%pet:' . $pet->getId() . '.name% went out ' . $whileDoingDescription . '...');
        }

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $description)
            ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Gathering ]))
        ;

        $this->petExperienceService->gainExp($pet, 1, $distraction['skills'], $activityLog);

        return $activityLog;
    }

    private function getPossibleDistractions(ComputedPetSkills $petWithSkills, DistractionLocationEnum $location): array
    {
        $weather = WeatherService::getWeather(new \DateTimeImmutable(), $petWithSkills->getPet());
        $distractions = [];
        $anyRain = $weather->getRainfall() > 0;
        $anyClouds = $weather->getClouds() > 0;
        $isNight = $weather->isNight;

        if(
            ($location === DistractionLocationEnum::Woods && !$anyRain) ||
            ($location === DistractionLocationEnum::InTown && !$anyRain) ||
            $location === DistractionLocationEnum::Underground
        )
        {
            $distractions[] = [
                'description' => 'they saw a spider making a crazy-huge web! They watched for a while before returning home.',
                'skills' => [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ],
            ];
        }

        if(($location === DistractionLocationEnum::Woods || $location === DistractionLocationEnum::InTown) && !$anyRain)
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

        if(($location === DistractionLocationEnum::Woods || $location === DistractionLocationEnum::InTown) && !$anyRain)
        {
            $winner = $this->rng->rngNextFromArray([
                'mantis', 'spider'
            ]);

            $distractions[] = [
                'description' => 'they saw a large spider fighting a praying mantis! After a long fight, the ' . $winner . ' claimed victory! (And a meal!)',
                'skills' => [ PetSkillEnum::NATURE, PetSkillEnum::BRAWL ],
            ];
        }

        if($location === DistractionLocationEnum::InTown && !$anyRain && $isNight)
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

        if($location === DistractionLocationEnum::Woods || $location === DistractionLocationEnum::Beach)
        {
            $distractions[] = [
                'description' => 'they saw a HUGE turtle on the edge of the woods, just chillin\' and grazin\'.',
                'skills' => [ PetSkillEnum::NATURE ],
            ];
        }

        if(($location === DistractionLocationEnum::Woods || $location === DistractionLocationEnum::InTown) && $isNight)
        {
            $rummageTarget = $this->rng->rngNextFromArray($location === DistractionLocationEnum::Woods
                ? [ 'around a bush', 'around a fallen log' ]
                : [ 'through some trash', 'around a bush' ]
            );

            $darkness = $anyRain ? "darkness; any sound of struggle was drowned out by the rain" : "darkness";

            $distractions[] = [
                'description' => "they saw a raccoon rummaging {$rummageTarget}, when a huge owl swooped in and grabbed the raccoon! The raccoon let out a short cry as it was carried away, into the {$darkness}...",
                'skills' => [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH, PetSkillEnum::BRAWL ],
            ];
        }

        if($location === DistractionLocationEnum::Beach && !$isNight)
        {
            $distractions[] = [
                'description' => 'they saw some seagulls smashing clams against the rocks' . ($anyRain ? ' in the rain' : '') . '! They watched for a while - from a safe distance - before returning home.',
                'skills' => [ PetSkillEnum::NATURE, PetSkillEnum::BRAWL ],
            ];
        }

        if($location === DistractionLocationEnum::Beach && !$isNight)
        {
            $distractions[] = [
                'description' => 'they saw a pair of crabs under a rock, grooming one another, and eating the various bits picked off one another!',
                'skills' => [ PetSkillEnum::NATURE ],
            ];
        }

        if($location === DistractionLocationEnum::Underground)
        {
            $distractions[] = [
                'description' => 'they saw an army of ants traveling through the tunnels. They followed for a while, but never found out where the army was headed...',
                'skills' => [ PetSkillEnum::NATURE ],
            ];
        }

        if($location === DistractionLocationEnum::Underground)
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

        if($location === DistractionLocationEnum::Underground && $this->rng->rngNextInt(1, 5) === 1)
        {
            $distractions[] = [
                'description' => 'they saw a really funny stalagmite formation-- or, wait, are they stalactites? ("Stalagmites might make it..." but what does _that_ mean?!? Stupid, useless mnemonic!)',
                'skills' => [ PetSkillEnum::NATURE ],
            ];
        }

        if($location === DistractionLocationEnum::InTown)
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

        if($location === DistractionLocationEnum::InTown && !$isNight)
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

        if($location === DistractionLocationEnum::InTown && !$anyRain && !$isNight)
        {
            $distractions[] = [
                'description' => 'they spotted a couple scientists chatting over a meal. They listened for a while before returning home.',
                'skills' => [ PetSkillEnum::SCIENCE ],
            ];
        }

        if($location === DistractionLocationEnum::Woods || $location === DistractionLocationEnum::Underground)
        {
            if($location === DistractionLocationEnum::Woods)
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

        if(($location === DistractionLocationEnum::Beach || $location === DistractionLocationEnum::Woods) && !$anyRain && !$anyClouds && !$isNight)
        {
            $distractions[] = [
                'description' => 'they saw a family of huge lizards bathing in a pool. They watched for a while - from a safe distance - before returning home.',
                'skills' => [ PetSkillEnum::STEALTH, PetSkillEnum::NATURE ],
            ];
        }

        if($location === DistractionLocationEnum::Volcano && !$isNight)
        {
            $distractions[] = [
                'description' => 'they saw a family of huge lizards lounging on steaming rocks' . ($anyRain ? ', apparently completely unconcerned about the rain' : '') . '. They watched for a while - from a safe distance - before returning home.',
                'skills' => [ PetSkillEnum::STEALTH, PetSkillEnum::NATURE ],
            ];
        }

        if($location === DistractionLocationEnum::Volcano)
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

        if($location === DistractionLocationEnum::Beach && !$isNight)
        {
            $distractions[] = [
                'description' => 'they saw a seagull flying low over the ocean. Suddenly, a shark jumped out, and snatched it!',
                'skills' => [ PetSkillEnum::NATURE, PetSkillEnum::BRAWL, PetSkillEnum::STEALTH ],
            ];
        }

        if($location === DistractionLocationEnum::Beach)
        {
            $distractions[] = [
                'description' => 'they saw a fox running into the woods, seagull egg in maw! Sneaky thief!',
                'skills' => [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH ],
            ];
        }

        if($location === DistractionLocationEnum::Beach)
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