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
use App\Entity\UserActivityLog;
use App\Enum\UserStat;
use App\Functions\PlayerLogFactory;
use Doctrine\ORM\EntityManagerInterface;

class TransactionService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserStatsService $userStatsRepository
    )
    {
    }

    /**
     * @param string[] $additionalTags
     */
    public function spendMoney(User $user, int $amount, string $description, bool $countTotalMoneysSpentStat = true, array $additionalTags = []): UserActivityLog
    {
        if($amount < 1)
            throw new \InvalidArgumentException('$amount must be 1 or greater.');

        if($user->getMoneys() < $amount)
            throw new \InvalidArgumentException($user->getName() . ' (#' . $user->getId() . ') does not have enough money.');

        $user->increaseMoneys(-$amount);

        if($countTotalMoneysSpentStat)
            $this->userStatsRepository->incrementStat($user, UserStat::TotalMoneysSpent, $amount);

        $tags = array_merge($additionalTags, [ 'Moneys' ]);

        return PlayerLogFactory::create($this->em, $user, $description . ' (-' . $amount . '~~m~~)', $tags);
    }

    /**
     * @param string[] $additionalTags
     */
    public function getMoney(User $user, int $amount, string $description, array $additionalTags = []): UserActivityLog
    {
        if($amount < 1)
            throw new \InvalidArgumentException('$amount must be 1 or greater.');

        $user->increaseMoneys($amount);

        $tags = array_merge($additionalTags, [ 'Moneys' ]);

        return PlayerLogFactory::create($this->em, $user, $description . ' (+' . $amount . '~~m~~)', $tags);
    }

    /**
     * @param string[] $additionalTags
     */
    public function spendRecyclingPoints(User $user, int $amount, string $description, array $additionalTags = []): UserActivityLog
    {
        if($amount < 1)
            throw new \InvalidArgumentException('$amount must be 1 or greater.');

        if($user->getRecyclePoints() < $amount)
            throw new \InvalidArgumentException($user->getName() . ' (#' . $user->getId() . ') does not have enough recycling points.');

        $user->increaseRecyclePoints(-$amount);

        $tags = array_merge($additionalTags, [ 'Recycling' ]);

        return PlayerLogFactory::create($this->em, $user, $description . ' (-' . $amount . ' Recycling Point' . ($amount == 1 ? '' : 's') . ')', $tags);
    }

    /**
     * @param string[] $additionalTags
     */
    public function getRecyclingPoints(User $user, int $amount, string $description, array $additionalTags = []): UserActivityLog
    {
        if($amount < 0)
            throw new \InvalidArgumentException('$amount must be 1 or greater.');

        if($amount >= 1)
            $user->increaseRecyclePoints($amount);

        $tags = array_merge($additionalTags, [ 'Recycling' ]);

        return PlayerLogFactory::create($this->em, $user, $description . ' (+' . $amount . ' Recycling Point' . ($amount == 1 ? '' : 's') . ')', $tags);
    }

    /**
     * @param string[] $additionalTags
     */
    public function spendMuseumFavor(User $user, int $amount, string $description, array $additionalTags = []): UserActivityLog
    {
        if($amount < 1)
            throw new \InvalidArgumentException('$amount must be 1 or greater.');

        if($user->getMuseumPoints() - $user->getMuseumPointsSpent() < $amount)
            throw new \InvalidArgumentException($user->getName() . ' (#' . $user->getId() . ') does not have enough museum favor.');

        $user->addMuseumPointsSpent($amount);

        $tags = array_merge($additionalTags, [ 'Museum' ]);

        return PlayerLogFactory::create($this->em, $user, $description . ' (-' . $amount . ' Museum Favor)', $tags);
    }

    /**
     * @param string[] $additionalTags
     */
    public function getMuseumFavor(User $user, int $amount, string $description, array $additionalTags = []): UserActivityLog
    {
        if($amount < 1)
            throw new \InvalidArgumentException('$amount must be 1 or greater.');

        $user->addMuseumPoints($amount);

        $tags = array_merge($additionalTags, [ 'Museum' ]);

        return PlayerLogFactory::create($this->em, $user, $description . ' (+' . $amount . ' Museum Favor)', $tags);
    }
}