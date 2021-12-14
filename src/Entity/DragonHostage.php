<?php

namespace App\Entity;

use App\Repository\DragonHostageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=DragonHostageRepository::class)
 */
class DragonHostage
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Dragon::class, inversedBy="hostage", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $dragon;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"myDragon"})
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"myDragon"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"myDragon"})
     */
    private $appearance;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $dialog;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDragon(): ?Dragon
    {
        return $this->dragon;
    }

    public function setDragon(Dragon $dragon): self
    {
        $this->dragon = $dragon;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAppearance(): ?string
    {
        return $this->appearance;
    }

    public function setAppearance(string $appearance): self
    {
        $this->appearance = $appearance;

        return $this;
    }

    public function getDialog(): ?string
    {
        return $this->dialog;
    }

    public function setDialog(string $dialog): self
    {
        $this->dialog = $dialog;

        return $this;
    }
}
