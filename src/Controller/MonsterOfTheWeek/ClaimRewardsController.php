<?php
declare(strict_types=1);

namespace App\Controller\MonsterOfTheWeek;

use App\Entity\Inventory;
use App\Entity\MonsterOfTheWeek;
use App\Entity\MonsterOfTheWeekContribution;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\MonsterOfTheWeekEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Functions\ArrayFunctions;
use App\Service\Clock;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/monsterOfTheWeek")]
class ClaimRewardsController extends AbstractController
{
    #[Route("/{monsterId}/claimRewards", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function claimRewards(
        int $monsterId, InventoryService $inventoryService, ResponseService $responseService, EntityManagerInterface $em,
        UserStatsService $userStatsService, Clock $clock
    )
    {
        /** @var User $user */
        $user = $this->getUser();

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
            $userStatsService->incrementStat($user, UserStatEnum::RECEIVED_A_MINOR_PRIZE_FROM_A_GREAT_SPIRIT);
            $userStatsService->incrementStat($user, self::ReceivedAPrizeFrom('Minor', $monster->getMonster()));
        }

        if($monster->getCommunityTotal() >= $thresholds[1] * $monster->getLevel())
        {
            $rewards[] = $monster->getMediumPrize()->getName();
            $userStatsService->incrementStat($user, UserStatEnum::RECEIVED_A_MODERATE_PRIZE_FROM_A_GREAT_SPIRIT);
            $userStatsService->incrementStat($user, self::ReceivedAPrizeFrom('Moderate', $monster->getMonster()));
        }

        if($monster->getCommunityTotal() >= $thresholds[2] * $monster->getLevel())
        {
            $rewards[] = $monster->getHardPrize()->getName();
            $userStatsService->incrementStat($user, UserStatEnum::RECEIVED_A_MAJOR_PRIZE_FROM_A_GREAT_SPIRIT);
            $userStatsService->incrementStat($user, self::ReceivedAPrizeFrom('Major', $monster->getMonster()));
        }

        $contribution->setRewardsClaimed();

        foreach($rewards as $reward)
            $inventoryService->receiveItem($reward, $user, null, $user->getName() . ' received this for feeding ' . MonsterOfTheWeekHelpers::getSpiritNameWithArticle($monster->getMonster()) . '.', LocationEnum::HOME, true);

        $em->flush();

        $punctuation = match(count($rewards))
        {
            1 => '. (The spirit was 0% impressed by the island\'s offerings.)',
            2 => '!',
            3 => '! :)',
            4 => '! :D'
        };

        return $responseService->success('You received ' . ArrayFunctions::list_nice($rewards) . $punctuation);
    }

    public static function ReceivedAPrizeFrom(string $prizeType, string $monster): string
    {
        return match($prizeType)
        {
            'Minor' => match ($monster)
            {
                MonsterOfTheWeekEnum::ANHUR => UserStatEnum::RECEIVED_A_MINOR_PRIZE_FROM_A_HUNTER_OF_ANHUR,
                MonsterOfTheWeekEnum::BOSHINOGAMI => UserStatEnum::RECEIVED_A_MINOR_PRIZE_FROM_SOME_BOSHINOGAMI,
                MonsterOfTheWeekEnum::CARDEA => UserStatEnum::RECEIVED_A_MINOR_PRIZE_FROM_CARDEAS_LOCKBEARER,
                MonsterOfTheWeekEnum::DIONYSUS => UserStatEnum::RECEIVED_A_MINOR_PRIZE_FROM_DIONYSUSS_HUNGER,
                MonsterOfTheWeekEnum::HUEHUECOYOTL => UserStatEnum::RECEIVED_A_MINOR_PRIZE_FROM_HUEHUECOYOTLS_FOLLY,
                default => throw new \Exception('Invalid monster: ' . $monster)
            },
            'Moderate' => match ($monster)
            {
                MonsterOfTheWeekEnum::ANHUR => UserStatEnum::RECEIVED_A_MODERATE_PRIZE_FROM_A_HUNTER_OF_ANHUR,
                MonsterOfTheWeekEnum::BOSHINOGAMI => UserStatEnum::RECEIVED_A_MODERATE_PRIZE_FROM_SOME_BOSHINOGAMI,
                MonsterOfTheWeekEnum::CARDEA => UserStatEnum::RECEIVED_A_MODERATE_PRIZE_FROM_CARDEAS_LOCKBEARER,
                MonsterOfTheWeekEnum::DIONYSUS => UserStatEnum::RECEIVED_A_MODERATE_PRIZE_FROM_DIONYSUSS_HUNGER,
                MonsterOfTheWeekEnum::HUEHUECOYOTL => UserStatEnum::RECEIVED_A_MODERATE_PRIZE_FROM_HUEHUECOYOTLS_FOLLY,
                default => throw new \Exception('Invalid monster: ' . $monster)
            },
            'Major' => match ($monster)
            {
                MonsterOfTheWeekEnum::ANHUR => UserStatEnum::RECEIVED_A_MAJOR_PRIZE_FROM_A_HUNTER_OF_ANHUR,
                MonsterOfTheWeekEnum::BOSHINOGAMI => UserStatEnum::RECEIVED_A_MAJOR_PRIZE_FROM_SOME_BOSHINOGAMI,
                MonsterOfTheWeekEnum::CARDEA => UserStatEnum::RECEIVED_A_MAJOR_PRIZE_FROM_CARDEAS_LOCKBEARER,
                MonsterOfTheWeekEnum::DIONYSUS => UserStatEnum::RECEIVED_A_MAJOR_PRIZE_FROM_DIONYSUSS_HUNGER,
                MonsterOfTheWeekEnum::HUEHUECOYOTL => UserStatEnum::RECEIVED_A_MAJOR_PRIZE_FROM_HUEHUECOYOTLS_FOLLY,
                default => throw new \Exception('Invalid monster: ' . $monster)
            }
        };
    }
}
