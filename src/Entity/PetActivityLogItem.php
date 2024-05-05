<?php

namespace App\Entity;

use App\Repository\PetActivityLogItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PetActivityLogItemRepository::class)]
class PetActivityLogItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'createdItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PetActivityLog $log = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Item $item = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLog(): ?PetActivityLog
    {
        return $this->log;
    }

    public function setLog(?PetActivityLog $log): static
    {
        $this->log = $log;

        return $this;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): static
    {
        $this->item = $item;

        return $this;
    }
}
