<?php

namespace App\Entity;

use App\Enum\PetActivityLogInterestingnessEnum;
use App\Model\PetChangesSummary;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PetActivityLogRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="interesingness_idx", columns={"interestingness"})
 * })
 */
class PetActivityLog
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pet")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"petActivityLogAndPublicPet"})
     */
    private $pet;

    /**
     * @ORM\Column(type="text")
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    private $entry;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    private $createdOn;

    /**
     * @ORM\Column(type="object", nullable=true)
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    private $changes;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    private $icon = '';

    /**
     * @ORM\Column(type="integer")
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    private $interestingness = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $viewed = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Item")
     * @Groups({"petActivityLogAndPublicPet"})
     */
    private $equippedItem;

    public function __construct()
    {
        $this->createdOn = new \DateTimeImmutable();
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

    public function getViewed(): bool
    {
        return $this->viewed;
    }

    public function setViewed(): self
    {
        $this->viewed = true;

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
}
