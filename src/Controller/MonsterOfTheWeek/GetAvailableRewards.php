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

use App\Entity\User;
use App\Enum\MonsterOfTheWeekEnum;
use App\Exceptions\PSPNotFoundException;
use App\Functions\SimpleDb;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
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
                    monster.level,
                    monster.community_total,
                    monster.start_date,
                    monster.end_date,
                    easy_prize.name AS easy_prize_name,
                    easy_prize.image AS easy_prize_image,
                    medium_prize.name AS medium_prize_name,
                    medium_prize.image AS medium_prize_image,
                    hard_prize.name AS hard_prize_name,
                    hard_prize.image AS hard_prize_image,
                    contribution.points,
                    contribution.rewards_claimed
                FROM monster_of_the_week AS monster
                LEFT JOIN monster_of_the_week_contribution AS contribution ON contribution.monster_of_the_week_id=monster.id AND contribution.user_id=?
                LEFT JOIN item AS easy_prize ON easy_prize.id=monster.easy_prize_id
                LEFT JOIN item AS medium_prize ON medium_prize.id=monster.medium_prize_id
                LEFT JOIN item AS hard_prize ON hard_prize.id=monster.hard_prize_id
                WHERE contribution.rewards_claimed = 0
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
            $milestones = MonsterOfTheWeekHelpers::getBasePrizeValues(MonsterOfTheWeekEnum::from($monster['monster']));

            $monsters[] = [
                'id' => $monster['id'],
                'type' => $monster['monster'],
                'level' => $monster['level'],
                'communityTotal' => $monster['community_total'],
                'personalContribution' => $monster['points'],
                'startDate' => $monster['start_date'],
                'endDate' => $monster['end_date'],
                'rewardsClaimed' => (bool)$monster['rewards_claimed'],
                'milestones' => [
                    [
                        'value' => $milestones[0] * $monster['level'],
                        'item' => [
                            'name' => $monster['easy_prize_name'],
                            'image' => $monster['easy_prize_image'],
                        ],
                    ],
                    [
                        'value' => $milestones[1] * $monster['level'],
                        'item' => [
                            'name' => $monster['medium_prize_name'],
                            'image' => $monster['medium_prize_image'],
                        ],
                    ],
                    [
                        'value' => $milestones[2] * $monster['level'],
                        'item' => [
                            'name' => $monster['hard_prize_name'],
                            'image' => $monster['hard_prize_image'],
                        ],
                    ]
                ]
            ];
        }

        return $responseService->success($monsters);
    }
}
