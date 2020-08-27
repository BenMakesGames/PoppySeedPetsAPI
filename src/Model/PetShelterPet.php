<?php
namespace App\Model;

use App\Entity\PetSpecies;
use Symfony\Component\Serializer\Annotation\Groups;

class PetShelterPet
{
    public const PET_NAMES = [
        'Aalina', 'Aaron', 'Abrahil', 'Aedoc', 'Aelfric', 'Alain', 'Alda', 'Aldreda', 'Aldus', 'Alienora', 'Aliette',
        'Amis', 'Artaca', 'Aureliana',

        'Batu', 'Belka', 'Berislav', 'Bezzhen', 'Biedeluue', 'Blicze', 'Bogdan', 'Bogdana',

        'Cateline', 'Ceinguled', 'Ceri', 'Ceslinus', 'Chedomir', 'Christien', 'Clement', 'Col', 'Cyra', 'Czestobor',

        'Dagena', 'Denyw', 'Dicun', 'Disideri', 'Dmitrei', 'Dragomir',

        'Eda', 'Eileve', 'Elis', 'Emilija', 'Enguerrand', 'Enim', 'Enynny', 'Erasmus', 'Estienne', 'Eve',

        'Felix', 'Fiora', 'Firmin', 'Fluri', 'Frotlildis',

        'Galine', 'Garnier', 'Garsea', 'Gennoveus', 'Genoveva', 'Geoffroi', 'Gidie', 'Giliana', 'Godelive', 'Gomes',
        'Gubin',

        'Hamon', 'Hamon',

        'Ibb', 'Idzi',

        'Jadviga', 'Jehanne', 'Jurian',

        'Kaija', 'Kain', 'Kima', 'Kinborough', 'Kint', 'Kirik', 'Klara', 'Kryspin',

        'Larkin', 'Leodhild', 'Leon', 'Levi', 'Lorencio', 'Lowri', 'Lucass', 'Ludmila',

        'Maccos', 'Maeldoi', 'Magdalena', 'Makrina', 'Malik', 'Margaret', 'Marsley', 'Masayasu', 'Mateline',
        'Mathias', 'Matty', 'Maurifius', 'Meduil', 'Melita', 'Meoure', 'Merewen', 'Milesent', 'Milian', 'Mold',
        'Montgomery', 'Morys',

        'Newt', 'Nicholina', 'Nilus', 'Noe', 'Nuño',

        'Onfroi', 'Oswyn',

        'Paperclip', 'Perkhta', 'Pesczek',

        'Radomil', 'Raven', 'Regina', 'Reina', 'Rimoete', 'Rocatos', 'Rostislav', 'Rozalia', 'Rum', 'Runne', 'Ryd',

        'Saewine', 'Sancha', 'Sandivoi', 'Skenfrith', 'Sulimir', 'Sybil',

        'Taki', 'Talan', 'Tede', 'Tephaine', 'Tetris', 'Tiecia', 'Timur', 'Toregene', 'Trenewydd',

        'Usk',

        'Vasilii', 'Vitseslav', 'Vivka',

        'Wilkin', 'Wrexham', 'Wymond',

        'Yaromir', 'Yrian', 'Ysabeau', 'Ystradewel',

        'Zofija', 'Zygmunt'
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

    /**
     * @var string
     * @Groups({"petShelterPet"})
     */
    public $label;
}
