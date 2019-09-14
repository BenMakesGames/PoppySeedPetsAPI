<?php

namespace App\Entity;

use App\Enum\FlavorEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ItemFoodRepository")
 */
class ItemFood
{
    public const FLAVOR_FIELDS = [
        ''
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $food = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $love = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $junk = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $alcohol = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $caffeine = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $psychedelic = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $earthy = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $fruity = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $tannic = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $spicy = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $creamy = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $meaty = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $planty = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $fishy = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $floral = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $fatty = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $oniony = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $chemicaly = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFood(): ?int
    {
        return $this->food;
    }

    public function setFood(int $food): self
    {
        $this->food = $food;

        return $this;
    }

    public function getLove(): ?int
    {
        return $this->love;
    }

    public function setLove(int $love): self
    {
        $this->love = $love;

        return $this;
    }

    public function getJunk(): ?int
    {
        return $this->junk;
    }

    public function setJunk(int $junk): self
    {
        $this->junk = $junk;

        return $this;
    }

    public function getAlcohol(): ?int
    {
        return $this->alcohol;
    }

    public function setAlcohol(int $alcohol): self
    {
        $this->alcohol = $alcohol;

        return $this;
    }

    public function getCaffeine(): int
    {
        return $this->caffeine;
    }

    public function setCaffeine(int $caffeine): self
    {
        $this->caffeine = $caffeine;

        return $this;
    }

    public function getPsychedelic(): int
    {
        return $this->psychedelic;
    }

    public function setPsychedelic(int $psychedelic): self
    {
        $this->psychedelic = $psychedelic;

        return $this;
    }

    public function getEarthy(): ?int
    {
        return $this->earthy;
    }

    public function setEarthy(int $earthy): self
    {
        $this->earthy = $earthy;

        return $this;
    }

    public function getFruity(): ?int
    {
        return $this->fruity;
    }

    public function setFruity(int $fruity): self
    {
        $this->fruity = $fruity;

        return $this;
    }

    public function getTannic(): ?int
    {
        return $this->tannic;
    }

    public function setTannic(int $tannic): self
    {
        $this->tannic = $tannic;

        return $this;
    }

    public function getSpicy(): ?int
    {
        return $this->spicy;
    }

    public function setSpicy(int $spicy): self
    {
        $this->spicy = $spicy;

        return $this;
    }

    public function getCreamy(): ?int
    {
        return $this->creamy;
    }

    public function setCreamy(int $creamy): self
    {
        $this->creamy = $creamy;

        return $this;
    }

    public function getMeaty(): ?int
    {
        return $this->meaty;
    }

    public function setMeaty(int $meaty): self
    {
        $this->meaty = $meaty;

        return $this;
    }

    public function getPlanty(): ?int
    {
        return $this->planty;
    }

    public function setPlanty(int $planty): self
    {
        $this->planty = $planty;

        return $this;
    }

    public function getFishy(): ?int
    {
        return $this->fishy;
    }

    public function setFishy(int $fishy): self
    {
        $this->fishy = $fishy;

        return $this;
    }

    public function getFloral(): ?int
    {
        return $this->floral;
    }

    public function setFloral(int $floral): self
    {
        $this->floral = $floral;

        return $this;
    }

    public function getFatty(): ?int
    {
        return $this->fatty;
    }

    public function setFatty(int $fatty): self
    {
        $this->fatty = $fatty;

        return $this;
    }

    public function getOniony(): ?int
    {
        return $this->oniony;
    }

    public function setOniony(int $oniony): self
    {
        $this->oniony = $oniony;

        return $this;
    }

    public function getChemicaly(): ?int
    {
        return $this->chemicaly;
    }

    public function setChemicaly(int $chemicaly): self
    {
        $this->chemicaly = $chemicaly;

        return $this;
    }

    public function add(ItemFood $f): ItemFood
    {
        $added = clone $this;

        if($f === null) return $added;

        $added->food += $f->food;
        $added->love += $f->love;
        $added->junk += $f->junk;
        $added->alcohol += $f->alcohol;

        return $added;
    }

    public function multiply(int $f): ItemFood
    {
        $multiplied = clone $this;

        $multiplied->food *= $f;
        $multiplied->love *= $f;
        $multiplied->junk *= $f;
        $multiplied->alcohol *= $f;

        return $multiplied;
    }

    /**
     * @Groups({"myInventory", "itemEncyclopedia"})
     */
    public function getModifiers(): array
    {
        $modifiers = [];

        if($this->food >= 10)
            $modifiers[] = 'huge meal';
        else if($this->food >= 6)
            $modifiers[] = 'meal';
        else if($this->food >= 3)
            $modifiers[] = 'small meal';
        else if($this->food >= 1)
            $modifiers[] = 'snack';
        else
            $modifiers[] = 'no food value';

        if($this->love > 2) $modifiers[] = 'all pets love to eat this food';
        else if($this->love > 0) $modifiers[] = 'all pets like to eat this food';
        else if($this->love < 0) $modifiers[] = 'all pets hate to eat this food';

        if($this->junk > 0) $modifiers[] = 'a junk food';
        else if($this->junk < 0) $modifiers[] = 'a healthy food';

        if($this->alcohol > 0) $modifiers[] = 'alcoholic';
        if($this->caffeine > 0) $modifiers[] = 'caffeinated';
        if($this->psychedelic > 0) $modifiers[] = 'trippy';

        foreach(FlavorEnum::getValues() as $flavor)
        {
            if($this->$flavor > 2) $modifiers[] = 'very ' . $flavor;
            else if($this->$flavor > 0) $modifiers[] = $flavor;
        }

        return $modifiers;
    }
}
