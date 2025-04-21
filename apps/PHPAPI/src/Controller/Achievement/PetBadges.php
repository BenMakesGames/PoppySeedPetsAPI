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


namespace App\Controller\Achievement;

use App\Entity\User;
use App\Enum\PetBadgeEnum;
use App\Functions\SimpleDb;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/achievement")]
final class PetBadges extends AbstractController
{
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/petBadges", methods: ["GET"])]
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