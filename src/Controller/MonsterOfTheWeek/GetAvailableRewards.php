<?php
namespace App\Controller\MonsterOfTheWeek;

use App\Entity\User;
use App\Exceptions\PSPNotFoundException;
use App\Functions\SimpleDb;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/monsterOfTheWeek")]
class GetAvailableRewards extends AbstractController
{
    #[Route("/rewards", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getCurrent(ResponseService $responseService)
    {
        /** @var User $user */
        $user = $this->getUser();

        $db = SimpleDb::createReadOnlyConnection();

        $query = $db->query(
            "
                SELECT
                    monster.id,
                    monster.monster,
                    monster.start_date,
                    monster.end_date,
                    easy_prize.name AS easy_prize_name,
                    medium_prize.name AS medium_prize_name,
                    hard_prize.name AS hard_prize_name,
                    contribution.points,
                    contribution.rewards_claimed
                FROM monster_of_the_week AS monster
                LEFT JOIN monster_of_the_week_contribution AS contribution ON contribution.monster_of_the_week_id=monster.id AND contribution.user_id=?
                LEFT JOIN item AS easy_prize ON easy_prize.id=monster.easy_prize_id
                LEFT JOIN item AS medium_prize ON medium_prize.id=monster.medium_prize_id
                LEFT JOIN item AS hard_prize ON hard_prize.id=monster.hard_prize_id
                ORDER BY monster.id DESC
                LIMIT 10
            ",
            [
                $user->getId()
            ]
        );

        $data = $query->getResults();

        $monsters = [];

        foreach($data as $monster)
        {
            $milestones = MonsterOfTheWeekHelpers::getBasePrizeValues($monster['monster']);

            $monsters[] = [
                'id' => $monster['id'],
                'type' => $monster['monster'],
                'startDate' => $monster['start_date'],
                'endDate' => $monster['end_date'],
                'progress' => $monster['points'],
                'rewardsClaimed' => (bool)$monster['rewards_claimed'],
                'milestones' => [
                    [
                        'value' => $milestones[0],
                        'prize' => $monster['easy_prize_name']
                    ],
                    [
                        'value' => $milestones[1],
                        'prize' => $monster['medium_prize_name']
                    ],
                    [
                        'value' => $milestones[2],
                        'prize' => $monster['hard_prize_name']
                    ]
                ]
            ];
        }

        return $responseService->success($monsters);
    }
}
