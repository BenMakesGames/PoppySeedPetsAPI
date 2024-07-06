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
class GetController extends AbstractController
{
    #[Route("/current", methods: ["GET"])]
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
                    easy_prize.name AS easy_prize_name,
                    medium_prize.name AS medium_prize_name,
                    hard_prize.name AS hard_prize_name,
                    contribution.points
                FROM monster_of_the_week AS monster
                LEFT JOIN monster_of_the_week_contribution AS contribution ON contribution.monster_of_the_week_id=monster.id AND contribution.user_id=?
                LEFT JOIN item AS easy_prize ON easy_prize.id=monster.easy_prize_id
                LEFT JOIN item AS medium_prize ON medium_prize.id=monster.medium_prize_id
                LEFT JOIN item AS hard_prize ON hard_prize.id=monster.hard_prize_id
                WHERE ? BETWEEN monster.start_date AND monster.end_date
                LIMIT 1
            ",
            [
                $user->getId(),
                date("Y-m-d")
            ]
        );

        $data = $query->getResults();

        if(count($data) == 0)
            throw new PSPNotFoundException("No spirit was found...");

        $milestones = MonsterOfTheWeekHelpers::getBasePrizeValues($data[0]['monster']);

        return $responseService->success([
            'id' => $data[0]['id'],
            'type' => $data[0]['monster'],
            'communityTotal' => $data[0]['community_total'],
            'personalContribution' => $data[0]['points'],
            'milestones' => [
                [
                    'value' => $milestones[0] * $data[0]['level'],
                    'prize' => $data[0]['easy_prize_name']
                ],
                [
                    'value' => $milestones[1] * $data[0]['level'],
                    'prize' => $data[0]['medium_prize_name']
                ],
                [
                    'value' => $milestones[2] * $data[0]['level'],
                    'prize' => $data[0]['hard_prize_name']
                ]
            ]
        ]);
    }
}
