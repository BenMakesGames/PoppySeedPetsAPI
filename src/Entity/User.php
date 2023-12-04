<?php

namespace App\Entity;

use App\Enum\EnumInvalidValueException;
use App\Enum\UnlockableFeatureEnum;
use App\Functions\ArrayFunctions;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table]
#[ORM\Index(name: 'name_idx', columns: ['name'])]
#[ORM\Index(name: 'last_activity_idx', columns: ['last_activity'])]
#[ORM\Entity]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const MAX_HOUSE_INVENTORY = 100;
    public const MAX_BASEMENT_INVENTORY = 10000;

    /**
     * @Groups({"myAccount", "myInventory", "userPublicProfile", "article", "petPublicProfile", "museum", "parkEvent", "userTypeahead", "publicStyle", "myFollowers"})
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Groups(['myAccount'])]
    private $email;

    #[ORM\Column(type: 'json')]
    #[Groups(['myAccount'])]
    private $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: 'string')]
    private $password;

    /**
     * @Groups({"myAccount", "myInventory", "userPublicProfile", "article", "petPublicProfile", "museum", "parkEvent", "userTypeahead", "publicStyle", "myFollowers"})
     */
    #[ORM\Column(type: 'string', length: 40)]
    private $name;

    /**
     * @Groups({"userPublicProfile", "myFollowers"})
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private $lastActivity;

    #[ORM\OneToMany(targetEntity: Pet::class, mappedBy: 'owner', fetch: 'EXTRA_LAZY')]
    private $pets;

    /**
     * @Groups({"userPublicProfile"})
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private $registeredOn;

    /**
     * @Groups({"myAccount"})
     */
    #[ORM\Column(type: 'date_immutable')]
    private $lastAllowanceCollected;

    #[ORM\Column(type: 'boolean')]
    private $isLocked = false;

    /**
     * @Groups({"myAccount"})
     */
    #[ORM\Column(type: 'integer')]
    private $moneys = 0;

    /**
     * @Groups({"myAccount"})
     */
    #[ORM\Column(type: 'integer')]
    private $maxPets = 2;

    #[ORM\OneToMany(targetEntity: UserFollowing::class, mappedBy: 'user', orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    private $following;

    #[ORM\OneToMany(targetEntity: UserFollowing::class, mappedBy: 'following', orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    private $followedBy;

    #[ORM\OneToMany(targetEntity: 'App\Entity\UserStats', mappedBy: 'user', orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    private $stats;

    /**
     * @Groups({"myAccount"})
     */
    #[ORM\Column(type: 'integer')]
    private $defaultSessionLengthInHours = 72;

    /**
     * @Groups({"myAccount"})
     */
    #[ORM\Column(type: 'integer')]
    private $maxSellPrice = 10;

    #[ORM\OneToOne(targetEntity: 'App\Entity\PassphraseResetRequest', mappedBy: 'user', cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    private $passphraseResetRequest;

    #[ORM\OneToMany(targetEntity: 'App\Entity\GreenhousePlant', mappedBy: 'owner', orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    private $greenhousePlants;

    #[ORM\OneToMany(targetEntity: 'App\Entity\UserSession', mappedBy: 'user', orphanRemoval: true)]
    private $userSessions;

    #[ORM\OneToOne(targetEntity: 'App\Entity\HollowEarthPlayer', mappedBy: 'user', cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    private $hollowEarthPlayer;

    #[ORM\OneToOne(targetEntity: 'App\Entity\Fireplace', mappedBy: 'user', cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    private $fireplace;

    #[ORM\OneToOne(targetEntity: 'App\Entity\Beehive', mappedBy: 'user', cascade: ['persist', 'remove'])]
    private $beehive;

    #[ORM\OneToOne(targetEntity: 'App\Entity\Greenhouse', mappedBy: 'owner', cascade: ['persist', 'remove'])]
    private $greenhouse;

    /**
     * @Groups({"myAccount"})
     */
    #[ORM\Column(type: 'integer')]
    private $recyclePoints = 0;

    /**
     * @Groups({"myAccount"})
     */
    #[ORM\Column(type: 'smallint')]
    private $unreadNews = 0;

    /**
     * @Groups({"myAccount", "userPublicProfile", "petPublicProfile", "museum", "parkEvent", "publicStyle", "myFollowers"})
     */
    #[ORM\Column(type: 'string', length: 60, nullable: true)]
    private $icon;

    /**
     * @Groups({"myAccount"})
     */
    #[ORM\Column(type: 'smallint')]
    private $maxMarketBids = 5;

    #[ORM\OneToOne(targetEntity: UserMenuOrder::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private $menuOrder;

    #[ORM\OneToMany(targetEntity: UserUnlockedAura::class, mappedBy: 'user', orphanRemoval: true)]
    private $unlockedAuras;

    #[ORM\Column(type: 'integer')]
    private $museumPoints = 0;

    #[ORM\Column(type: 'integer')]
    private $museumPointsSpent = 0;

    /**
     * @Groups({"myAccount"})
     */
    #[ORM\Column(type: 'boolean')]
    private $canAssignHelpers = false;

    #[ORM\Column(type: 'integer')]
    private $fate;

    /**
     * @Groups({"myAccount"})
     */
    #[ORM\OneToMany(targetEntity: UserUnlockedFeature::class, mappedBy: 'user', orphanRemoval: true)]
    private $unlockedFeatures;

    #[ORM\OneToMany(targetEntity: UserBadge::class, mappedBy: 'user', orphanRemoval: true)]
    private $badges;

    /**
     * @Groups({"myAccount"})
     */
    #[ORM\OneToOne(targetEntity: UserSubscription::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private $subscription;

    #[ORM\OneToMany(targetEntity: UserFieldGuideEntry::class, mappedBy: 'user', orphanRemoval: true)]
    private $fieldGuideEntries;

    public function __construct()
    {
        $this->pets = new ArrayCollection();
        $this->registeredOn = new \DateTimeImmutable();
        $this->lastAllowanceCollected = (new \DateTimeImmutable())->modify('-7 days');
        $this->following = new ArrayCollection();
        $this->stats = new ArrayCollection();
        $this->greenhousePlants = new ArrayCollection();
        $this->userSessions = new ArrayCollection();
        $this->unlockedAuras = new ArrayCollection();
        $this->fate = mt_rand(0, 2147483647);
        $this->unlockedFeatures = new ArrayCollection();
        $this->badges = new ArrayCollection();
        $this->fieldGuideEntries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUsername(): string
    {
        return $this->email;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt(): ?string
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
        return null;
    }

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

    /**
     * @deprecated use TransactionService::getMoneys and spendMoneys instead
     */
    public function increaseMoneys(int $amount): self
    {
        $this->moneys += $amount;

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

    public function getMaxSellPrice(): ?int
    {
        return $this->maxSellPrice;
    }

    public function setMaxSellPrice(int $maxSellPrice): self
    {
        $this->maxSellPrice = $maxSellPrice;

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

    /**
     * @deprecated use TransactionService::getRecyclingPoints and spendRecyclingPoints instead
     */
    public function increaseRecyclePoints(int $recyclePoints): self
    {
        $this->recyclePoints += $recyclePoints;

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

    public function getMaxMarketBids(): int
    {
        return $this->maxMarketBids;
    }

    public function increaseMaxMarketBids(int $amount): self
    {
        $this->maxMarketBids += $amount;

        return $this;
    }

    public function getMenuOrder(): ?UserMenuOrder
    {
        return $this->menuOrder;
    }

    public function setMenuOrder(UserMenuOrder $menuOrder): self
    {
        // set the owning side of the relation if necessary
        if ($menuOrder->getUser() !== $this) {
            $menuOrder->setUser($this);
        }

        $this->menuOrder = $menuOrder;

        return $this;
    }

    /**
     * @return Collection|UserUnlockedAura[]
     */
    public function getUnlockedAuras(): Collection
    {
        return $this->unlockedAuras;
    }

    public function addUnlockedAura(UserUnlockedAura $unlockedAura): self
    {
        if (!$this->unlockedAuras->contains($unlockedAura)) {
            $this->unlockedAuras[] = $unlockedAura;
            $unlockedAura->setUser($this);
        }

        return $this;
    }

    public function getMuseumPoints(): int
    {
        return $this->museumPoints;
    }

    /**
     * @deprecated use TransactionService::getMuseumFavor instead
     */
    public function addMuseumPoints(int $museumPoints): self
    {
        $this->museumPoints += $museumPoints;

        return $this;
    }

    public function getMuseumPointsSpent(): int
    {
        return $this->museumPointsSpent;
    }

    /**
     * @deprecated use TransactionService::spendMuseumFavor instead
     */
    public function addMuseumPointsSpent(int $museumPointsSpent): self
    {
        $this->museumPointsSpent += $museumPointsSpent;

        return $this;
    }

    public function getCanAssignHelpers(): ?bool
    {
        return $this->canAssignHelpers;
    }

    public function setCanAssignHelpers(bool $canAssignHelpers): self
    {
        $this->canAssignHelpers = $canAssignHelpers;

        return $this;
    }

    public function getFate(): int
    {
        return $this->fate;
    }

    public function setFate(): self
    {
        $this->fate = mt_rand(0, 2147483647);

        return $this;
    }

    public function getDailySeed()
    {
        return (($this->fate * date('N')) % (date('nd') * 53)) + (int)date('Yj');
    }

    /**
     * @return Collection<int, UserUnlockedFeature>
     */
    public function getUnlockedFeatures(): Collection
    {
        return $this->unlockedFeatures;
    }

    public function addUnlockedFeature(UserUnlockedFeature $unlockedFeature): self
    {
        if (!$this->unlockedFeatures->contains($unlockedFeature)) {
            $this->unlockedFeatures[] = $unlockedFeature;
            $unlockedFeature->setUser($this);
        }

        return $this;
    }

    public function hasUnlockedFeature(string $feature): bool
    {
        if(!UnlockableFeatureEnum::isAValue($feature))
            throw new EnumInvalidValueException(UnlockableFeatureEnum::class, $feature);

        return $this->unlockedFeatures->exists(
            fn($key, UserUnlockedFeature $unlockedFeature) => $unlockedFeature->getFeature() === $feature
        );
    }

    public function getUnlockedFeatureDate(string $feature)
    {
        if(!UnlockableFeatureEnum::isAValue($feature))
            throw new EnumInvalidValueException(UnlockableFeatureEnum::class, $feature);

        $unlockedFeature = ArrayFunctions::find_one(
            $this->unlockedFeatures,
            fn(UserUnlockedFeature $unlockedFeature) => $unlockedFeature->getFeature() === $feature
        );

        if(!$unlockedFeature)
            return null;

        return $unlockedFeature->getUnlockedOn();
    }

    /**
     * @return Collection<int, UserBadge>
     */
    public function getBadges(): Collection
    {
        return $this->badges;
    }

    public function addBadge(UserBadge $badge): self
    {
        if (!$this->badges->contains($badge)) {
            $this->badges[] = $badge;
            $badge->setUser($this);
        }

        return $this;
    }

    public function getSubscription(): ?UserSubscription
    {
        return $this->subscription;
    }

    public function setSubscription(?UserSubscription $subscription): self
    {
        // unset the owning side of the relation if necessary
        if ($subscription === null && $this->subscription !== null) {
            $this->subscription->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($subscription !== null && $subscription->getUser() !== $this) {
            $subscription->setUser($this);
        }

        $this->subscription = $subscription;

        return $this;
    }

    /**
     * @return Collection<int, UserFieldGuideEntry>
     */
    public function getFieldGuideEntries(): Collection
    {
        return $this->fieldGuideEntries;
    }
}
