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


namespace App\Controller\Trader;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\UserQuestRepository;
use App\Service\FieldGuideService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\TraderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/trader")]
class MakeExchangeController extends AbstractController
{
    #[Route("/{id}/exchange", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function makeExchange(
        string $id, TraderService $traderService, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService, Request $request, FieldGuideService $fieldGuideService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Trader))
            throw new PSPNotUnlockedException('Trader');

        $quantity = $request->request->getInt('quantity', 1);

        if($quantity < 1)
            throw new PSPFormValidationException('Quantity must be 1, or more.');

        $exchange = $traderService->getOfferById($user, $id);

        if(!$exchange)
            throw new PSPNotFoundException('There is no such exchange available.');

        if($quantity > $exchange->canMakeExchange)
            throw new PSPInvalidOperationException('You only have the stuff to do this exchange up to ' . $exchange->canMakeExchange . ' times.');

        if(!$traderService->userCanMakeExchange($user, $exchange, LocationEnum::HOME))
            throw new PSPNotFoundException('The items you need to make this exchange could not be found in your house.');

        $traderService->makeExchange($user, $exchange, LocationEnum::HOME, $quantity);

        $message = null;

        $now = new \DateTimeImmutable();

        // october
        if((int)$now->format('n') === 10)
        {
            $quest = UserQuestRepository::findOrCreate($em, $user, 'Get October ' . $now->format('Y') . ' Behatting Scroll', false);
            if($quest->getValue() === false)
            {
                $quest->setValue(true);
                $inventoryService->receiveItem('Behatting Scroll', $user, null, 'The Trader gave you this, for Halloween.', LocationEnum::HOME, true);

                $message = 'Oh, and here, have a Behatting Scroll. It\'ll come in handy for Halloween, trust me!';
            }
        }

        $fieldGuideService->maybeUnlock($user, 'Tell Samarzhoustia', '%user:' . $user->getId() . '.Name% made an exchange with a trader from Tell Samarzhoustia.');

        $em->flush();

        $offers = $traderService->getOffers($user);

        return $responseService->success(
            [
                'message' => $message,
                'trades' => $offers
            ],
            [ SerializationGroupEnum::TRADER_OFFER, SerializationGroupEnum::MARKET_ITEM ]
        );
    }
}
