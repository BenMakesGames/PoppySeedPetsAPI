<?php

namespace App\DoctrineEventListeners;

use App\Entity\Inventory;
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
        $query->enableResultCache(24 * 60 * 60, 'InventoryLoadListener_GetItemById_' . $itemId);

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