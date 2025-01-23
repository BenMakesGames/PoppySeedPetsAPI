<?php
namespace App\Controller\Achievement;

use App\Entity\User;
use App\Enum\PetBadgeEnum;
use App\Functions\SimpleDb;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/achievement")]
final class PetBadges extends AbstractController
{
    /**
     * @Route("/petBadges", methods={"GET"})
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getPetBadges(ResponseService $responseService)
    {
        /** @var User $user */
        $user = $this->getUser();

        $badges = SimpleDb::createReadOnlyConnection()
            ->query(
                <<<EOSQL
                    SELECT COUNT(pet_badge.id) AS pets, MIN(pet_badge.date_acquired) AS firstAchievedOn, pet_badge.badge
                    FROM pet_badge
                    LEFT JOIN pet ON pet.id = pet_badge.pet_id
                    WHERE pet.owner_id = ?
                    GROUP BY pet_badge.badge
                EOSQL,
                [ $user->getId() ]
            )
            ->getResults();

        $results = [];
        $resultIndexByBadge = [];

        foreach(PetBadgeEnum::getValues() as $badge)
        {
            $resultIndexByBadge[$badge] = count($results);
            $results[] = [
                'badge' => $badge,
                'firstAchievedOn' => null,
                'pets' => 0
            ];
        }

        foreach($badges as $badge)
        {
            $results[$resultIndexByBadge[$badge['badge']]] = [
                'badge' => $badge['badge'],
                'firstAchievedOn' => $badge['firstAchievedOn'],
                'pets' => $badge['pets']
            ];
        }

        return $responseService->success($results);
    }
}