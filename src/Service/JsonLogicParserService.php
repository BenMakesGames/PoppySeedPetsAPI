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


namespace App\Service;

use App\Entity\User;
use App\Enum\UnlockableFeatureEnum;
use Psr\Log\LoggerInterface;

class JsonLogicParserService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly UserStatsService $userStatsRepository)
    {
    }

    public function evaluate($expression, User $user)
    {
        if(is_array($expression))
        {
            if(count($expression) === 2)
            {
                $operator = $this->evaluate($expression[0], $user);
                $right = $this->evaluate($expression[1], $user);

                switch($operator)
                {
                    case '!': return !$right;
                    case '-': return -$right;
                    case 'sqrt': return sqrt($right);
                    case 'floor': return floor($right);
                    case 'ceil': return ceil($right);
                    case 'round': return round($right);
                    default:
                        $this->logger->error('JsonLogicParserService could not parse the following unary operator: ' . var_export($expression, true));
                        throw new \InvalidArgumentException('JsonLogicParserService failed to parse a unary operator. Additional information has been added to the logs.');
                }
            }
            else if(count($expression) === 3)
            {
                $left = $this->evaluate($expression[0], $user);
                $operator = $this->evaluate($expression[1], $user);
                $right = $this->evaluate($expression[2], $user);

                switch($operator)
                {
                    case '<': return $left < $right;
                    case '>': return $left > $right;
                    case '<=': return $left <= $right;
                    case '>=': return $left >= $right;
                    case '==': return $left == $right;
                    case '+': return $left + $right;
                    case '-': return $left - $right;
                    case '*': return $left * $right;
                    case '/': return $left / $right;
                    case '%': return $left % $right;
                    case '||': return $left || $right;
                    case '&&': return $left && $right;
                    default:
                        $this->logger->error('JsonLogicParserService could not parse the following binary operator: ' . var_export($expression, true));
                        throw new \InvalidArgumentException('JsonLogicParserService failed to parse a binary operator. Additional information has been added to the logs.');
                }
            }
            else
            {
                $this->logger->error('JsonLogicParserService could not parse the following array expression: ' . var_export($expression, true));
                throw new \InvalidArgumentException('JsonLogicParserService failed to parse an array expression. Additional information has been added to the logs.');
            }
        }
        else if(is_string($expression) && $expression[0] === '%' && $expression[strlen($expression) - 1] === '%')
        {
            if($expression === '%user.moneys%')
                return $user->getMoneys();
            else if($expression === '%user.dailySeed%')
                return $user->getDailySeed();
            else if($expression === '%user.unlockedBeehive%')
                return $user->hasUnlockedFeature(UnlockableFeatureEnum::Beehive);
            else if(preg_match('/%user.stat.[^%]+%/', $expression))
            {
                $stat = substr($expression, 11, strlen($expression) - 12);

                return $this->userStatsRepository->getStatValue($user, $stat);
            }
            else
            {
                $this->logger->error('JsonLogicParserService could not parse the following template expression: ' . var_export($expression, true));
                throw new \InvalidArgumentException('JsonLogicParserService failed to parse a template expression. Additional information has been added to the logs.');
            }
        }
        else
            return $expression;
    }
}