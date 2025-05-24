<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Model;

use App\Entity\PetSpecies;
use Symfony\Component\Serializer\Attribute\Groups;

final class PetShelterPet
{
    public const array PetNames = [
        'Aalina', 'Aaron', 'Abrahil', 'Addy', 'Aedoc', 'Aelfric', 'Aimery', 'Alain','Alcubierre', 'Alda', 'Aldreda', 'Aldus',
        'Alienora', 'Aliette', 'Amée', 'Amis', 'Amphelise', 'Arlotto', 'Artaca', 'Auberi', 'Aureliana',

        'Batu', 'Belka', 'Berislav', 'Bezzhen', 'Biedeluue', 'Blaise', 'Blicze', 'Bogdan', 'Bogdana', 'Bogumir', 'Bohr', 'Borscht', 'Bradan',
        'Bratomil',

        'Cat', 'Cateline', 'Ceinguled', 'Ceri', 'Ceslinus', 'Chedomir', 'Christien', 'Clement', 'Coilean', 'Col', 'Cookie', 'Curie', 'Cynbel',
        'Cyra', 'Czestobor',

        'Dagena', 'Dalibor', 'Denyw', 'Dicun', 'Disideri', 'Dmitrei', 'Dog', 'Dorothy', 'Dragomir', 'Dye',

        'Eda', 'Eileve', 'Elena', 'Elis', 'Elric', 'Emilija', 'Enguerrand', 'Enim', 'Enynny', 'Erasmus', 'Estienne', 'Euler',
        'Eve',

        'Felix', 'Fennel', 'Fiora', 'Firmin', 'Fluri', 'Francis', 'Fritz', 'Frotlildis',

        'Galine', 'Garnier', 'Garsea', 'Gene', 'Gennoveus', 'Genoveva', 'Geoffroi', 'Gidie', 'Giliana', 'Godelive', 'Gomes',
        'Gosse', 'Gregory', 'Gubin', 'Guiscard', 'Gwennan',

        'Hamon', 'Hans', 'Hideki', 'Hippu', 'Honey', 'Hopcyn', 'Hunfrid',

        'Ibb', 'Idzi', 'Ingot', 'Ink', 'Iron', 'Isle', 'Ismo',

        'Jadviga', 'Jehanne', 'Jocosa', 'Johannes', 'Josse', 'Juniper', 'Jurian',

        'Kaija', 'Kain', 'Kale', 'Karma', 'Kazimir', 'Kepler', 'Kima', 'Kinborough', 'Kint', 'Kirik', 'Klara', 'Kryspin', 'Kukka',

        'Larkin', 'Leland', 'Leodhild', 'Leon', 'Levi', 'Lorencio', 'Lowri', 'Lucass', 'Ludmila', 'Lumi',

        'Maccos', 'Maeldoi', 'Magdalena', 'Makrina', 'Malik', 'Margaret', 'Marsley', 'Masayasu', 'Mateline',
        'Mathias', 'Matty', 'Maurifius', 'Mayonnaise', 'Meduil', 'Melita', 'Meoure', 'Merewen', 'Milian', 'Millicent', 'Mold',
        'Molle', 'Montgomery', 'Morys', 'Muriel',

        'Nascimbene', 'Nate', 'Newt', 'Nicholina', 'Nilus', 'Noe', 'Noll', 'Nuño',

        'Octavio', 'Onfroi', 'Orchid', 'Oregano', 'Origami', 'Oswyn', 'Otto', 'Owen',

        'Paperclip', 'Pascal', 'Perkhta', 'Pesczek', 'Poppy', 'Pridbjørn', 'Pyry',

        'Radomil', 'Raven', 'Raymond', 'Regina', 'Reina', 'Rihanna', 'Rimoete', 'Rocatos', 'Roger', 'Rosalind', 'Rosehip', 'Rostislav', 'Rozalia', 'Rum', 'Runne', 'Ryd',

        'Saewine', 'Sancha', 'Sandivoi', 'Schmitty', 'Seppo', 'Sisu', 'Skenfrith', 'Sulimir', 'Sunnifa', 'Sybil',

        'Taki', 'Talan', 'Tede', 'Temüjin', 'Tephaine', 'Tetris', 'Theodore', 'Tiecia', 'Timur', 'Tomila', 'Toregene', 'Trenewydd', 'Tuli',

        'Ulla', 'Umami', 'Umbra', 'Úna', 'Unit', 'Usk',

        'Vasilii', 'Venceslaus', 'Vesi', 'Vine', 'Vitseslav', 'Vivka',

        'Wallace', 'Walter', 'Wilkin', 'Wilmot', 'Wolfgang', 'Wrexham', 'Wybert', 'Wymond',

        'Xanadu', 'Xi', 'Ximeno', 'Xoxo',

        'Yaromir', 'Yrian', 'Ysabeau', 'Ystradewel',

        'Zen', 'Zofija', 'Zuan', 'Zygmunt'
    ];

    public static function generatePirateName(\DateTimeImmutable $dt, int $index): string
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


    public const array PetHalloweenNames = [
        'Pumpkin', 'Luna', 'Magic', 'Bones', 'Haunt', 'Spirit', 'Cauldron', 'Werewolf', 'Vampire',
    ];

    public const array PetChristmasNames = [
        'Holly', 'Cocoa', 'Evergreen', 'Santa', 'Dasher', 'Dancer', 'Prancer', 'Vixen', 'Comet', 'Cupid', 'Donner',
        'Blitzen', 'Rudolph', 'Olive', 'Spirit', 'Mint', 'Sol Invictus',
    ];

    public const array PetBlackFridayNames = [
        'Madness', 'Despair', 'Desperation', 'Depravity', 'Turpitude', 'Darkness', 'Terror', 'Recoil',
        'Deals', '50% Off', 'Bedlam', 'Chaos', 'Breakdown', 'Spoils',
    ];

    public const array PetCyberMondayNames = [
        'DLC', '50% Off', 'Meta Tag', 'Redirect', '302', 'robots.txt', 'Heatmap', 'Tracking Cookie', 'SEO', 'CPC',
        'CTR', 'Demographic', 'Lead Magnet', 'Remarketing', 'CTA', 'Bounce Rate', 'Open Rate', 'CPA',
        'Conversion Rate',
    ];

    public const array PetThanksgivingNames = [
        'Gobbles', 'Pumpkin', 'Cranberry', 'Turkey', 'Stuffing', 'Mashed Potatoes', 'Gravy', 'Marshmallow',
        'Light Meat', 'Dark Meat', 'Crispy Onion', 'Sweet Potato', 'Brown Sugar', 'Pecan Pie',
    ];

    public const array PetHanukkahNames = [
        'Dreidel', 'Olive Oil', 'Potato', 'Pancake', 'Gelt', 'Maccabee', 'Pączki', 'Buñuelo', 'Sufganiyah',
    ];

    public const array PetEasterNames = [
        'Osterbaum', 'Bunny', 'Rabbit', 'Daffodil', 'Lamb', 'Pastel',
    ];

    public const array PetWinterSolsticeNames = [
        'Solstice', 'Midwinter', 'Makara', 'Yaldā', 'Yule', 'Dongzhi',
    ];

    public const array PetValentinesNames = [
        'Isabeau', 'Margery', 'Lace', 'Coupon', 'Cariño',
    ];

    public const array PetWhiteDayNames = [
        'Marshmallow', 'Trắng', 'Cake', 'Cookie', 'Doki-doki', 'Lace',
    ];

    public const array PetChineseZodiacNames = [
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
