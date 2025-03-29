<?php
declare(strict_types=1);

namespace App\Model;

use App\Entity\PetSpecies;
use Symfony\Component\Serializer\Attribute\Groups;

final class PetShelterPet
{
    public const PetNames = [
        'Aalina', 'Aaron', 'Abrahil', 'Addy', 'Aedoc', 'Aelfric', 'Aimery', 'Alain', 'Alda', 'Aldreda', 'Aldus',
        'Alienora', 'Aliette', 'Amée', 'Amis', 'Amphelise', 'Arlotto', 'Artaca', 'Auberi', 'Aureliana',

        'Batu', 'Belka', 'Ben', 'Berislav', 'Bezzhen', 'Biedeluue', 'Blicze', 'Bogdan', 'Bogdana', 'Bogumir', 'Borscht', 'Bradan',
        'Bratomil',

        'Cat', 'Cateline', 'Ceinguled', 'Ceri', 'Ceslinus', 'Chedomir', 'Christien', 'Clement', 'Coilean', 'Col', 'Cookie', 'Cynbel',
        'Cyra', 'Czestobor',

        'Dagena', 'Dalibor', 'Denyw', 'Dicun', 'Disideri', 'Dmitrei', 'Dog', 'Dragomir', 'Dye',

        'Eda', 'Eileve', 'Elena', 'Elis', 'Elric', 'Emilija', 'Enguerrand', 'Enim', 'Enynny', 'Erasmus', 'Estienne',
        'Eve',

        'Felix', 'Fennel', 'Fiora', 'Firmin', 'Fluri', 'Frotlildis',

        'Galine', 'Garnier', 'Garsea', 'Gene', 'Gennoveus', 'Genoveva', 'Geoffroi', 'Gidie', 'Giliana', 'Godelive', 'Gomes',
        'Gosse', 'Gregory', 'Gubin', 'Guiscard', 'Gwennan',

        'Hamon', 'Hamon', 'Hippu', 'Honey', 'Hopcyn', 'Hunfrid',

        'Ibb', 'Idzi', 'Ingot', 'Ink', 'Iron', 'Isle', 'Ismo',

        'Jadviga', 'Jehanne', 'Jocosa', 'Josse', 'Jurian', 'Juniper',

        'Kaija', 'Kain', 'Kale', 'Karma', 'Kazimir', 'Kima', 'Kinborough', 'Kint', 'Kirik', 'Klara', 'Kryspin', 'Kukka',

        'Larkin', 'Leodhild', 'Leon', 'Levi', 'Lorencio', 'Lowri', 'Lucass', 'Ludmila', 'Lumi',

        'Maccos', 'Maeldoi', 'Magdalena', 'Makrina', 'Malik', 'Margaret', 'Marsley', 'Masayasu', 'Mateline',
        'Mathias', 'Matty', 'Maurifius', 'Mayonnaise', 'Meduil', 'Melita', 'Meoure', 'Merewen', 'Milian', 'Millicent', 'Mold',
        'Molle', 'Montgomery', 'Morys', 'Muriel',

        'Nascimbene', 'Nate', 'Newt', 'Nicholina', 'Nilus', 'Noe', 'Noll', 'Nuño',

        'Onfroi', 'Orchid', 'Oregano', 'Origami', 'Oswyn', 'Owen',

        'Paperclip', 'Perkhta', 'Poppy', 'Pesczek', 'Pridbjørn', 'Pyry',

        'Radomil', 'Raven', 'Regina', 'Reina', 'Rihanna', 'Rimoete', 'Rocatos', 'Roger', 'Rostislav', 'Rosehip', 'Rozalia', 'Rum', 'Runne', 'Ryd',

        'Saewine', 'Sancha', 'Sandivoi', 'Schmitty', 'Sisu', 'Seppo', 'Skenfrith', 'Sulimir', 'Sunnifa', 'Sybil',

        'Taki', 'Talan', 'Tede', 'Temüjin', 'Tephaine', 'Theodore', 'Tetris', 'Tiecia', 'Timur', 'Tomila', 'Toregene', 'Trenewydd', 'Tuli',

        'Ulla', 'Úna', 'Umami', 'Umbra', 'Unit', 'Usk',

        'Vasilii', 'Venceslaus', 'Vesi', 'Vine', 'Vitseslav', 'Vivka',

        'Wallace', 'Wilkin', 'Wilmot', 'Wrexham', 'Wybert', 'Wymond',

        'Xanadu', 'Xi', 'Ximeno', 'Xoxo',

        'Yaromir', 'Yrian', 'Ysabeau', 'Ystradewel',

        'Zofija', 'Zen', 'Zuan', 'Zygmunt'
    ];

    public static function generatePirateName(\DateTimeImmutable $dt, int $index)
    {
        $i1 = (int)$dt->format('jz') + $index * (int)$dt->format('w') * 71; // 71 = a small prime
        $i2 = (int)$dt->format('Yz') + $index * (int)$dt->format('N') * 167; // 167 = a slightly-larger prime

        // all names have one syllable. except Jorge.
        $firstNames = [
            'Jake', 'Jade', 'Jane', 'Jay', 'Jax', 'Jaz', 'Jean', 'Jed', 'Jeff', 'Jen', 'Jess', 'Jet', 'Jewel', 'Jill',
            'Jim', 'Jin', 'Jo', 'Joan', 'Joe', 'Joel', 'John', 'Joon', 'Jorge', 'Joy', 'Joyce', 'Juan', 'Jude', 'Jules',
            'Jun',
        ];

        // all names have two syllables
        $birds = [
            'Robin', 'Seagull', 'Spearow', 'Starling', 'Parrot', 'Nuthatch', 'Titmouse', 'Puffin', 'Goldfinch',
            'Warbler', 'Penguin', 'Owlet', 'Martin', 'Magpie', 'Antwren', 'Lovebird', 'Fruitdove', 'Waxbill',
        ];

        $firstName = $firstNames[$i1 % count($firstNames)];
        $lastName = $birds[$i2 % count($birds)];

        return 'Captain ' . $firstName . ' ' . $lastName;
    }


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

    #[Groups(['petShelterPet'])]
    public int $id;

    #[Groups(['petShelterPet'])]
    public string $name;

    #[Groups(['petShelterPet'])]
    public PetSpecies $species;

    #[Groups(['petShelterPet'])]
    public string $colorA;

    #[Groups(['petShelterPet'])]
    public string $colorB;

    #[Groups(['petShelterPet'])]
    public string $label;

    #[Groups(['petShelterPet'])]
    public int $scale;
}
