<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table]
#[ORM\UniqueConstraint(name: 'user_id_name_idx', columns: ['user_id', 'name'])]
#[ORM\Entity]
class UserStyle
{
    public const CURRENT = 'Current';

    const PROPERTIES = [
        'backgroundColor',
        'speechBubbleBackgroundColor',
        'textColor',
        'primaryColor',
        'textOnPrimaryColor',
        'tabBarBackgroundColor',
        'linkAndButtonColor',
        'buttonTextColor',
        'dialogLinkColor',
        'warningColor',
        'gainColor',
        'bonusAndSpiceColor',
        'bonusAndSpiceSelectedColor',
        'inputBackgroundColor',
        'inputTextColor'
    ];

    #[Groups(["myStyle", "publicStyle"])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[Groups(["publicStyle"])]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[Groups(["myStyle"])]
    #[ORM\Column(type: 'string', length: 40)]
    private $name;

    #[Groups(["myStyle", "publicStyle"])]
    #[ORM\Column(type: 'string', length: 6)]
    private $backgroundColor;

    #[Groups(["myStyle", "publicStyle"])]
    #[ORM\Column(type: 'string', length: 6)]
    private $speechBubbleBackgroundColor;

    #[Groups(["myStyle", "publicStyle"])]
    #[ORM\Column(type: 'string', length: 6)]
    private $textColor;

    #[Groups(["myStyle", "publicStyle"])]
    #[ORM\Column(type: 'string', length: 6)]
    private $primaryColor;

    #[Groups(["myStyle", "publicStyle"])]
    #[ORM\Column(type: 'string', length: 6)]
    private $textOnPrimaryColor;

    #[Groups(["myStyle", "publicStyle"])]
    #[ORM\Column(type: 'string', length: 6)]
    private $tabBarBackgroundColor;

    #[Groups(["myStyle", "publicStyle"])]
    #[ORM\Column(type: 'string', length: 6)]
    private $linkAndButtonColor;

    #[Groups(["myStyle", "publicStyle"])]
    #[ORM\Column(type: 'string', length: 6)]
    private $buttonTextColor;

    #[Groups(["myStyle", "publicStyle"])]
    #[ORM\Column(type: 'string', length: 6)]
    private $dialogLinkColor;

    #[Groups(["myStyle", "publicStyle"])]
    #[ORM\Column(type: 'string', length: 6)]
    private $warningColor;

    #[Groups(["myStyle", "publicStyle"])]
    #[ORM\Column(type: 'string', length: 6)]
    private $gainColor;

    #[Groups(["myStyle", "publicStyle"])]
    #[ORM\Column(type: 'string', length: 6)]
    private $bonusAndSpiceColor;

    #[Groups(["myStyle", "publicStyle"])]
    #[ORM\Column(type: 'string', length: 6)]
    private $bonusAndSpiceSelectedColor;

    #[Groups(["myStyle", "publicStyle"])]
    #[ORM\Column(type: 'string', length: 6)]
    private $inputBackgroundColor;

    #[Groups(["myStyle", "publicStyle"])]
    #[ORM\Column(type: 'string', length: 6)]
    private $inputTextColor;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
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

    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(string $backgroundColor): self
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    public function getSpeechBubbleBackgroundColor(): ?string
    {
        return $this->speechBubbleBackgroundColor;
    }

    public function setSpeechBubbleBackgroundColor(string $speechBubbleBackgroundColor): self
    {
        $this->speechBubbleBackgroundColor = $speechBubbleBackgroundColor;

        return $this;
    }

    public function getTextColor(): ?string
    {
        return $this->textColor;
    }

    public function setTextColor(string $textColor): self
    {
        $this->textColor = $textColor;

        return $this;
    }

    public function getPrimaryColor(): ?string
    {
        return $this->primaryColor;
    }

    public function setPrimaryColor(string $primaryColor): self
    {
        $this->primaryColor = $primaryColor;

        return $this;
    }

    public function getTextOnPrimaryColor(): ?string
    {
        return $this->textOnPrimaryColor;
    }

    public function setTextOnPrimaryColor(string $textOnPrimaryColor): self
    {
        $this->textOnPrimaryColor = $textOnPrimaryColor;

        return $this;
    }

    public function getTabBarBackgroundColor(): ?string
    {
        return $this->tabBarBackgroundColor;
    }

    public function setTabBarBackgroundColor(string $tabBarBackgroundColor): self
    {
        $this->tabBarBackgroundColor = $tabBarBackgroundColor;

        return $this;
    }

    public function getLinkAndButtonColor(): ?string
    {
        return $this->linkAndButtonColor;
    }

    public function setLinkAndButtonColor(string $linkAndButtonColor): self
    {
        $this->linkAndButtonColor = $linkAndButtonColor;

        return $this;
    }

    public function getButtonTextColor(): ?string
    {
        return $this->buttonTextColor;
    }

    public function setButtonTextColor(string $buttonTextColor): self
    {
        $this->buttonTextColor = $buttonTextColor;

        return $this;
    }

    public function getDialogLinkColor(): ?string
    {
        return $this->dialogLinkColor;
    }

    public function setDialogLinkColor(string $dialogLinkColor): self
    {
        $this->dialogLinkColor = $dialogLinkColor;

        return $this;
    }

    public function getWarningColor(): ?string
    {
        return $this->warningColor;
    }

    public function setWarningColor(string $warningColor): self
    {
        $this->warningColor = $warningColor;

        return $this;
    }

    public function getGainColor(): ?string
    {
        return $this->gainColor;
    }

    public function setGainColor(string $gainColor): self
    {
        $this->gainColor = $gainColor;

        return $this;
    }

    public function getBonusAndSpiceColor(): ?string
    {
        return $this->bonusAndSpiceColor;
    }

    public function setBonusAndSpiceColor(string $bonusAndSpiceColor): self
    {
        $this->bonusAndSpiceColor = $bonusAndSpiceColor;

        return $this;
    }

    public function getBonusAndSpiceSelectedColor(): ?string
    {
        return $this->bonusAndSpiceSelectedColor;
    }

    public function setBonusAndSpiceSelectedColor(string $bonusAndSpiceSelectedColor): self
    {
        $this->bonusAndSpiceSelectedColor = $bonusAndSpiceSelectedColor;

        return $this;
    }

    public function getInputBackgroundColor(): ?string
    {
        return $this->inputBackgroundColor;
    }

    public function setInputBackgroundColor(string $inputBackgroundColor): self
    {
        $this->inputBackgroundColor = $inputBackgroundColor;

        return $this;
    }

    public function getInputTextColor(): ?string
    {
        return $this->inputTextColor;
    }

    public function setInputTextColor(string $inputTextColor): self
    {
        $this->inputTextColor = $inputTextColor;

        return $this;
    }
}
