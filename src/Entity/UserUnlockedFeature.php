<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Entity;

use App\Enum\EnumInvalidValueException;
use App\Enum\UnlockableFeatureEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table]
#[ORM\UniqueConstraint(name: 'user_id_feature_idx', columns: ['user_id', 'feature'])]
#[ORM\Entity]
class UserUnlockedFeature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'unlockedFeatures')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[Groups(["myAccount"])]
    #[ORM\Column(type: 'string', length: 40)]
    private $feature;

    #[Groups(["myAccount"])]
    #[ORM\Column(type: 'datetime_immutable')]
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
