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
use App\Enum\LocationEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\GrammarFunctions;
use App\Functions\InventoryModifierFunctions;
use App\Functions\SpiceRepository;
use App\Functions\UserQuestRepository;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/hotPot")]
class HotPotController
{
    #[Route("/{inventory}/dip", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function dipAFood(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, IRandom $rng,
        Request $request, UserStatsService $userStatsRepository, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'hotPot');

        $itemId = $request->request->getInt('food', 0);

        if($itemId <= 0)
            throw new PSPFormValidationException('You forgot to select a food!');

        $dippedItem = $em->getRepository(Inventory::class)->findOneBy([
            'id' => $itemId,
            'owner' => $user,
            'location' => LocationEnum::Home
        ]);

        if(!$dippedItem)
            throw new PSPNotFoundException('Could not find that item!? Reload, and try again...');

        if(!$dippedItem->getItem()->getFood())
            throw new PSPInvalidOperationException('That item is not a food! Dipping it into the Hot Pot would accomplish NOTHING.');

        $today = (new \DateTimeImmutable())->format('Y-m-d');
        $usedHotPot = UserQuestRepository::findOrCreate($em, $user, 'Used Hot Pot', (new \DateTimeImmutable())->modify('-1 day')->format('Y-m-d'));

        if($today === $usedHotPot->getValue())
            throw new PSPInvalidOperationException('You already dipped something into a Hot Pot today. You\'ll just have to wait for tomorrow!');

        $usedHotPot->setValue($today);

        $dippingStat = $userStatsRepository->incrementStat($user, UserStatEnum::FoodsDippedInAHotPot);

        // Hot Pot-only spices
        $possibleSpices = [
            'Sichuan', 'Salty', 'Meaty',
            '5-Spice\'d', 'with Sesame Seeds'
        ];

        if($dippingStat->getValue() > 1)
        {
            // other spices:
            $possibleSpices[] = 'Spicy';
            $possibleSpices[] = 'Onion\'d';
            $possibleSpices[] = 'Fishy';
        }

        if($dippedItem->getSpice())
        {
            $possibleSpices = array_filter($possibleSpices, fn(string $bonus) =>
                $bonus !== $dippedItem->getSpice()->getName()
            );
        }

        $newSpice = SpiceRepository::findOneByName($em, $rng->rngNextFromArray($possibleSpices));

        $hadASpice = $dippedItem->getSpice() !== null;
        $oldName = InventoryModifierFunctions::getNameWithModifiers($dippedItem);

        $dippedItem
            ->setSpice($newSpice)
            ->addComment('This item gained "' . $newSpice->getName() . '" from a Hot Pot.')
        ;

        $newName = InventoryModifierFunctions::getNameWithModifiers($dippedItem);

        $em->flush();

        if($hadASpice)
            $responseService->addFlashMessage('The ' . $oldName . '\'s spice was replaced! It is now ' . GrammarFunctions::indefiniteArticle($newName) . ' ' . $newName . '!');
        else
            $responseService->addFlashMessage('The ' . $oldName . ' has been spiced! It is now ' . GrammarFunctions::indefiniteArticle($newName) . ' ' . $newName . '!');

        return $responseService->success();
    }
}
