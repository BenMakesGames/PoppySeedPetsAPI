<?php
namespace App\Model;

use App\Entity\PetSpecies;
use Symfony\Component\Serializer\Annotation\Groups;

class PetShelterPet
{
    public const PET_NAMES = [
        'Aalina', 'Aaron', 'Abrahil', 'Addy', 'Aedoc', 'Aelfric', 'Aimery', 'Alain', 'Alda', 'Aldreda', 'Aldus',
        'Alienora', 'Aliette', 'Amée', 'Amis', 'Amphelise', 'Arlotto', 'Artaca', 'Auberi', 'Aureliana',

        'Batu', 'Belka', 'Berislav', 'Bezzhen', 'Biedeluue', 'Blicze', 'Bogdan', 'Bogdana', 'Bogumir', 'Bradan',
        'Bratomil',

        'Cateline', 'Ceinguled', 'Ceri', 'Ceslinus', 'Chedomir', 'Christien', 'Clement', 'Coilean', 'Col', 'Cynbel',
        'Cyra', 'Czestobor',

        'Dagena', 'Dalibor', 'Denyw', 'Dicun', 'Disideri', 'Dmitrei', 'Dragomir', 'Dye',

        'Eda', 'Eileve', 'Elena', 'Elis', 'Elric', 'Emilija', 'Enguerrand', 'Enim', 'Enynny', 'Erasmus', 'Estienne',
        'Eve',

        'Felix', 'Fiora', 'Firmin', 'Fluri', 'Frotlildis',

        'Galine', 'Garnier', 'Garsea', 'Gennoveus', 'Genoveva', 'Geoffroi', 'Gidie', 'Giliana', 'Godelive', 'Gomes',
        'Gosse', 'Gubin', 'Guiscard', 'Gwennan',

        'Hamon', 'Hamon', 'Hopcyn', 'Hunfrid',

        'Ibb', 'Idzi',

        'Jadviga', 'Jehanne', 'Jocosa', 'Josse', 'Jurian',

        'Kaija', 'Kain', 'Kazimir', 'Kima', 'Kinborough', 'Kint', 'Kirik', 'Klara', 'Kryspin',

        'Larkin', 'Leodhild', 'Leon', 'Levi', 'Lorencio', 'Lowri', 'Lucass', 'Ludmila',

        'Maccos', 'Maeldoi', 'Magdalena', 'Makrina', 'Malik', 'Margaret', 'Marsley', 'Masayasu', 'Mateline',
        'Mathias', 'Matty', 'Maurifius', 'Meduil', 'Melita', 'Meoure', 'Merewen', 'Milian', 'Millicent', 'Mold',
        'Molle', 'Montgomery', 'Morys', 'Muriel',

        'Nascimbene', 'Newt', 'Nicholina', 'Nilus', 'Noe', 'Noll', 'Nuño',

        'Onfroi', 'Oswyn',

        'Paperclip', 'Perkhta', 'Pesczek', 'Pridbjørn',

        'Radomil', 'Raven', 'Regina', 'Reina', 'Rimoete', 'Rocatos', 'Rostislav', 'Rozalia', 'Rum', 'Runne', 'Ryd',

        'Saewine', 'Sancha', 'Sandivoi', 'Skenfrith', 'Sulimir', 'Sunnifa', 'Sybil',

        'Taki', 'Talan', 'Tede', 'Temüjin', 'Tephaine', 'Tetris', 'Tiecia', 'Timur', 'Tomila', 'Toregene', 'Trenewydd',

        'Úna', 'Usk',

        'Vasilii', 'Venceslaus', 'Vitseslav', 'Vivka',

        'Wilkin', 'Wilmot', 'Wrexham', 'Wybert', 'Wymond',

        'Ximeno',

        'Yaromir', 'Yrian', 'Ysabeau', 'Ystradewel',

        'Zofija', 'Zuan', 'Zygmunt'
    ];

