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


namespace App\DoctrineEventListeners;

use App\Entity\Inventory;
use App\Functions\CacheHelpers;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostLoadEventArgs;

class InventoryLoadListener
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function postLoad(Inventory $inventory, PostLoadEventArgs $eventArgs): void
    {
        $itemProxy = $inventory->getItem();

        if(!$itemProxy instanceof \Doctrine\ORM\Proxy\Proxy)
            return;

        // Check if the Item proxy is already initialized
        if ($itemProxy->__isInitialized()) {
            return;
        }

        $itemId = $itemProxy->getId();
        $query = $this->entityManager->createQuery('SELECT i FROM App\Entity\Item i WHERE i.id = :id');
        $query->setParameter('id', $itemId);
        $query->enableResultCache(24 * 60 * 60, CacheHelpers::getCacheItemName('InventoryLoadListener_GetItemById_' . $itemId));

        // Execute query and get the result
        $item = $query->getOneOrNullResult();

        // Update the Item proxy with the fetched Item data
        if ($item) {
            $uow = $eventArgs->getObjectManager()->getUnitOfWork();
            $uow->registerManaged($itemProxy, ['id' => $itemId], $uow->getOriginalEntityData($item));
            $itemProxy->__load();
        }
    }
}