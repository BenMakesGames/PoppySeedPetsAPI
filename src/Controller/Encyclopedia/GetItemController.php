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
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ItemRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/encyclopedia")]
class GetItemController extends AbstractController
{
    #[DoesNotRequireHouseHours]
    #[Route("/item/{itemName}", methods: ["GET"])]
    public function getItemByName(string $itemName, EntityManagerInterface $em, ResponseService $responseService)
    {
        try
        {
            $item = ItemRepository::findOneByName($em, $itemName);

            return $responseService->success($item, [ SerializationGroupEnum::ITEM_ENCYCLOPEDIA ]);
        }
        catch(\InvalidArgumentException $e)
        {
            throw new PSPNotFoundException('There is no such item.');
        }
    }
}