    public const PET_PIRATE_NAMES = [
        'Captain Jim Starling',
        'Captain Jake Robin',
        'Captain John Parrot',
        'Captain Jorge Dove',
        'Captain Jane Seagull',
        'Captain Jill Hummingbird',
        'Captain Jeff Chickadee',
    ];

    public const PET_HALLOWEEN_NAMES = [
        'Pumpkin', 'Luna', 'Magic', 'Bones', 'Haunt', 'Spirit', 'Cauldron', 'Werewolf', 'Vampire',
    ];

    public const PET_CHRISTMAS_NAMES = [
        'Holly', 'Cocoa', 'Evergreen', 'Santa', 'Dasher', 'Dancer', 'Prancer', 'Vixen', 'Comet', 'Cupid', 'Donner',
        'Blitzen', 'Rudolph', 'Olive', 'Spirit', 'Mint', 'Sol Invictus',
    ];

    public const PET_BLACK_FRIDAY_NAMES = [
        'Madness', 'Despair', 'Desperation', 'Depravity', 'Turpitude', 'Darkness', 'Terror', 'Recoil',
        'Deals', '50% Off', 'Bedlam', 'Chaos', 'Breakdown', 'Spoils',
    ];

    public const PET_CYBER_MONDAY_NAMES = [
        'DLC', '50% Off', 'Meta Tag', 'Redirect', '302', 'robots.txt', 'Heatmap', 'Tracking Cookie', 'SEO', 'CPC',
        'CTR', 'Demographic', 'Lead Magnet', 'Remarketing', 'CTA', 'Bounce Rate', 'Open Rate', 'CPA',
        'Conversion Rate',
    ];

    public const PET_THANKSGIVING_NAMES = [
        'Gobbles', 'Pumpkin', 'Cranberry', 'Turkey', 'Stuffing', 'Mashed Potatoes', 'Gravy', 'Marshmallow',
        'Light Meat', 'Dark Meat', 'Crispy Onion', 'Sweet Potato', 'Brown Sugar', 'Pecan Pie',
    ];

    public const PET_HANUKKAH_NAMES = [
        'Dreidel', 'Olive Oil', 'Potato', 'Pancake', 'Gelt', 'Maccabee', 'Pączki', 'Buñuelo', 'Sufganiyah',
    ];

    public const PET_EASTER_NAMES = [
        'Osterbaum', 'Bunny', 'Rabbit', 'Daffodil', 'Lamb', 'Pastel',
    ];

    public const PET_WINTER_SOLSTICE_NAMES = [
        'Solstice', 'Midwinter', 'Makara', 'Yaldā', 'Yule', 'Dongzhi',
    ];

    public const PET_VALENTINES_NAMES = [
        'Isabeau', 'Margery', 'Lace', 'Coupon', 'Cariño',
    ];

    public const PET_WHITE_DAY_NAMES = [
        'Marshmallow', 'Trắng', 'Cake', 'Cookie', 'Doki-doki', 'Lace',
    ];

    public const PET_CHINESE_ZODIAC_NAMES = [
        '鼠' => [ 'Rat', 'Shǔ' ],
        '牛' => [ 'Ox', 'Niú' ],
        '虎' => [ 'Tiger', 'Hǔ' ],
        '兔' => [ 'Rabbit', 'Tù' ],
        '龙' => [ 'Dragon', 'Lóng' ],
        '蛇' => [ 'Snake', 'Shé' ],
        '马' => [ 'Horse', 'Mǎ' ],
        '羊' => [ 'Goat', 'Yáng' ],
        '猴' => [ 'Monkey', 'Hóu' ],
        '鸡' => [ 'Rooster', 'Jī' ],
        '狗' => [ 'Dog', 'Gǒu' ],
        '猪' => [ 'Pig', 'Zhū' ],
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

    /**
     * @var int
     * @Groups({"petShelterPet"})
     */
    public $scale;
}
