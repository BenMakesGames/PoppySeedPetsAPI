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


namespace App\Controller\Achievement;

use App\Entity\User;
use App\Entity\UserBadge;
use App\Enum\BadgeEnum;
use App\Enum\CostOrYieldTypeEnum;
use App\Enum\LocationEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Functions\InMemoryCache;
use App\Model\TraderOfferCostOrYield;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\TransactionService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/achievement")]
final class Claim
{
    #[Route("/claim", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function claim(
        ResponseService $responseService, Request $request, EntityManagerInterface $em,
        UserStatsService $userStatsRepository, InventoryService $inventoryService,
        TransactionService $transactionService, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $badge = mb_trim($request->request->getString('achievement'));

        if(!$badge || !BadgeEnum::isAValue($badge))
            throw new PSPFormValidationException('Which achievement?');

        $progress = BadgeHelpers::getBadgeProgress($badge, $user, $em, new InMemoryCache());

        if(!$progress['done'])
            throw new PSPInvalidOperationException('You are not eligible to claim that achievement.');

        $badge = new UserBadge(user: $user, badge: $badge);

        $em->persist($badge);

        $userStatsRepository->incrementStat($user, UserStatEnum::ACHIEVEMENTS_CLAIMED);

        self::getAchievementReward($user, $progress['reward'], $inventoryService, $transactionService);

        $em->flush();

        $celebration = $rng->rngNextFromArray([
            [ 'Achievement get!', '!' ],
            [ 'Yippee!', '!' ],
            [ 'By jove, you\'ve done it!', '!' ],
            [ 'Great googly-moogly!', '!' ],
            [ 'Da-da-da-daaaaaa!', '!' ],
            [ 'Hot diggity!', '!' ],
            [ 'ooooommmmmgggggggggg!', '!!1!' ],
            [ 'Congrats!', '!' ],
            [ 'Well I\'ll be a Star Monkey\'s uncle!', '!' ],
            [ 'Cha-ching!', ' $_$' ],
            [ 'Coo\'... coo\'...', '.' ],
            [ 'Whaaaaaaaat?!?!', '!?!?' ],
            [ 'Say _what_ now?', '???' ],
            [ 'Sweet, fancy Moses!', '!' ],
            [ '\\*gasp\\*!', '!' ],
            [ 'Oh, hey, so this is neat:', '!' ],
            [ 'Yatta!', ' ^\\_^' ]
        ]);

        $responseService->addFlashMessage($celebration[0] . ' You received ' . self::describeYield($progress['reward']) . $celebration[1]);

        return $responseService->success();
    }

    private static function describeYield(TraderOfferCostOrYield $yield): string
    {
        return match ($yield->type)
        {
            CostOrYieldTypeEnum::ITEM => $yield->quantity . '× ' . $yield->item->getName(),
            CostOrYieldTypeEnum::MONEY => $yield->quantity . '~~m~~',
            CostOrYieldTypeEnum::RECYCLING_POINTS => $yield->quantity . ' Recycling Points',
            default => throw new \Exception('Unexpected reward type "' . $yield->type . '"!? Weird! Ben should fix this!'),
        };
    }

    private static function getAchievementReward(
        User $user, TraderOfferCostOrYield $yield, InventoryService $inventoryService,
        TransactionService $transactionService
    ): void
    {
        switch($yield->type)
        {
            case CostOrYieldTypeEnum::ITEM:
                for($i = 0; $i < $yield->quantity; $i++)
                    $inventoryService->receiveItem($yield->item, $user, $user, 'Received by claiming an Achievement.', LocationEnum::HOME, true);

                break;

            case CostOrYieldTypeEnum::MONEY:
                $transactionService->getMoney($user, $yield->quantity, 'Received by claiming an Achievement.');
                break;

            case CostOrYieldTypeEnum::RECYCLING_POINTS:
                $transactionService->getRecyclingPoints($user, $yield->quantity, 'Received by claiming an Achievement.');
                break;

            default:
                throw new \Exception('Unexpected reward type "' . $yield->type . '"!? Weird! Ben should fix this!');
        }
    }
}