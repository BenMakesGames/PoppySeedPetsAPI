<?php

namespace App\DoctrineEventListeners;

use App\Entity\Inventory;
use App\Entity\Pet;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostLoadEventArgs;

class PetLoadListener
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function postLoad(Pet $pet, PostLoadEventArgs $eventArgs): void
    {
        $petSpeciesProxy = $pet->getSpecies();

        if(!$petSpeciesProxy instanceof \Doctrine\ORM\Proxy\Proxy)
            return;

        // Check if the PetSpecies proxy is already initialized
        if ($petSpeciesProxy->__isInitialized()) {
            return;
        }

        $speciesId = $petSpeciesProxy->getId();
        $query = $this->entityManager->createQuery('SELECT s FROM App\Entity\PetSpecies s WHERE s.id = :id');
        $query->setParameter('id', $speciesId);
        $query->enableResultCache(24 * 60 * 60, 'PetLoadListener_GetPetSpeciesById_' . $speciesId);

        // Execute query and get the result
        $petSpecies = $query->getOneOrNullResult();

        // Update the PetSpecies proxy with the fetched Item data
        if ($petSpecies) {
            $uow = $eventArgs->getObjectManager()->getUnitOfWork();
            $uow->registerManaged($petSpeciesProxy, ['id' => $speciesId], $uow->getOriginalEntityData($petSpecies));
            $petSpeciesProxy->__load();
        }
    }
}