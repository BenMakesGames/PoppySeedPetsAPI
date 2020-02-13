<?php
namespace App\Model;

use App\Entity\PetSpecies;
use Symfony\Component\Serializer\Annotation\Groups;

class PetShelterPet
{
    public const PET_NAMES = [
        'Aalina', 'Aaron', 'Abrahil', 'Aedoc', 'Aelfric', 'Alain', 'Alda', 'Alienora', 'Aliette', 'Artaca',
        'Aureliana', 'Batu', 'Belka', 'Bezzhen', 'Biedeluue', 'Blicze', 'Ceinguled', 'Ceri', 'Ceslinus', 'Christien',
        'Clement', 'Cyra', 'Czestobor', 'Dagena', 'Denyw', 'Disideri', 'Eileve', 'Emilija', 'Enim', 'Enynny',
        'Erasmus', 'Eve', 'Felix', 'Fiora', 'Fluri', 'Frotlildis', 'Galine', 'Gennoveus', 'Genoveva', 'Giliana',
        'Godelive', 'Gubin', 'Idzi', 'Jadviga', 'Jehanne', 'Kaija', 'Kain', 'Kima', 'Kint', 'Kirik',
        'Klara', 'Kryspin', 'Leodhild', 'Leon', 'Levi', 'Lowri', 'Lucass', 'Ludmila', 'Maccos', 'Maeldoi',
        'Magdalena', 'Makrina', 'Malik', 'Margaret', 'Marsley', 'Masayasu', 'Mateline', 'Mathias', 'Maurifius', 'Meduil',
        'Melita', 'Meoure', 'Merewen', 'Milesent', 'Milian', 'Mold', 'Montgomery', 'Morys', 'Newt', 'Nicholina',
        'Nilus', 'Noe', 'Oswyn', 'Paperclip', 'Perkhta', 'Pesczek', 'Regina', 'Reina', 'Rimoete', 'Rocatos',
        'Rozalia', 'Rum', 'Runne', 'Ryd', 'Saewine', 'Sandivoi', 'Skenfrith', 'Sulimir', 'Sybil', 'Talan',
        'Tede', 'Tephaine', 'Tetris', 'Tiecia', 'Toregene', 'Trenewydd', 'Usk', 'Vasilii', 'Vitseslav', 'Vivka',
        'Wrexham', 'Ysabeau', 'Ystradewel', 'Zofija', 'Zygmunt'
    ];

    public const PET_HALLOWEEN_NAMES = [
        'Pumpkin', 'Luna', 'Magic', 'Bones', 'Haunt', 'Spirit', 'Cauldron', 'Werewolf', 'Vampire',
    ];

    public const PET_CHRISTMAS_NAMES = [
        'Holly', 'Cocoa', 'Evergreen', 'Santa', 'Dasher', 'Dancer', 'Prancer', 'Vixen', 'Comet', 'Cupid', 'Donner',
        'Blitzen', 'Rudolph', 'Olive', 'Spirit', 'Mint', 'Sol Invictus',
    ];

    public const PET_THANKSGIVING_NAMES = [
        'Gobbles', 'Pumpkin', 'Cranberry', 'Turkey', 'Stuffing', 'Potato', 'Gravy',
    ];

    public const PET_HANNUKAH_NAMES = [
        'Dreidel', 'Olive Oil', 'Potato', 'Pancake', 'Gelt', 'Maccabee', 'Pączki', 'Buñuelo', 'Sufganiyah',
    ];

    public const PET_EASTER_NAMES = [
        'Osterbaum', 'Bunny', 'Rabbit', 'Daffodil', 'Lamb', 'Pastel',
    ];

    public const PET_SOLSTICE_NAMES = [
        'Solstice', 'Midwinter', 'Makara', 'Yaldā', 'Yule', 'Dongzhi',
    ];

    public const PET_VALENTINES_NAMES = [
        'Isabeau', 'Margery', 'Lace', 'Coupon', 'Cariño',
    ];

    public const PET_WHITE_DAY_NAMES = [
        'Marshmallow', 'Trắng', 'Cake', 'Cookie', 'Doki-doki', 'Lace',
    ];

    /**
     * @var int
     * @Groups({"petShelterPet"})
     */
    public $id;

    /**
     * @var string
     * @Groups({"petShelterPet"})
     */
    public $name;

    /**
     * @var PetSpecies
     * @Groups({"petShelterPet"})
     */
    public $species;

    /**
     * @var string
     * @Groups({"petShelterPet"})
     */
    public $colorA;

    /**
     * @var string
     * @Groups({"petShelterPet"})
     */
    public $colorB;
}
