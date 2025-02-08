<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class DragonHostage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\OneToOne(targetEntity: Dragon::class, inversedBy: 'hostage', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private $dragon;

    #[Groups(["myDragon"])]
    #[ORM\Column(type: 'string', length: 40)]
    private $type;

    #[Groups(["myDragon"])]
    #[ORM\Column(type: 'string', length: 40)]
    private $name;

    #[Groups(["myDragon"])]
    #[ORM\Column(type: 'string', length: 40)]
    private $appearance;

    #[Groups(["myDragon"])]
    #[ORM\Column(type: 'string', length: 255)]
    private $dialog;

    #[Groups(["myDragon"])]
    #[ORM\Column(type: 'string', length: 6)]
    private $colorA;

    #[Groups(["myDragon"])]
    #[ORM\Column(type: 'string', length: 6)]
    private $colorB;

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

    public function getColorA(): ?string
    {
        return $this->colorA;
    }

    public function setColorA(string $colorA): self
    {
        $this->colorA = $colorA;

        return $this;
    }

    public function getColorB(): ?string
    {
        return $this->colorB;
    }

    public function setColorB(string $colorB): self
    {
        $this->colorB = $colorB;

        return $this;
    }
}
