<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"logIn", "myInventory", "publicProfile"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
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
     * @Groups({"logIn", "myInventory", "publicProfile"})
     */
    private $name;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"semiPrivateProfile"})
     */
    private $lastActivity;

    /**
     * @ORM\Column(type="string", length=40, unique=true)
     * @Groups({"logIn"})
     */
    private $sessionId;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $sessionExpiration;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Pet", mappedBy="owner")
     * @Groups({"publicProfile"})
     */
    private $pets;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"semiPrivateProfile"})
     */
    private $registeredOn;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"logIn"})
     */
    private $lastAllowanceCollected;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"privateProfile"})
     */
    private $isLocked;

    public function __construct()
    {
        $this->pets = new ArrayCollection();
        $this->registeredOn = new \DateTimeImmutable();
        $this->lastAllowanceCollected = (new \DateTimeImmutable())->modify('-7 days');
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

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
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
        $this->sessionExpiration = (new \DateTimeImmutable())->modify('+8 hours');

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
}
