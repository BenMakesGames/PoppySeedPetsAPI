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


namespace App\Controller\Market;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Functions\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\MarketService;
use App\Service\MuseumService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/market")]
class GetWingedKeyController extends AbstractController
{
    #[Route("/getWingedKey", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getWingedKey(
        ResponseService $responseService, MarketService $marketService, MuseumService $museumService,
        InventoryService $inventoryService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$marketService->canOfferWingedKey($user))
            throw new AccessDeniedHttpException();

        UserQuestRepository::findOrCreate($em, $user, 'Received Winged Key', false)
            ->setValue(true)
        ;

        $comment = 'Begrudgingly given to ' . $user->getName() . ' by Argentelle.';

        $museumService->forceDonateItem($user, 'Winged Key', $comment);

        $inventoryService->receiveItem('Winged Key', $user, null, $comment, LocationEnum::HOME, true);

        $em->flush();

        return $responseService->success();
    }
}
