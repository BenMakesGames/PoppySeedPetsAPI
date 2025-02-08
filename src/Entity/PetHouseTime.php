<?php
declare(strict_types=1);

namespace App\Entity;

use App\Functions\NumberFunctions;
use App\Service\PetExperienceService;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table]
#[ORM\Index(name: 'activity_time_idx', columns: ['activity_time'])]
#[ORM\Index(name: 'social_energy_idx', columns: ['social_energy'])]
#[ORM\Entity(repositoryClass: 'App\Repository\PetHouseTimeRepository')]
class PetHouseTime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\OneToOne(targetEntity: Pet::class, inversedBy: 'houseTime', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $pet;

    #[ORM\Column(type: 'integer')]
    #[Groups(['myPet'])]
    private $activityTime = 59;

    #[ORM\Column(type: 'integer')]
    private $socialEnergy = 0;

    #[ORM\Column(type: 'integer')]
    private $timeSpent = 0;

    #[ORM\Column(type: 'datetime_immutable')]
    private $canAttemptSocialHangoutAfter;

    public function __construct()
    {
        $this->canAttemptSocialHangoutAfter = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPet(): ?Pet
    {
        return $this->pet;
    }

    public function setPet(Pet $pet): self
    {
        $this->pet = $pet;

        return $this;
    }

    public function getActivityTime(): ?int
    {
        return $this->activityTime;
    }

    public function setActivityTime(int $activityTime): self
    {
        $this->activityTime = $activityTime;

        return $this;
    }

    public function spendActivityTime(int $amount): self
    {
        $this->activityTime -= $amount;
        $this->timeSpent += $amount;

        return $this;
    }

    public function getSocialEnergy(): ?int
    {
        return $this->socialEnergy;
    }

    public function setSocialEnergy(int $initialSocialEnergy): self
    {
        $this->socialEnergy = $initialSocialEnergy;
        return $this;
    }

    public function spendSocialEnergy(int $amount): self
    {
        $this->socialEnergy = NumberFunctions::clamp(
            $this->socialEnergy - $amount,
            -PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT,
            PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT * 5
        );

        return $this;
    }

    public function getTimeSpent(): ?int
    {
        return $this->timeSpent;
    }

    public function getCanAttemptSocialHangoutAfter(): ?\DateTimeImmutable
    {
        return $this->canAttemptSocialHangoutAfter;
    }

    public function setCanAttemptSocialHangoutAfter(\DateTimeImmutable $dateTime): self
    {
        $this->canAttemptSocialHangoutAfter = $dateTime;

        return $this;
    }
}
