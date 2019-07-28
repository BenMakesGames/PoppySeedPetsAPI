<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="name_index", columns={"name"})
 * })
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"myAccount", "myInventory", "userPublicProfile", "article", "petPublicProfile", "museum", "marketItem"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({"myAccount"})
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"myAccount", "myInventory", "userPublicProfile", "article", "petPublicProfile", "museum", "marketItem"})
     */
    private $name;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"userPublicProfile"})
     */
    private $lastActivity;

    /**
     * @ORM\Column(type="string", length=40, unique=true)
     * @Groups({"myAccount"})
     */
    private $sessionId;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $sessionExpiration;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Pet", mappedBy="owner")
     * @Groups({"userPublicProfile"})
     */
    private $pets;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"userPublicProfile"})
     */
    private $registeredOn;

    /**
     * @ORM\Column(type="date_immutable")
     * @Groups({"myAccount"})
     */
    private $lastAllowanceCollected;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isLocked = false;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"myAccount"})
     */
    private $moneys = 0;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"myAccount"})
     */
    private $maxInventory = 100;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"myAccount"})
     */
    private $maxPets = 2;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserFriend", mappedBy="user", orphanRemoval=true)
     */
    private $friends;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserFriend", mappedBy="friend", orphanRemoval=true)
     */
    private $friendsOf;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserStats", mappedBy="user", orphanRemoval=true)
     */
    private $stats;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"myAccount"})
     */
    private $defaultSessionLengthInHours = 72;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\UserNotificationPreferences", mappedBy="user", cascade={"persist", "remove"})
     */
    private $userNotificationPreferences;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxSellPrice = 50;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"myAccount"})
     */
    private $unlockedFlorist;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"myAccount"})
     */
    private $unlockedBookstore;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"myAccount"})
     */
    private $unlockedMerchant;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"myAccount"})
     */
    private $unlockedPark;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\PassphraseResetRequest", mappedBy="user", cascade={"persist", "remove"})
     */
    private $passphraseResetRequest;

    public function __construct()
    {
        $this->pets = new ArrayCollection();
        $this->registeredOn = new \DateTimeImmutable();
        $this->lastAllowanceCollected = (new \DateTimeImmutable())->modify('-7 days');
        $this->friends = new ArrayCollection();
        $this->stats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDailySeed(): int
    {
        return (($this->id * date('N')) % (date('nd') * 53)) + date('Yj');
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @Groups({"myAccount"})
     */
    public function getIsAdmin(): bool
    {
        return $this->hasRole('ROLE_ADMIN');
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getLastActivity(): ?\DateTimeImmutable
    {
        return $this->lastActivity;
    }

    public function setLastActivity(?int $sessionHours): self
    {
        $this->lastActivity = new \DateTimeImmutable();

        if(!$sessionHours)
            $sessionHours = $this->getDefaultSessionLengthInHours();

        $this->sessionExpiration = (new \DateTimeImmutable())->modify('+' . $sessionHours . ' hours');

        return $this;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId): self
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function getSessionExpiration(): ?\DateTimeImmutable
    {
        return $this->sessionExpiration;
    }

    public function logOut()
    {
        $this->sessionExpiration = new \DateTimeImmutable();
    }

    /**
     * @return Collection|Pet[]
     */
    public function getPets(): Collection
    {
        return $this->pets;
    }

    public function addPet(Pet $pet): self
    {
        if (!$this->pets->contains($pet)) {
            $this->pets[] = $pet;
            $pet->setOwner($this);
        }

        return $this;
    }

    public function removePet(Pet $pet): self
    {
        if ($this->pets->contains($pet)) {
            $this->pets->removeElement($pet);
            // set the owning side to null (unless already changed)
            if ($pet->getOwner() === $this) {
                $pet->setOwner(null);
            }
        }

        return $this;
    }

    public function getRegisteredOn(): ?\DateTimeImmutable
    {
        return $this->registeredOn;
    }

    public function getLastAllowanceCollected(): ?\DateTimeImmutable
    {
        return $this->lastAllowanceCollected;
    }

    public function setLastAllowanceCollected(\DateTimeImmutable $lastAllowanceCollected): self
    {
        $this->lastAllowanceCollected = $lastAllowanceCollected;

        return $this;
    }

    public function getIsLocked(): ?bool
    {
        return $this->isLocked;
    }

    public function setIsLocked(bool $isLocked): self
    {
        $this->isLocked = $isLocked;

        return $this;
    }

    public function getMoneys(): int
    {
        return $this->moneys;
    }

    public function increaseMoneys(int $amount): self
    {
        $this->moneys += $amount;

        return $this;
    }

    public function getMaxInventory(): int
    {
        return $this->maxInventory;
    }

    public function setMaxInventory(int $maxInventory): self
    {
        $this->maxInventory = $maxInventory;

        return $this;
    }

    public function getMaxPets(): int
    {
        return $this->maxPets;
    }

    public function setMaxPets(int $maxPets): self
    {
        $this->maxPets = $maxPets;

        return $this;
    }

    /**
     * @return Collection|UserFriend[]
     */
    public function getFriends(): Collection
    {
        return $this->friends;
    }

    public function addFriend(UserFriend $friend): self
    {
        if (!$this->friends->contains($friend)) {
            $this->friends[] = $friend;
            $friend->setUser($this);
        }

        return $this;
    }

    public function removeFriend(UserFriend $friend): self
    {
        if ($this->friends->contains($friend)) {
            $this->friends->removeElement($friend);
            // set the owning side to null (unless already changed)
            if ($friend->getUser() === $this) {
                $friend->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserStats[]
     */
    public function getStats(): Collection
    {
        return $this->stats;
    }

    public function addStat(UserStats $stat): self
    {
        if (!$this->stats->contains($stat)) {
            $this->stats[] = $stat;
            $stat->setUser($this);
        }

        return $this;
    }

    public function removeStat(UserStats $stat): self
    {
        if ($this->stats->contains($stat)) {
            $this->stats->removeElement($stat);
            // set the owning side to null (unless already changed)
            if ($stat->getUser() === $this) {
                $stat->setUser(null);
            }
        }

        return $this;
    }

    public function getDefaultSessionLengthInHours(): int
    {
        return $this->defaultSessionLengthInHours;
    }

    public function setDefaultSessionLengthInHours(int $defaultSessionLengthInHours): self
    {
        $this->defaultSessionLengthInHours = $defaultSessionLengthInHours;

        return $this;
    }

    public function getUserNotificationPreferences(): ?UserNotificationPreferences
    {
        return $this->userNotificationPreferences;
    }

    public function setUserNotificationPreferences(UserNotificationPreferences $userNotificationPreferences): self
    {
        $this->userNotificationPreferences = $userNotificationPreferences;

        // set the owning side of the relation if necessary
        if ($this !== $userNotificationPreferences->getUser()) {
            $userNotificationPreferences->setUser($this);
        }

        return $this;
    }

    public function getMaxSellPrice(): ?int
    {
        return $this->maxSellPrice;
    }

    public function setMaxSellPrice(int $maxSellPrice): self
    {
        $this->maxSellPrice = $maxSellPrice;

        return $this;
    }

    public function getUnlockedFlorist(): ?\DateTimeImmutable
    {
        return $this->unlockedFlorist;
    }

    public function setUnlockedFlorist(): self
    {
        $this->unlockedFlorist = new \DateTimeImmutable();

        return $this;
    }

    public function getUnlockedBookstore(): ?\DateTimeImmutable
    {
        return $this->unlockedBookstore;
    }

    public function setUnlockedBookstore(): self
    {
        $this->unlockedBookstore = new \DateTimeImmutable();

        return $this;
    }

    public function getUnlockedMerchant(): ?\DateTimeImmutable
    {
        return $this->unlockedMerchant;
    }

    public function setUnlockedMerchant(): self
    {
        $this->unlockedMerchant = new \DateTimeImmutable();

        return $this;
    }

    public function getUnlockedPark(): ?\DateTimeImmutable
    {
        return $this->unlockedPark;
    }

    public function setUnlockedPark(?\DateTimeImmutable $unlockedPark): self
    {
        $this->unlockedPark = $unlockedPark;

        return $this;
    }

    public function getPasswordResetRequest(): ?PassphraseResetRequest
    {
        return $this->passwordResetRequest;
    }

    public function setPasswordResetRequest(PassphraseResetRequest $passwordResetRequest): self
    {
        $this->passwordResetRequest = $passwordResetRequest;

        // set the owning side of the relation if necessary
        if ($this !== $passwordResetRequest->getUser()) {
            $passwordResetRequest->setUser($this);
        }

        return $this;
    }
}
