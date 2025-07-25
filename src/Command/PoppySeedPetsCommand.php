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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

abstract class PoppySeedPetsCommand extends Command
{
    protected InputInterface $input;
    protected OutputInterface $output;
    protected QuestionHelper $questionHelper;

    abstract protected function doCommand(): int;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;
        $this->questionHelper = $this->getHelper('question');

        return $this->doCommand();
    }

    protected function confirm(string $prompt, bool $defaultValue): bool
    {
        if($defaultValue)
            $prompt .= ' (Yes) ';
        else
            $prompt .= ' (No) ';

        return $this->questionHelper->ask($this->input, $this->output, new ConfirmationQuestion($prompt, $defaultValue));
    }

    protected function askNullableInt(string $prompt, ?int $defaultValue, callable $constraint = null): ?int
    {
        $question = new Question($prompt . ' (' . ($defaultValue === null ? '~' : $defaultValue) . ') ', $defaultValue);

        $question->setValidator(function($answer) use($constraint) {
            if($answer === '~') $answer = null;

            if($answer !==  null && (int)$answer != $answer)
                throw new \RuntimeException('Must be an integer, or null (~).');

            $answerValue = $answer === null ? null : (int)$answer;

            if($constraint && !$constraint($answerValue))
                throw new \RuntimeException('Number is out of range.');

            return $answerValue;
        });

        return $this->ask($question);
    }

    protected function askInt(string $prompt, int $defaultValue, callable $constraint = null): int
    {
        $question = new Question($prompt . ' (' . $defaultValue . ') ', $defaultValue);

        $question->setValidator(function($answer) use($constraint) {
            if((int)$answer != $answer)
                throw new \RuntimeException('Must be an integer.');

            if($constraint && !$constraint((int)$answer))
                throw new \RuntimeException('Number is out of range.');

            return (int)$answer;
        });

        return $this->ask($question);
    }

    protected function askBool(string $prompt, bool $defaultValue): bool
    {
        return $this->confirm($prompt, $defaultValue);
    }

    protected function askChoice(string $prompt, array $choices, $defaultValue): string
    {
        $question = new ChoiceQuestion($prompt, $choices, $defaultValue);

        return $this->ask($question);
    }

    protected function askNullableString(string $prompt, ?string $defaultValue, callable $constraint = null): ?string
    {
        if($defaultValue === null) $defaultValue = '~';

        $result = $this->askString($prompt, $defaultValue, $constraint);

        return $result === '~' ? null : $result;
    }

    protected function askString(string $prompt, ?string $defaultValue, callable $constraint = null): string
    {
        if($defaultValue === null) $defaultValue = '';

        $question = new Question($prompt . ' (' . $defaultValue . ') ', $defaultValue);

        $question->setValidator(function($answer) use($constraint) {
            if($constraint && !$constraint(trim($answer)))
                throw new \RuntimeException('That input was no good. Try again.');

            return $answer;
        });

        return trim($this->ask($question));
    }

    protected function askFloat(string $prompt, float $defaultValue, callable $constraint = null): float
    {
        $question = new Question($prompt . ' (' . $defaultValue . ') ', $defaultValue);

        $question->setValidator(function($answer) use($constraint) {
            if((float)$answer != $answer)
                throw new \RuntimeException('Must be a real number.');

            if($constraint && !$constraint((float)$answer))
                throw new \RuntimeException('Number is out of range.');

            return (float)$answer;
        });

        return $this->ask($question);
    }

    protected function ask(Question $q): mixed
    {
        return $this->questionHelper->ask($this->input, $this->output, $q);
    }
}
