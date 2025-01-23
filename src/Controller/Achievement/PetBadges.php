<?php
namespace App\Controller\Achievement;

use App\Entity\User;
use App\Enum\PetBadgeEnum;
use App\Functions\SimpleDb;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/petBadges")]
final class PetBadges extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getPetBadges(ResponseService $responseService)
    {
        /** @var User $user */
        $user = $this->getUser();

        $badges = SimpleDb::createReadOnlyConnection()
            ->query(
                <<<EOSQL
                    SELECT COUNT(pet_badge_id) AS pets, MIN(pet_badge.claimed_on) AS claimedOn
                    FROM pet_badge
                    LEFT JOIN pet ON pet.id = pet_badge.pet_id
                    WHERE pet.owner_id = ?
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
                'firstClaimedOn' => null,
                'pets' => 0
            ];
        }

        foreach($badges as $badge)
        {
            $results[$resultIndexByBadge[$badge['badge']]] = [
                'badge' => $badge['badge'],
                'firstClaimedOn' => $badge['claimedOn'],
                'pets' => $badge['pets']
            ];
        }

        return $responseService->success($results);
    }
}