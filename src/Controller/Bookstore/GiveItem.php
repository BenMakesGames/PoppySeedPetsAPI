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


namespace App\Controller\Bookstore;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\BookstoreService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Attribute\Route;

// allows player to buy books; inventory grows based on various criteria

#[Route("/bookstore")]
class GiveItem extends AbstractController
{
    #[Route("/giveItem/{item}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function giveItem(
        string $item, BookstoreService $bookstoreService, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Bookstore))
            throw new PSPNotUnlockedException('Bookstore');

        $bookstoreService->advanceBookstoreQuest($user, $item);

        $em->flush();

        $data = $bookstoreService->getResponseData($user);

        $responseService->addFlashMessage('Thanks! Renaming Scrolls now cost ' . $bookstoreService->getRenamingScrollCost($user) . '~~m~~!');

        return $responseService->success($data, [ SerializationGroupEnum::MARKET_ITEM ]);
    }
}
