<?php
namespace App\Command;

use App\Service\ProfanityFilterService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

class TestProfanityFilterCommand extends PoppySeedPetsCommand
{
    private $profanityFilter;

    public function __construct(ProfanityFilterService $profanityFilter)
    {
        $this->profanityFilter = $profanityFilter;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:test-profanity-filter')
            ->setDescription('Runs text through the profanity filter, and displays the result.')
            ->addArgument('phrase', InputArgument::REQUIRED, 'The phrase to test with.')
        ;
    }

    protected function doCommand(): int
    {
        $phrase = $this->input->getArgument('phrase');

        $start = microtime(true);
        $output = $this->profanityFilter->filter($phrase);
        $end = microtime(true);

        echo $output . "\n";
        echo "(took " . round($end - $start, 4) . " seconds)\n";

        return Command::SUCCESS;
    }
}
