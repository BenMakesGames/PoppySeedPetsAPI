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
use App\Entity\User;
use App\Enum\PlantTypeEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ArrayFunctions;
use App\Repository\InventoryRepository;
use App\Service\GreenhouseService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/greenhouse")]
class PlantSeedController extends AbstractController
{
    #[Route("/plantSeed", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function plantSeed(
        ResponseService $responseService, InventoryRepository $inventoryRepository, Request $request,
        EntityManagerInterface $em, GreenhouseService $greenhouseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();
        $greenhouse = $user->getGreenhouse();

        if($greenhouse === null)
            throw new PSPNotUnlockedException('Greenhouse');

        $seedId = $request->request->getInt('seed', 0);

        if($seedId <= 0)
            throw new PSPFormValidationException('"seed" is missing, or invalid.');

        $seed = $inventoryRepository->findOneBy([
            'id' => $seedId,
            'owner' => $user->getId(),
            'location' => Inventory::CONSUMABLE_LOCATIONS,
        ]);

        if($seed === null || $seed->getItem()->getPlant() === null)
            throw new PSPNotFoundException('There is no such seed. That\'s super-weird. Can you reload and try again?');

        $largestOrdinal = ArrayFunctions::max($user->getGreenhousePlants(), fn(GreenhousePlant $gp) => $gp->getOrdinal());
        $lastOrdinal = $largestOrdinal === null ? 1 : ($largestOrdinal->getOrdinal() + 1);

        $plantsOfSameType = $user->getGreenhousePlants()->filter(fn(GreenhousePlant $plant) =>
            $plant->getPlant()->getType() === $seed->getItem()->getPlant()->getType()
        );

        $numberOfPlots = match ($seed->getItem()->getPlant()->getType())
        {
            PlantTypeEnum::EARTH => $greenhouse->getMaxPlants(),
            PlantTypeEnum::WATER => $greenhouse->getMaxWaterPlants(),
            PlantTypeEnum::DARK => $greenhouse->getMaxDarkPlants(),
            default => throw new \Exception('Selected item doesn\'t have a valid plant type! Someone let Ben know he messed up!'),
        };

        if(count($plantsOfSameType) >= $numberOfPlots)
            throw new PSPInvalidOperationException('You can\'t plant anymore plants of this type.');

        $plant = (new GreenhousePlant())
            ->setOwner($user)
            ->setPlant($seed->getItem()->getPlant())
            ->setOrdinal($lastOrdinal + 1)
        ;

        $em->persist($plant);
        $em->remove($seed);
        $em->flush();

        return $responseService->success(
            $greenhouseService->getGreenhouseResponseData($user),
            [ SerializationGroupEnum::GREENHOUSE_PLANT, SerializationGroupEnum::MY_GREENHOUSE, SerializationGroupEnum::HELPER_PET ]
        );
    }
}
