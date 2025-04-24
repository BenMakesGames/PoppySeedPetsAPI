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

use App\Entity\Dragon;
use App\Entity\Inventory;
use App\Entity\User;
use App\Exceptions\PSPNotFoundException;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/dragonPolymorphPotion")]
class DragonPolymorphPotionController extends AbstractController
{
    #[Route("/{inventory}/give", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function drink(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, IRandom $rng
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'dragonPolymorphPotion/#/give');

        $dragon = $em->getRepository(Dragon::class)->findOneBy([ 'owner' => $user ]);

        if(!$dragon)
            throw new PSPNotFoundException('You don\'t know any dragons to give the potion to...');

        if(!$dragon->getIsAdult())
            throw new PSPNotFoundException('Your fireplace dragon, ' . $dragon->getName() . ', is too young to drink. Potions.');

        $em->remove($inventory);

        $currentAppearance = $dragon->getAppearance();

        $availableAppearances = array_filter(
            Dragon::APPEARANCE_IMAGES,
            fn(int $image) => $image !== $currentAppearance
        );

        $dragon->setAppearance($rng->rngNextFromArray($availableAppearances));

        $em->flush();

        $responseService->addFlashMessage($dragon->getName() . '\'s physical form has changed!');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
