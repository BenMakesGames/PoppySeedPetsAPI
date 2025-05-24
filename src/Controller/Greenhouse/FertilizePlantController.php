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


namespace App\Controller\Greenhouse;

use App\Entity\GreenhousePlant;
use App\Entity\Inventory;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\GrammarFunctions;
use App\Functions\PlayerLogFactory;
use App\Service\GreenhouseService;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/greenhouse")]
class FertilizePlantController
{
    #[Route("/{plant}/fertilize", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function fertilizePlant(
        GreenhousePlant $plant, ResponseService $responseService, Request $request, EntityManagerInterface $em,
        UserStatsService $userStatsRepository, GreenhouseService $greenhouseService, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if($plant->getOwner()->getId() !== $user->getId())
            throw new PSPNotFoundException('That plant does not exist.');

        if(new \DateTimeImmutable() < $plant->getCanNextInteract())
            throw new PSPInvalidOperationException('This plant is not yet ready to fertilize.');

        $fertilizerId = $request->request->getInt('fertilizer', 0);

        $fertilizer = $em->getRepository(Inventory::class)->findOneBy([
            'id' => $fertilizerId,
            'owner' => $user->getId(),
            'location' => Inventory::ConsumableLocations,
        ]);

        if(!$fertilizer || $fertilizer->getTotalFertilizerValue() <= 0)
            throw new PSPFormValidationException('A fertilizer must be selected.');

        $plant->fertilize($fertilizer);

        $userStatsRepository->incrementStat($user, UserStatEnum::FertilizedAPlant);

        $plantNameArticle = GrammarFunctions::indefiniteArticle($plant->getPlant()->getName());

        PlayerLogFactory::create(
            $em,
            $user,
            "You fertilized $plantNameArticle {$plant->getPlant()->getName()} plant with {$fertilizer->getFullItemName()}.",
            [ 'Greenhouse' ]
        );

        $em->remove($fertilizer);
        $em->flush();

        return $responseService->success(
            $greenhouseService->getGreenhouseResponseData($user),
            [
                SerializationGroupEnum::GREENHOUSE_PLANT,
                SerializationGroupEnum::MY_GREENHOUSE,
                SerializationGroupEnum::HELPER_PET
            ]
        );
    }
}
