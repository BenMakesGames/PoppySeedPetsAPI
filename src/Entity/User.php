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
 *     @ORM\Index(name="name_idx", columns={"name"}),
 *     @ORM\Index(name="last_activity_idx", columns={"last_activity"}),
 * })
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"myAccount", "myInventory", "userPublicProfile", "article", "petPublicProfile", "museum", "parkEvent", "userTypeahead"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({"myAccount"})
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @Groups({"myAccount"})
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"myAccount", "myInventory", "userPublicProfile", "article", "petPublicProfile", "museum", "parkEvent", "userTypeahead"})
     */
    private $name;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"userPublicProfile"})
     */
    private $lastActivity;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Pet", mappedBy="owner", fetch="EXTRA_LAZY")
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
     * @ORM\OneToMany(targetEntity="UserFollowing", mappedBy="user", orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $following;

    /**
     * @ORM\OneToMany(targetEntity="UserFollowing", mappedBy="following", orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $followedBy;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserStats", mappedBy="user", orphanRemoval=true, fetch="EXTRA_LAZY")
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
    private $maxSellPrice = 10;

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
    private $unlockedMuseum;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"myAccount"})
     */
    private $unlockedPark;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"myAccount"})
     */
    private $unlockedGreenhouse;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\PassphraseResetRequest", mappedBy="user", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     */
    private $passphraseResetRequest;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GreenhousePlant", mappedBy="owner", orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $greenhousePlants;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserSession", mappedBy="user", orphanRemoval=true)
     */
    private $userSessions;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"myAccount"})
     */
    private $unlockedBasement;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PushSubscription", mappedBy="user", fetch="EXTRA_LAZY")
     */
    private $pushSubscriptions;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\HollowEarthPlayer", mappedBy="user", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     */
    private $hollowEarthPlayer;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"myAccount"})
     */
    private $unlockedHollowEarth;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"myAccount"})
     */
    private $unlockedMarket;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"myAccount"})
     */
    private $unlockedFireplace;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Fireplace", mappedBy="user", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     */
    private $fireplace;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"myAccount"})
     */
    private $unlockedBeehive;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Beehive", mappedBy="user", cascade={"persist", "remove"})
     */
    private $beehive;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Greenhouse", mappedBy="owner", cascade={"persist", "remove"})
     */
    private $greenhouse;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"myAccount"})
     */
    private $recyclePoints = 0;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"myAccount"})
     */
    private $unlockedRecycling;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"myAccount"})
     */
    private $unlockedTrader;

    /**
     * @ORM\Column(type="smallint")
     * @Groups({"myAccount"})
     */
    private $unreadNews = 0;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     * @Groups({"myAccount", "userPublicProfile", "petPublicProfile", "museum", "parkEvent"})
     */
    private $icon;

    public function __construct()
    {
        $this->pets = new ArrayCollection();
        $this->registeredOn = new \DateTimeImmutable();
        $this->lastAllowanceCollected = (new \DateTimeImmutable())->modify('-7 days');
        $this->following = new ArrayCollection();
        $this->stats = new ArrayCollection();
        $this->greenhousePlants = new ArrayCollection();
        $this->userSessions = new ArrayCollection();
        $this->pushSubscriptions = new ArrayCollection();
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

    public function setLastActivity(): self
    {
        $this->lastActivity = new \DateTimeImmutable();

        return $this;
    }

    /**
     * @return Collection|Pet[]
     */
    public function getPets(): Collection
    {
        return $this->pets;
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

    // should only be called from TransactionService
    public function increaseMoneys(int $amount): self
    {
        $this->moneys += $amount;

        if(!$this->unlockedMarket)
            $this->setUnlockedMarket();

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

    public function increaseMaxPets(int $amount): self
    {
        $this->maxPets += $amount;

        return $this;
    }

    /**
     * @return Collection|UserFollowing[]
     */
    public function getFollowing(): Collection
    {
        return $this->following;
    }

    public function addFollowing(UserFollowing $following): self
    {
        if (!$this->following->contains($following)) {
            $this->following[] = $following;
            $following->setUser($this);
        }

        return $this;
    }

    public function removeFollowing(UserFollowing $following): self
    {
        if ($this->following->contains($following)) {
            $this->following->removeElement($following);
            // set the owning side to null (unless already changed)
            if ($following->getUser() === $this) {
                $following->setUser(null);
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
        if(!$this->unlockedFlorist)
            $this->unlockedFlorist = new \DateTimeImmutable();

        return $this;
    }

    public function getUnlockedBookstore(): ?\DateTimeImmutable
    {
        return $this->unlockedBookstore;
    }

    public function setUnlockedBookstore(): self
    {
        if(!$this->unlockedBookstore)
            $this->unlockedBookstore = new \DateTimeImmutable();

        return $this;
    }

    public function getUnlockedMuseum(): ?\DateTimeImmutable
    {
        return $this->unlockedMuseum;
    }

    public function setUnlockedMuseum(): self
    {
        if(!$this->unlockedMuseum)
            $this->unlockedMuseum = new \DateTimeImmutable();

        return $this;
    }

    public function getUnlockedPark(): ?\DateTimeImmutable
    {
        return $this->unlockedPark;
    }

    public function setUnlockedPark(): self
    {
        if(!$this->unlockedPark)
            $this->unlockedPark = new \DateTimeImmutable();

        return $this;
    }

    public function getPassphraseResetRequest(): ?PassphraseResetRequest
    {
        return $this->passphraseResetRequest;
    }

    public function setPassphraseResetRequest(PassphraseResetRequest $passphraseResetRequest): self
    {
        $this->passphraseResetRequest = $passphraseResetRequest;

        // set the owning side of the relation if necessary
        if ($this !== $passphraseResetRequest->getUser()) {
            $passphraseResetRequest->setUser($this);
        }

        return $this;
    }

    public function getUnlockedGreenhouse(): ?\DateTimeImmutable
    {
        return $this->unlockedGreenhouse;
    }

    public function setUnlockedGreenhouse(): self
    {
        if(!$this->unlockedGreenhouse)
            $this->unlockedGreenhouse = new \DateTimeImmutable();

        return $this;
    }

    /**
     * @return Collection|GreenhousePlant[]
     */
    public function getGreenhousePlants(): Collection
    {
        return $this->greenhousePlants;
    }

    public function addGreenhousePlant(GreenhousePlant $greenhousePlant): self
    {
        if (!$this->greenhousePlants->contains($greenhousePlant)) {
            $this->greenhousePlants[] = $greenhousePlant;
            $greenhousePlant->setOwner($this);
        }

        return $this;
    }

    public function removeGreenhousePlant(GreenhousePlant $greenhousePlant): self
    {
        if ($this->greenhousePlants->contains($greenhousePlant)) {
            $this->greenhousePlants->removeElement($greenhousePlant);
            // set the owning side to null (unless already changed)
            if ($greenhousePlant->getOwner() === $this) {
                $greenhousePlant->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @Groups({"myAccount"})
     */
    public function getMaxPlants(): int
    {
        return $this->getGreenhouse() ? $this->getGreenhouse()->getMaxPlants() : 0;
    }

    /**
     * @return Collection|UserSession[]
     */
    public function getUserSessions(): Collection
    {
        return $this->userSessions;
    }

    public function addUserSession(UserSession $userSession): self
    {
        if (!$this->userSessions->contains($userSession)) {
            $this->userSessions[] = $userSession;
            $userSession->setUser($this);
        }

        return $this;
    }

    public function removeUserSession(UserSession $userSession): self
    {
        if ($this->userSessions->contains($userSession)) {
            $this->userSessions->removeElement($userSession);
            // set the owning side to null (unless already changed)
            if ($userSession->getUser() === $this) {
                $userSession->setUser(null);
            }
        }

        return $this;
    }

    public function getUnlockedBasement(): ?\DateTimeImmutable
    {
        return $this->unlockedBasement;
    }

    public function setUnlockedBasement(): self
    {
        if(!$this->unlockedBasement)
            $this->unlockedBasement = new \DateTimeImmutable();

        return $this;
    }

    /**
     * @return Collection|PushSubscription[]
     */
    public function getPushSubscriptions(): Collection
    {
        return $this->pushSubscriptions;
    }

    public function getHollowEarthPlayer(): ?HollowEarthPlayer
    {
        return $this->hollowEarthPlayer;
    }

    public function setHollowEarthPlayer(HollowEarthPlayer $hollowEarthPlayer): self
    {
        $this->hollowEarthPlayer = $hollowEarthPlayer;

        // set the owning side of the relation if necessary
        if ($this !== $hollowEarthPlayer->getUser()) {
            $hollowEarthPlayer->setUser($this);
        }

        return $this;
    }

    public function getUnlockedHollowEarth(): ?\DateTimeImmutable
    {
        return $this->unlockedHollowEarth;
    }

    public function setUnlockedHollowEarth(): self
    {
        if(!$this->unlockedHollowEarth)
            $this->unlockedHollowEarth = new \DateTimeImmutable();

        return $this;
    }

    public function getUnlockedMarket(): ?\DateTimeImmutable
    {
        return $this->unlockedMarket;
    }

    public function setUnlockedMarket(): self
    {
        if(!$this->unlockedMarket)
            $this->unlockedMarket = new \DateTimeImmutable();

        return $this;
    }

    public function getUnlockedFireplace(): ?\DateTimeImmutable
    {
        return $this->unlockedFireplace;
    }

    public function setUnlockedFireplace(): self
    {
        if(!$this->unlockedFireplace)
            $this->unlockedFireplace = new \DateTimeImmutable();

        return $this;
    }

    public function getFireplace(): ?Fireplace
    {
        return $this->fireplace;
    }

    public function setFireplace(Fireplace $fireplace): self
    {
        $this->fireplace = $fireplace;

        // set the owning side of the relation if necessary
        if ($this !== $fireplace->getUser()) {
            $fireplace->setUser($this);
        }

        return $this;
    }

    public function getUnlockedBeehive(): ?\DateTimeImmutable
    {
        return $this->unlockedBeehive;
    }

    public function setUnlockedBeehive(): self
    {
        $this->unlockedBeehive = new \DateTimeImmutable();

        return $this;
    }

    public function getBeehive(): ?Beehive
    {
        return $this->beehive;
    }

    public function setBeehive(Beehive $beehive): self
    {
        $this->beehive = $beehive;

        // set the owning side of the relation if necessary
        if ($this !== $beehive->getUser()) {
            $beehive->setUser($this);
        }

        return $this;
    }

    public function getGreenhouse(): ?Greenhouse
    {
        return $this->greenhouse;
    }

    public function setGreenhouse(Greenhouse $greenhouse): self
    {
        $this->greenhouse = $greenhouse;

        // set the owning side of the relation if necessary
        if ($this !== $greenhouse->getOwner()) {
            $greenhouse->setOwner($this);
        }

        return $this;
    }

    public function getRecyclePoints(): int
    {
        return $this->recyclePoints;
    }

    public function increaseRecyclePoints(int $recyclePoints): self
    {
        $this->recyclePoints += $recyclePoints;

        return $this;
    }

    public function getUnlockedRecycling(): ?\DateTimeImmutable
    {
        return $this->unlockedRecycling;
    }

    public function setUnlockedRecycling(): self
    {
        $this->unlockedRecycling = new \DateTimeImmutable();

        return $this;
    }

    public function getUnlockedTrader(): ?\DateTimeImmutable
    {
        return $this->unlockedTrader;
    }

    public function setUnlockedTrader(): self
    {
        $this->unlockedTrader = new \DateTimeImmutable();

        return $this;
    }

    public function getUnreadNews(): int
    {
        return $this->unreadNews;
    }

    public function setUnreadNews(int $unreadNews): self
    {
        $this->unreadNews = $unreadNews;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }
}
