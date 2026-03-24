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

namespace App\Controller\Encyclopedia;

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\PetSpecies;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPNotFoundException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/encyclopedia")]
class GetSpeciesController
{
    #[DoesNotRequireHouseHours]
    #[Route("/species/{speciesName}", methods: ["GET"])]
    public function getSpeciesByName(
        string $speciesName, EntityManagerInterface $em, ResponseService $responseService
    ): JsonResponse
    {
        $species = $em->getRepository(PetSpecies::class)->findOneBy([ 'name' => $speciesName ]);

        if(!$species)
            throw new PSPNotFoundException('There is no such species.');

        return $responseService->success($species, [ SerializationGroupEnum::PET_ENCYCLOPEDIA ]);
    }
}
