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
use App\Enum\LocationEnum;
use App\Enum\PollinatorEnum;
use App\Exceptions\PSPNotFoundException;
use App\Functions\PlayerLogFactory;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/greenhouse")]
class PullUpPlantController
{
    #[Route("/{plant}/pullUp", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function pullUpPlant(
        GreenhousePlant $plant, ResponseService $responseService, EntityManagerInterface $em, IRandom $rng,
        InventoryService $inventoryService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if($plant->getOwner()->getId() !== $user->getId())
            throw new PSPNotFoundException('That plant does not exist.');

        $logMessage = 'You pulled up the ' . $plant->getPlant()->getName() . '.';

        if($plant->getPlant()->getName() === 'Magic Beanstalk')
        {
            $flashMessage = 'Pulling up the stalk is surprisingly easy, but perhaps more surprising, you find yourself holding Magic Beans, instead of a stalk!';
            $logMessage .= ' ' . $flashMessage;
            $responseService->addFlashMessage($flashMessage);

            $inventoryService->receiveItem('Magic Beans', $user, $user, 'Received by pulling up a Magic Beanstalk, apparently. Magically.', LocationEnum::Home);
        }
        else if($plant->getPlant()->getName() === 'Midnight Arch')
        {
            $flashMessage = 'Pulling up the arch is surprisingly easy, but perhaps more surprising, you find yourself a Mysterious Seed, instead of a stalk!';
            $logMessage .= ' ' . $flashMessage;
            $responseService->addFlashMessage($flashMessage);

            $inventoryService->receiveItem('Mysterious Seed', $user, $user, 'Received by pulling up a Midnight Arch, apparently. Magically.', LocationEnum::Home);
        }
        else if($plant->getPlant()->getName() === 'Goat' && $plant->getIsAdult())
        {
            $flashMessage = 'The goat, startled, runs into the jungle, shedding a bit of Fluff in the process.';
            $logMessage .= ' ' . $flashMessage;
            $responseService->addFlashMessage($flashMessage);

            $inventoryService->receiveItem('Fluff', $user, $user, 'Dropped by a startled goat.', LocationEnum::Home);
            if($rng->rngNextInt(1, 2) === 1)
                $inventoryService->receiveItem('Fluff', $user, $user, 'Dropped by a startled goat.', LocationEnum::Home);
        }

        PlayerLogFactory::create($em, $user, $logMessage, [ 'Greenhouse' ]);

        $pollinators = $plant->getPollinators();

        if($pollinators === PollinatorEnum::Butterflies)
            $user->getGreenhouse()->setButterfliesDismissedOn(new \DateTimeImmutable());
        if($pollinators === PollinatorEnum::Bees1)
            $user->getGreenhouse()->setBeesDismissedOn(new \DateTimeImmutable());
        if($pollinators === PollinatorEnum::Bees2)
            $user->getGreenhouse()->setBees2DismissedOn(new \DateTimeImmutable());

        $em->remove($plant);
        $em->flush();

        return $responseService->success();
    }
}
