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

use App\Entity\Inventory;
use App\Entity\MonsterOfTheWeek;
use App\Entity\MonsterOfTheWeekContribution;
use App\Enum\LocationEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Service\Clock;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/monsterOfTheWeek")]
class ContributeController
{
    #[Route("/{monsterId}/contribute", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function makeContribution(
        int $monsterId, Request $request, ResponseService $responseService, EntityManagerInterface $em,
        Clock $clock,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $monster = $em->getRepository(MonsterOfTheWeek::class)->findOneBy([
            'id' => $monsterId
        ]);

        if(!$monster->isCurrent($clock->now))
            throw new PSPInvalidOperationException("It is not the time for this spirit! (Reload and try again?)");

        $itemIds = $request->get('items', []);

        if(!is_array($itemIds) || count($itemIds) == 0)
            throw new PSPInvalidOperationException('You must select at least one item!');

        if(count($itemIds) > 100)
            throw new PSPInvalidOperationException('Please contribute only up to 100 items at a time; thanks!');

        $items = $em->getRepository(Inventory::class)->findBy([
            'id' => $itemIds,
            'owner' => $user,
            'location' => LocationEnum::Home
        ]);

        if(count($items) < count($itemIds))
            throw new PSPInvalidOperationException('Could not find one or more of the selected items. (Reload and try again?)');

        $totalPoints = 0;

        foreach($items as $item)
        {
            $points = MonsterOfTheWeekHelpers::getItemValue($monster->getMonster(), $item->getItem());

            $em->remove($item);

            if($points < 1)
                throw new PSPInvalidOperationException('The spirit is not interested in one or more of the selected items! (Reload and try again?)');

            $totalPoints += $points;
        }

        $contribution = $em->getRepository(MonsterOfTheWeekContribution::class)->findOneBy([
            'monsterOfTheWeek' => $monster,
            'user' => $user
        ]);

        if($contribution === null)
        {
            $contribution = (new MonsterOfTheWeekContribution())
                ->setMonsterOfTheWeek($monster)
                ->setUser($user);

            $em->persist($contribution);
        }

        $contribution->addPoints($totalPoints);

        $em->flush();

        $em
            ->createQuery("UPDATE App\\Entity\\MonsterOfTheWeek AS m SET m.communityTotal = m.communityTotal + :points WHERE m.id = :id")
            ->execute([ 'points' => $totalPoints, 'id' => $monster->getId() ]);

        return $responseService->success([
            'personalContribution' => $contribution->getPoints(),
            'communityTotal' => $monster->getCommunityTotal() + $totalPoints
        ]);
    }
}
