<?php

namespace App\Entity;

use App\Enum\EnumInvalidValueException;
use App\Enum\UnlockableFeatureEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserUnlockedFeatureRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="user_id_feature_idx", columns={"user_id", "feature"})
 *     }
 * )
 */
class UserUnlockedFeature
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="unlockedFeatures")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $feature;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $unlockedOn;

    public function __construct()
    {
        $this->unlockedOn = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getFeature(): string
    {
        return $this->feature;
    }

    public function setFeature(string $feature): self
    {
        if(!UnlockableFeatureEnum::isAValue($feature))
            throw new EnumInvalidValueException(UnlockableFeatureEnum::class, $feature);

        $this->feature = $feature;

        return $this;
    }

    public function getUnlockedOn(): \DateTimeImmutable
    {
        return $this->unlockedOn;
    }
}
