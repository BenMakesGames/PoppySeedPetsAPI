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

use App\Entity\User;
use App\Service\JsonLogicParserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;

class TestJsonLogicParserCommand extends PoppySeedPetsCommand
{
    private JsonLogicParserService $jsonLogicParserService;
    private EntityManagerInterface $em;

    public function __construct(JsonLogicParserService $jsonLogicParserService, EntityManagerInterface $em)
    {
        $this->jsonLogicParserService = $jsonLogicParserService;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure(): void
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
        $user = $this->em->getRepository(User::class)->find($userId);

        if(!$user)
        {
            $this->output->writeln('There is no user #' . $userId . '.');
            return self::FAILURE;
        }

        $fileName = $this->input->getArgument('file');

        if(!file_exists($fileName))
        {
            $this->output->writeln('File ' . $fileName . ' does not exist.');
            return self::FAILURE;
        }

        $expression = file_get_contents($fileName);

        try
        {
            $data = \GuzzleHttp\json_decode($expression, true);
        }
        catch(\InvalidArgumentException $e)
        {
            $this->output->writeln('Read file ' . $fileName . ', but could not parse its contents as JSON.');
            return self::FAILURE;
        }

        var_dump($this->jsonLogicParserService->evaluate($data, $user));

        return self::SUCCESS;
    }
}
