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


namespace App\Service;

use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Functions\UserFunctions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class HotPotatoService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ResponseService $responseService
    )
    {
    }

    public static function countTosses(Inventory $inventory): int
    {
        return self::countMoves($inventory, ' tossed this to ');
    }

    public static function countTeleports(Inventory $inventory): int
    {
        return self::countMoves($inventory, ' magically transported ');
    }

    private static function countMoves(Inventory $inventory, string $identifyingText): int
    {
        $numberOfTosses = 0;

        foreach($inventory->getComments() as $comment)
        {
            if(str_contains($comment, $identifyingText))
                $numberOfTosses++;
        }

        return $numberOfTosses;
    }

    public function tossItem(Inventory $inventory, ?string $messageStart = null): JsonResponse
    {
        $owner = $inventory->getOwner();

        $target = UserFunctions::findOneRecentlyActive($this->em, $owner, 24);

        if($target === null)
            return $this->responseService->itemActionSuccess('Hm... there\'s no one to toss it to! (I guess no one\'s been playing Poppy Seed Pets...)');

        $inventory
            ->changeOwner($target, $owner->getName() . ' tossed this to ' . $target->getName() . '!', $this->em)
            ->setLocation(LocationEnum::Home)
        ;

        $this->em->flush();

        if($messageStart == null)
            $messageStart = 'You toss the ' . $inventory->getFullItemName();

        return $this->responseService->itemActionSuccess($messageStart . ' to <a href="/poppyopedia/resident/' . $target->getId() . '">' . $target->getName() . '</a>!', [ 'itemDeleted' => true ]);
    }

    public function teleportItem(Inventory $inventory, string $messageStart = null): JsonResponse
    {
        $owner = $inventory->getOwner();

        $target = UserFunctions::findOneRecentlyActive($this->em, $owner, 24);

        if($target === null)
            return $this->responseService->itemActionSuccess('Hm... there\'s no one to receive it! (I guess no one\'s been playing Poppy Seed Pets...)');

        $inventory
            ->changeOwner($target, 'This was magically transported from ' . $owner->getName() . ' to ' . $target->getName() . '!', $this->em)
            ->setLocation(LocationEnum::Home)
        ;

        $this->em->flush();

        return $this->responseService->itemActionSuccess($messageStart . ' It then poofs out of existence, though you get the _strange sensation_ that is has been magically transported to <a href="/poppyopedia/resident/' . $target->getId() . '">' . $target->getName() . '</a>!', [ 'itemDeleted' => true ]);
    }
}