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


namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\PetSpecies;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetLocationEnum;
use App\Enum\PetSpeciesName;
use App\Exceptions\PSPNotFoundException;
use App\Functions\MeritRepository;
use App\Functions\PetRepository;
use App\Functions\PetSpeciesRepository;
use App\Service\IRandom;
use App\Service\PetFactory;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/philosophersStone")]
class PhilosophersStoneController
{
    // should be kept in-sync with the list in philosophers-stone.component.ts
    // (or make a new endpoint to get eligible plushies... that'd be cool)
    private const array PLUSHIES = [
        'Bulbun Plushy' => [ 'species' => PetSpeciesName::Bulbun, 'colorA' => 'f8d592', 'colorB' => 'd4b36e' ],
        'Peacock Plushy' => [ 'species' => PetSpeciesName::Peacock, 'colorA' => 'ffe9d9', 'colorB' => 'a47dd7' ],
        'Rainbow Dolphin Plushy' => [ 'species' => PetSpeciesName::RainbowDolphin, 'colorA' => '64ea74', 'colorB' => 'ea64de' ],
        'Sneqo Plushy' => [ 'species' => PetSpeciesName::Sneqo, 'colorA' => '269645', 'colorB' => 'c8bb67' ],
        'Phoenix Plushy' => [ 'species' => PetSpeciesName::Phoenix, 'colorA' => 'b03d3d', 'colorB' => 'f5e106' ],
        'Dancing Sword' => [ 'species' => PetSpeciesName::DancingSword, 'colorA' => '846b38', 'colorB' => '843838' ],
    ];

    #[Route("/{inventory}/use", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function useStone(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, IRandom $rng,
        PetFactory $petFactory, Request $request, UserStatsService $userStatsRepository,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'philosophersStone');

        $itemId = $request->request->getInt('plushy');

        $plushy = $em->getRepository(Inventory::class)->findOneBy([
            'id' => $itemId,
            'owner' => $user,
            'location' => LocationEnum::HOME
        ]);

        if(!$plushy || !array_key_exists($plushy->getItem()->getName(), self::PLUSHIES))
            throw new PSPNotFoundException('Could not find that item!? Reload, and try again...');

        $speciesInfo = self::PLUSHIES[$plushy->getItem()->getName()];

        $species = PetSpeciesRepository::findOneByName($em, $speciesInfo['species']);

        $userStatsRepository->incrementStat($user, 'Philosopher\'s Stones Used');

        $message = 'The ' . $plushy->getFullItemName() . ' has been brought to life!';

        $em->remove($plushy);
        $em->remove($inventory);

        $name = $rng->rngNextFromArray([
            'Perenelle', 'Ostanes', 'Nicolas', 'Hermes',
            'Chymes', 'Zosimos', 'Paphnutia', 'Arephius',
            'Paracelsus', 'Vallalar', 'Kanada', 'Laozi',
        ]);

        $startingMerit = MeritRepository::findOneByName($em, MeritEnum::ETERNAL);

        $pet = $petFactory->createPet(
            $user,
            $name,
            $species,
            $speciesInfo['colorA'],
            $speciesInfo['colorB'],
            $rng->rngNextFromArray(FlavorEnum::cases()),
            $startingMerit
        );

        $numberOfPetsAtHome = PetRepository::getNumberAtHome($em, $user);

        if($numberOfPetsAtHome >= $user->getMaxPets())
        {
            $pet->setLocation(PetLocationEnum::DAYCARE);
            $message .= ' Since the house was already full, it went to the daycare.';
        }

        $em->flush();

        $responseService->addFlashMessage($message);

        return $responseService->success();
    }
}
