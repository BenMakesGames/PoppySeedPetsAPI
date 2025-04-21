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


namespace App\Controller\Dragon;

use App\Entity\User;
use App\Functions\DragonHelpers;
use App\Functions\RequestFunctions;
use App\Service\DragonService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route("/dragon")]
class MakeOfferingController extends AbstractController
{
    #[Route("/giveTreasure", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function giveTreasure(
        ResponseService $responseService, EntityManagerInterface $em,
        Request $request, DragonService $dragonService, NormalizerInterface $normalizer
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $itemIds = RequestFunctions::getUniqueIdsOrThrow($request, 'treasure', 'No items were selected to give???');

        $message = $dragonService->giveTreasures($user, $itemIds);

        $responseService->addFlashMessage($message);

        $dragon = DragonHelpers::getAdultDragon($em, $user);

        return $responseService->success(DragonHelpers::createDragonResponse($em, $normalizer, $user, $dragon));
    }
}
