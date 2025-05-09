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


namespace App\Command;

use App\Enum\PetGroupTypeEnum;
use App\Service\PetGroupService;
use Symfony\Component\Console\Input\InputArgument;

class TestGroupNamesCommand extends PoppySeedPetsCommand
{
    private PetGroupService $petGroupService;

    public function __construct(PetGroupService $petGroupService)
    {
        $this->petGroupService = $petGroupService;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:test-group-names')
            ->setDescription('Tests group name generators.')
            ->addArgument('group-type', InputArgument::OPTIONAL, 'Type of group to test name generation of.')
        ;
    }

    private const array GroupTypesByName = [
        'band' => PetGroupTypeEnum::BAND,
        'astronomy' => PetGroupTypeEnum::ASTRONOMY,
        'gaming' => PetGroupTypeEnum::GAMING
    ];

    protected function doCommand(): int
    {
        $argument = $this->input->getArgument('group-type');

        if(!array_key_exists($argument, self::GroupTypesByName))
        {
            $this->output->writeln('Group type must be one of "astronomy", "band", or "gaming".');
            return self::FAILURE;
        }

        $groupType = self::GroupTypesByName[$argument];

        for($i = 0; $i < 20; $i++)
            $this->output->writeln($this->petGroupService->generateName($groupType));

        return self::SUCCESS;
    }
}
