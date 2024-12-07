<?php
namespace App\Entity;

use App\Service\IRandom;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class CookingBuddy
{
    public const NAMES = [
        'Asparagus', 'Arugula',
        'Biryani', 'Bisque',
        'Cake', 'Ceviche',
        'Cookie', 'Couscous',
        'Dal',
        'Egg Roll', 'Edamame',
        'Falafel',
        'Gnocchi', 'Gobi', 'Goulash', 'Gumbo',
        'Haggis', 'Halibut', 'Hummus',
        'Kabuli', 'Kebab', 'Kimchi', 'Kiwi', 'Kuli Kuli',
        'Larb',
        'Masala', 'Moose',
        'Pinto', 'Pho', 'Polenta', 'Pudding',
        'Reuben',
        'Schnitzel', 'Shawarma', 'Soba', 'Stew', 'Succotash',
        'Taco', 'Tart',
        'Walnut',
        'Yuzu',
        'Ziti',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'cookingBuddy', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(length: 40)]
    private ?string $name = null;

    #[ORM\Column(length: 40)]
    private ?string $appearance = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function generateNewName(IRandom $rng): static
    {
        $oldName = $this->name;

        do
        {
            $this->name = $rng->rngNextFromArray(self::NAMES);
        } while($this->name === $oldName);

        return $this;
    }

    public function getAppearance(): ?string
    {
        return $this->appearance;
    }

    public function setAppearance(string $appearance): static
    {
        $this->appearance = $appearance;

        return $this;
    }
}
