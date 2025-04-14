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
use App\Entity\Merit;
use App\Entity\PetSpecies;
use App\Entity\User;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetLocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ItemRepository;
use App\Functions\MeritRepository;
use App\Functions\PetRepository;
use App\Repository\InventoryRepository;
use App\Service\IRandom;
use App\Service\PetFactory;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/betaBug")]
class BetaBugController extends AbstractController
{
    private const ALLOWED_ITEMS = [
        'Cooking Buddy',
        'Cooking "Alien"',
        'Cooking... with Fire',
        'Mini Cooking Buddy',
        'Mega Cooking Buddy',
        'Sentient Beetle',
        'Rainbowsaber'
    ];

    #[Route("/{inventory}/eligibleItems", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getEligibleItems(Inventory $inventory, ResponseService $responseService, InventoryRepository $inventoryRepository)
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'betaBug');

        $qb = $inventoryRepository->createQueryBuilder('i');
        $items = $qb
            ->join('i.item', 'item')
            ->andWhere('i.owner=:ownerId')
            ->andWhere('item.name IN (:allowedItemNames)')
            ->andWhere('i.location=:home')
            ->setParameter('ownerId', $user->getId())
            ->setParameter('allowedItemNames', self::ALLOWED_ITEMS)
            ->setParameter('home', LocationEnum::HOME)
            ->getQuery()
            ->execute();

        return $responseService->success($items, [ SerializationGroupEnum::MY_INVENTORY ]);
    }

    #[Route("/{inventory}/use", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function useBug(
        Inventory $inventory, Request $request, InventoryRepository $inventoryRepository,
        ResponseService $responseService, EntityManagerInterface $em, PetFactory $petFactory, IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'betaBug');

        $item = $inventoryRepository->findOneBy([
            'id' => $request->request->getInt('item'),
            'owner' => $user,
            'location' => LocationEnum::HOME
        ]);

        if(!$item)
            throw new PSPNotFoundException("Couldn't find that item!");

        switch($item->getItem()->getName())
        {
            case 'Cooking Buddy': self::createCookingBuddy($responseService, $em, $petFactory, $rng, $item, $user, MeritRepository::getRandomStartingMerit($em, $rng), FlavorEnum::getRandomValue($rng), null, 'd8d8d8', 100); break;
            case 'Cooking "Alien"': self::createCookingBuddy($responseService, $em, $petFactory, $rng, $item, $user, MeritRepository::findOneByName($em, MeritEnum::BEHATTED), FlavorEnum::getRandomValue($rng), 'Antenna', 'd8d8d8', 100); break;
            case 'Cooking... with Fire': self::createCookingBuddy($responseService, $em, $petFactory, $rng, $item, $user, MeritRepository::getRandomStartingMerit($em, $rng), FlavorEnum::SPICY, null, '6e6e6e', 100); break;
            case 'Mini Cooking Buddy': self::createCookingBuddy($responseService, $em, $petFactory, $rng, $item, $user, MeritRepository::getRandomStartingMerit($em, $rng), FlavorEnum::getRandomValue($rng), null, 'd8d8d8', 60); break;
            case 'Mega Cooking Buddy': self::createCookingBuddy($responseService, $em, $petFactory, $rng, $item, $user, MeritRepository::getRandomStartingMerit($em, $rng), FlavorEnum::getRandomValue($rng), null, 'd8d8d8', 150); break;
            case 'Sentient Beetle': self::makeBeetleEvil($responseService, $em, $user, $item); break;
            case 'Rainbowsaber': self::makeGlitchedOutRainbowsaber($responseService, $em, $user, $item); break;
            default: throw new PSPInvalidOperationException("The Beta Bug cannot be used on that item!");
        }

        $em->remove($inventory);
        $em->flush();

        return $responseService->success();
    }

    private static function makeBeetleEvil(
        ResponseService $responseService, EntityManagerInterface $em,
        User $user, Inventory $beetle
    )
    {
        $beetle->changeItem(ItemRepository::findOneByName($em, 'EVIL Sentient Beetle'));
        $beetle->addComment($user->getName() . ' introduced a Beta Bug into the Sentient Beetle, turning it EVIL!');

        $responseService->addFlashMessage('Oh dang! Introducing a Beta Bug into the Sentient Beetle turned it EVIL!');
        $responseService->setReloadInventory();
    }

    private static function makeGlitchedOutRainbowsaber(
        ResponseService $responseService, EntityManagerInterface $em,
        User $user, Inventory $rainbowsaber
    )
    {
        $rainbowsaber->changeItem(ItemRepository::findOneByName($em, 'Glitched-out Rainbowsaber'));
        $rainbowsaber->addComment($user->getName() . ' introduced a Beta Bug into the Rainbowsaber, glitching it out!');

        $responseService->addFlashMessage('Oh dang! Introducing a Beta Bug into the Rainbowsaber made it all glitchy!');
        $responseService->setReloadInventory();
    }

    private static function createCookingBuddy(
        ResponseService $responseService, EntityManagerInterface $em, PetFactory $petFactory, IRandom $rng,
        Inventory $inventoryItem, User $user, Merit $startingMerit, string $favoriteFlavor, ?string $startingHatItem,
        string $bodyColor, int $scale
    )
    {
        $newPet = $petFactory->createPet(
            $user,
            $rng->rngNextFromArray(\App\Entity\CookingBuddy::NAMES),
            $em->getRepository(PetSpecies::class)->findOneBy([ 'name' => 'Cooking Buddy' ]),
            $bodyColor,
            '236924', // "eyes"
            $favoriteFlavor,
            $startingMerit
        );

        $newPet->setScale($scale);

        if($startingHatItem)
        {
            $inventory = (new Inventory(owner: $user, item: ItemRepository::findOneByName($em, $startingHatItem)))
                ->setCreatedBy($user)
                ->setLocation(LocationEnum::WARDROBE)
                ->setWearer($newPet)
            ;

            $em->persist($inventory);
        }

        $em->remove($inventoryItem);

        $numberOfPetsAtHome = PetRepository::getNumberAtHome($em, $user);

        $petJoinsHouse = $numberOfPetsAtHome < $user->getMaxPets();

        $message = 'The Cooking Buddy starts to shake violently, then shuts down. After a moment of silence, it starts back up again, nuzzles you, and joins the rest of your pets in your house!';

        if(!$petJoinsHouse)
        {
            $newPet->setLocation(PetLocationEnum::DAYCARE);
            $message .= "\n\n(Seeing your house is full, it shortly thereafter wanders to daycare.)";
        }
        else
        {
            $responseService->setReloadPets(true);
        }

        $responseService->addFlashMessage($message);
    }
}