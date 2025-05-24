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

use App\Entity\Item;
use App\Entity\ItemGroup;
use App\Entity\MuseumItem;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Functions\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;

class MuseumService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserStatsService $userStatsRepository,
        private readonly TransactionService $transactionService
    )
    {
    }

    private array $donatedItemsThisRequest = [];

    public function forceDonateItem(User $user, string|int|Item $item, ?string $comment, ?User $createdBy = null): bool
    {
        if(is_string($item))
            $item = ItemRepository::findOneByName($this->em, $item);
        else if(is_numeric($item))
            $item = ItemRepository::findOneById($this->em, $item);

        if(in_array($item->getName(), $this->donatedItemsThisRequest))
            return false;

        $this->donatedItemsThisRequest[] = $item->getName();

        $museumItem = $this->em->getRepository(MuseumItem::class)->findOneBy([
            'user' => $user,
            'item' => $item
        ]);

        if($museumItem)
            return false;

        $museumItem = (new MuseumItem(user: $user, item: $item))
            ->setCreatedBy($createdBy)
        ;

        if($comment)
            $museumItem->setComments([ $comment ]);

        $this->em->persist($museumItem);

        $this->transactionService->getMuseumFavor($user, $item->getMuseumPoints(), 'Someone, or something, donated ' . $item->getNameWithArticle() . ' to the Museum on your behalf.');

        $this->userStatsRepository->incrementStat($user, UserStatEnum::ItemsDonatedToMuseum, 1);

        return true;
    }

    public function getGiftShopInventory(User $user): array
    {
        $inventory = [];

        $groups = $this->em->getRepository(ItemGroup::class)->findBy([
            'isGiftShop' => true
        ]);

        foreach($groups as $group)
        {
            $userDonated = $this->em->getRepository(MuseumItem::class)->count([
                'user' => $user,
                'item' => $group->getItems()->toArray()
            ]);

            $requiredToUnlock = (int)ceil(count($group->getItems()) / 2);

            $forSale = [];

            if($userDonated >= $requiredToUnlock)
            {
                foreach($group->getItems() as $item)
                {
                    $forSale[] = [
                        'item' => [ 'name' => $item->getName(), 'image' => $item->getImage() ],
                        'cost' => $item->getMuseumPoints() * 10,
                    ];
                }
            }

            $inventory[] = [
                'category' => $group->getName(),
                'itemsDonated' => $userDonated,
                'requiredToUnlock' => $requiredToUnlock,
                'inventory' => $forSale
            ];

        }

        return $inventory;
    }
}
