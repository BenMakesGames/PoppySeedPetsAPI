<?php

namespace App\Entity;

use App\Functions\ArrayFunctions;
use App\Functions\NumberFunctions;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PetRelationshipRepository")
 */
class PetRelationship
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pet", inversedBy="petRelationships")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pet;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pet")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"petFriend"})
     */
    private $relationship;

    /**
     * @ORM\Column(type="integer")
     */
    private $intimacy = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $passion = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $commitment = 0;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"petFriend"})
     */
    private $metDescription;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"petFriend"})
     */
    private $metOn;

    public function __construct()
    {
        $this->metOn = new \DateTimeImmutable();
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

        return $this;
    }

    public function getRelationship(): ?Pet
    {
        return $this->relationship;
    }

    public function setRelationship(?Pet $relationship): self
    {
        $this->relationship = $relationship;

        return $this;
    }

    public function getIntimacy(): int
    {
        return $this->intimacy;
    }

    public function increaseIntimacy(int $intimacy): self
    {
        $this->intimacy = NumberFunctions::constrain($this->intimacy + $intimacy, 0, 1000);

        return $this;
    }

    public function getPassion(): int
    {
        return $this->passion;
    }

    public function increasePassion(int $passion): self
    {
        $this->passion = NumberFunctions::constrain($this->passion + $passion, 0, 1000);

        return $this;
    }

    public function getCommitment(): int
    {
        return $this->commitment;
    }

    public function increaseCommitment(int $commitment): self
    {
        $this->commitment = NumberFunctions::constrain($this->commitment + $commitment, 0, 1000);

        return $this;
    }

    public function getMetDescription(): ?string
    {
        return $this->metDescription;
    }

    public function setMetDescription(string $metDescription): self
    {
        $this->metDescription = $metDescription;

        return $this;
    }

    public function getMetOn(): \DateTimeImmutable
    {
        return $this->metOn;
    }

    /**
     * @Groups({"petFriend"})
     */
    public function getSummary(): string
    {
        $descriptions = array();

        if($this->getPassion() >= 750)
            $descriptions[] = 'hot';
        else if($this->getPassion() >= 500)
            $descriptions[] = 'attractive';
        else if($this->getPassion() >= 250)
            $descriptions[] = 'cute';

        if($this->getIntimacy() >= 750)
            $descriptions[] = 'best friend';
        else if($this->getIntimacy() >= 500)
            $descriptions[] = 'awesome';
        else if($this->getIntimacy() >= 250)
            $descriptions[] = 'fun';

        if($this->getCommitment() >= 750)
            $descriptions[] = 'irreplaceable';
        else if($this->getCommitment() >= 500)
            $descriptions[] = 'greatly valued';
        else if($this->getCommitment() >= 250)
            $descriptions[] = 'valued';

        return ArrayFunctions::list_nice($descriptions);
    }
}
