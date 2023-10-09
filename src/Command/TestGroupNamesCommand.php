<?php
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

    protected function configure()
    {
        $this
            ->setName('app:test-group-names')
            ->setDescription('Tests group name generators.')
            ->addArgument('group-type', InputArgument::OPTIONAL, 'Type of group to test name generation of.')
        ;
    }

    private const GROUP_TYPES_BY_NAME = [
        'band' => PetGroupTypeEnum::BAND,
        'astronomy' => PetGroupTypeEnum::ASTRONOMY,
        'gaming' => PetGroupTypeEnum::GAMING
    ];

    protected function doCommand(): int
    {
        $argument = $this->input->getArgument('group-type');

        if(!array_key_exists($argument, self::GROUP_TYPES_BY_NAME))
        {
            $this->output->writeln('Group type must be one of "astronomy", "band", or "gaming".');
            return self::FAILURE;
        }

        $groupType = self::GROUP_TYPES_BY_NAME[$argument];

        for($i = 0; $i < 20; $i++)
            $this->output->writeln($this->petGroupService->generateName($groupType));

        return self::SUCCESS;
    }
}
