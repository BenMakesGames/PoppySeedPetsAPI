<?php
namespace App\Command;

use App\Service\ProfanityFilterService;
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
            ->setDescription('Tests the JSON logic parser against a given user & JSON expression.')
            ->addArgument('phrase', InputArgument::REQUIRED, 'The phrase to test with.')
        ;
    }

    protected function doCommand()
    {
        $phrase = $this->input->getArgument('phrase');

        var_dump($this->profanityFilter->filter($phrase));
    }
}
