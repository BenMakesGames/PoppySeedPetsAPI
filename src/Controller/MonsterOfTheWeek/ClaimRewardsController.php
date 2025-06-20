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


namespace App\Controller\MonsterOfTheWeek;

use App\Entity\MonsterOfTheWeek;
use App\Entity\MonsterOfTheWeekContribution;
use App\Enum\LocationEnum;
use App\Enum\MonsterOfTheWeekEnum;
use App\Enum\UserStat;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\UnreachableException;
use App\Functions\ArrayFunctions;
use App\Service\Clock;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/monsterOfTheWeek")]
class ClaimRewardsController
{
    #[Route("/{monsterId}/claimRewards", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function claimRewards(
        int $monsterId, InventoryService $inventoryService, ResponseService $responseService, EntityManagerInterface $em,
        UserStatsService $userStatsService, Clock $clock,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $monster = $em->getRepository(MonsterOfTheWeek::class)->findOneBy([
            'id' => $monsterId
        ]);

        if($clock->now->setTime(0, 0, 0) <= $monster->getEndDate())
            throw new PSPInvalidOperationException("This spirit hasn't left, yet.");

        $contribution = $em->getRepository(MonsterOfTheWeekContribution::class)->findOneBy([
            'monsterOfTheWeek' => $monster,
            'user' => $user
        ]);

        $thresholds = MonsterOfTheWeekHelpers::getBasePrizeValues($monster->getMonster());

        if($contribution === null || $contribution->getPoints() < 1)
            throw new PSPInvalidOperationException("You didn't feed this spirit enough to get its attention :(");

        if($contribution->getRewardsClaimed())
            throw new PSPInvalidOperationException("You have already claimed the rewards for feeding this spirit!");

        $rewards = [
            MonsterOfTheWeekHelpers::getConsolationPrize($monster->getMonster())
        ];

        if($monster->getCommunityTotal() >= $thresholds[0] * $monster->getLevel())
        {
            $rewards[] = $monster->getEasyPrize()->getName();
            $userStatsService->incrementStat($user, UserStat::RECEIVED_A_MINOR_PRIZE_FROM_A_GREAT_SPIRIT);
            $userStatsService->incrementStat($user, self::ReceivedAPrizeFrom('Minor', $monster->getMonster()));
        }

        if($monster->getCommunityTotal() >= $thresholds[1] * $monster->getLevel())
        {
            $rewards[] = $monster->getMediumPrize()->getName();
            $userStatsService->incrementStat($user, UserStat::RECEIVED_A_MODERATE_PRIZE_FROM_A_GREAT_SPIRIT);
            $userStatsService->incrementStat($user, self::ReceivedAPrizeFrom('Moderate', $monster->getMonster()));
        }

        if($monster->getCommunityTotal() >= $thresholds[2] * $monster->getLevel())
        {
            $rewards[] = $monster->getHardPrize()->getName();
            $userStatsService->incrementStat($user, UserStat::RECEIVED_A_MAJOR_PRIZE_FROM_A_GREAT_SPIRIT);
            $userStatsService->incrementStat($user, self::ReceivedAPrizeFrom('Major', $monster->getMonster()));
        }

        $contribution->setRewardsClaimed();

        foreach($rewards as $reward)
            $inventoryService->receiveItem($reward, $user, null, $user->getName() . ' received this for feeding ' . MonsterOfTheWeekHelpers::getSpiritNameWithArticle($monster->getMonster()) . '.', LocationEnum::Home, true);

        $em->flush();

        $punctuation = match(count($rewards))
        {
            1 => '. (The spirit was 0% impressed by the island\'s offerings.)',
            2 => '!',
            3 => '! :)',
            4 => '! :D',
            default => throw new UnreachableException()
        };

        return $responseService->success('You received ' . ArrayFunctions::list_nice($rewards) . $punctuation);
    }

    public static function ReceivedAPrizeFrom(string $prizeType, MonsterOfTheWeekEnum $monster): string
    {
        return match($prizeType)
        {
            'Minor' => match ($monster)
            {
                MonsterOfTheWeekEnum::Anhur => UserStat::RECEIVED_A_MINOR_PRIZE_FROM_A_HUNTER_OF_ANHUR,
                MonsterOfTheWeekEnum::Boshinogami => UserStat::RECEIVED_A_MINOR_PRIZE_FROM_SOME_BOSHINOGAMI,
                MonsterOfTheWeekEnum::Cardea => UserStat::RECEIVED_A_MINOR_PRIZE_FROM_CARDEAS_LOCKBEARER,
                MonsterOfTheWeekEnum::Dionysus => UserStat::RECEIVED_A_MINOR_PRIZE_FROM_DIONYSUSS_HUNGER,
                MonsterOfTheWeekEnum::Huehuecoyotl => UserStat::RECEIVED_A_MINOR_PRIZE_FROM_HUEHUECOYOTLS_FOLLY,
                MonsterOfTheWeekEnum::EiriPersona => UserStat::RECEIVED_A_MINOR_PRIZE_FROM_AN_EIRI_PERSONA,
                default => throw new \Exception('Invalid monster: ' . $monster->value)
            },
            'Moderate' => match ($monster)
            {
                MonsterOfTheWeekEnum::Anhur => UserStat::RECEIVED_A_MODERATE_PRIZE_FROM_A_HUNTER_OF_ANHUR,
                MonsterOfTheWeekEnum::Boshinogami => UserStat::RECEIVED_A_MODERATE_PRIZE_FROM_SOME_BOSHINOGAMI,
                MonsterOfTheWeekEnum::Cardea => UserStat::RECEIVED_A_MODERATE_PRIZE_FROM_CARDEAS_LOCKBEARER,
                MonsterOfTheWeekEnum::Dionysus => UserStat::RECEIVED_A_MODERATE_PRIZE_FROM_DIONYSUSS_HUNGER,
                MonsterOfTheWeekEnum::Huehuecoyotl => UserStat::RECEIVED_A_MODERATE_PRIZE_FROM_HUEHUECOYOTLS_FOLLY,
                MonsterOfTheWeekEnum::EiriPersona => UserStat::RECEIVED_A_MODERATE_PRIZE_FROM_AN_EIRI_PERSONA,
                default => throw new \Exception('Invalid monster: ' . $monster->value)
            },
            'Major' => match ($monster)
            {
                MonsterOfTheWeekEnum::Anhur => UserStat::RECEIVED_A_MAJOR_PRIZE_FROM_A_HUNTER_OF_ANHUR,
                MonsterOfTheWeekEnum::Boshinogami => UserStat::RECEIVED_A_MAJOR_PRIZE_FROM_SOME_BOSHINOGAMI,
                MonsterOfTheWeekEnum::Cardea => UserStat::RECEIVED_A_MAJOR_PRIZE_FROM_CARDEAS_LOCKBEARER,
                MonsterOfTheWeekEnum::Dionysus => UserStat::RECEIVED_A_MAJOR_PRIZE_FROM_DIONYSUSS_HUNGER,
                MonsterOfTheWeekEnum::Huehuecoyotl => UserStat::RECEIVED_A_MAJOR_PRIZE_FROM_HUEHUECOYOTLS_FOLLY,
                MonsterOfTheWeekEnum::EiriPersona => UserStat::RECEIVED_A_MAJOR_PRIZE_FROM_AN_EIRI_PERSONA,
                default => throw new \Exception('Invalid monster: ' . $monster->value)
            }
        };
    }
}
