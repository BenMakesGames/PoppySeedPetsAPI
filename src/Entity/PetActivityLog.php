<?php

namespace App\Entity;

use App\Enum\PetActivityLogInterestingnessEnum;
use App\Model\PetChangesSummary;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table]
#[ORM\Index(name: 'created_on_idx', columns: ['created_on'])]
#[ORM\Entity]
class PetActivityLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * @Groups({"petActivityLogAndPublicPet"})
     */
    #[ORM\ManyToOne(targetEntity: Pet::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $pet;

    /**
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    #[ORM\Column(type: 'text')]
    private $entry;

    /**
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private $createdOn;

    /**
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    #[ORM\Column(type: 'object', nullable: true)]
    private $changes;

    /**
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    #[ORM\Column(type: 'string', length: 40)]
    private $icon = '';

    /**
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    #[ORM\Column(type: 'integer')]
    private $interestingness = 0;

    /**
     * @Groups({"petActivityLogAndPublicPet"})
     */
    #[ORM\ManyToOne(targetEntity: Item::class)]
    private $equippedItem;

    /**
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    #[ORM\ManyToMany(targetEntity: PetActivityLogTag::class)]
    private $tags;

    public function __construct()
    {
        $this->createdOn = new \DateTimeImmutable();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPet(): ?Pet
    {
        return $this->pet;
    }

    public function setPet(?Pet $pet): self
    {
        $this->pet = $pet;

        if($pet && $pet->getTool())
            $this->equippedItem = $pet->getTool()->getItem();

        return $this;
    }

    public function getEntry(): ?string
    {
        return $this->entry;
    }

    public function setEntry(string $entry): self
    {
        $this->entry = $entry;

        return $this;
    }

    public function getCreatedOn(): \DateTimeImmutable
    {
        return $this->createdOn;
    }

    public function getChanges(): ?PetChangesSummary
    {
        return $this->changes;
    }

    public function setChanges(?PetChangesSummary $changes): self
    {
        $this->changes = $changes;

        if($changes !== null && (!!$changes->level || !!$changes->affectionLevel))
            $this->addInterestingness(PetActivityLogInterestingnessEnum::LEVEL_UP);

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getInterestingness(): ?int
    {
        return $this->interestingness;
    }

    public function addInterestingness(int $interestingness): self
    {
        if($interestingness > $this->interestingness)
            $this->interestingness = $interestingness;

        return $this;
    }

    public function getEquippedItem(): ?Item
    {
        return $this->equippedItem;
    }

    public function setEquippedItem(?Item $equippedItem): self
    {
        $this->equippedItem = $equippedItem;

        return $this;
    }

    /**
     * @Groups({"petActivityLogs"})
     */
    public function getIsPetActivity(): bool
    {
        return $this->pet !== null;
    }

    /**
     * @return Collection|PetActivityLogTag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * @param PetActivityLogTag[] $tags
     */
    public function addTags(array $tags): self
    {
        foreach($tags as $tag)
            $this->addTag($tag);

        return $this;
    }

    public function addTag(PetActivityLogTag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }
}
