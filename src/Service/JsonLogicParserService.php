<?php
namespace App\Service;

use App\Entity\User;
use App\Enum\UnlockableFeatureEnum;
use App\Repository\UserStatsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class JsonLogicParserService
{
    private LoggerInterface $logger;
    private EntityManagerInterface $em;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $em)
    {
        $this->logger = $logger;
        $this->em = $em;
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

                return UserStatsRepository::getStatValue($this->em, $user, $stat);
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