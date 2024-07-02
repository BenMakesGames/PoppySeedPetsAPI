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
                SELECT monster.id,monster.monster,contribution.points,contribution.rewards_claimed
                FROM monster_of_the_week AS monster
                LEFT JOIN monster_of_the_week_contribution AS contribution ON contribution.monster_of_the_week_id=monster.id AND contribution.user_id=?
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
            throw new PSPNotFoundException("No Monster of the Week is available.");

        return $responseService->success($data[0]);
    }
}
