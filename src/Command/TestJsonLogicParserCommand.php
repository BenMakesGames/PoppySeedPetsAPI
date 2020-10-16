<?php
namespace App\Command;

use App\Repository\UserRepository;
use App\Service\JsonLogicParserService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

class TestJsonLogicParserCommand extends PoppySeedPetsCommand
{
    private $jsonLogicParserService;
    private $userRepository;

    public function __construct(JsonLogicParserService $jsonLogicParserService, UserRepository $userRepository)
    {
        $this->jsonLogicParserService = $jsonLogicParserService;
        $this->userRepository = $userRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:test-json-logic-parser')
            ->setDescription('Tests the JSON logic parser against a given user & JSON expression.')
            ->addArgument('user', InputArgument::REQUIRED, 'The ID of the user to test.')
            ->addArgument('file', InputArgument::REQUIRED, 'The name of a JSON file containing the expression to test.')
        ;
    }

    protected function doCommand(): int
    {
        $userId = $this->input->getArgument('user');
        $user = $this->userRepository->find($userId);

        if(!$user)
        {
            $this->output->writeln('There is no user #' . $userId . '.');
            return Command::FAILURE;
        }

        $fileName = $this->input->getArgument('file');

        if(!file_exists($fileName))
        {
            $this->output->writeln('File ' . $fileName . ' does not exist.');
            return Command::FAILURE;
        }

        $expression = file_get_contents($fileName);

        try
        {
            $data = \GuzzleHttp\json_decode($expression, true);
        }
        catch(\InvalidArgumentException $e)
        {
            $this->output->writeln('Read file ' . $fileName . ', but could not parse its contents as JSON.');
            return Command::FAILURE;
        }

        var_dump($this->jsonLogicParserService->evaluate($data, $user));

        return Command::SUCCESS;
    }
}
