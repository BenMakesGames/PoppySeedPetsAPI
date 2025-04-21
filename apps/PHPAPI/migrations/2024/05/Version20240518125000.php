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


namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240518125000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'more new location tags';
    }

    public const New_Item_Descriptions = [
        [
            'id' => 91,
            'description' => 'An essential component for crafting superconductors, advanced quantum circuits, and blinged-out nests to attract potential mates.'
        ],
        [
            'id' => 104,
            'description' => 'This blade has seen more seawater than combat.',
        ],
        [
            'id' => 115,
            'description' => 'So light and airy, it\'s like eating a cloud. A delicious, sugary, oddly-crunchy cloud.',
        ],
        [
            'id' => 119,
            'description' => 'Perfect for smelting, grilling, and making a mess! Wow! So versatile!',
        ],
        [
            'id' => 152,
            'description' => 'An essential tool for anyone venturing into the island\'s untamed wilds.',
        ],
        [
            'id' => 153,
            'description' => 'Now with feathers! Because nothing says "I\'m serious about survival" like a splash of colorful plumage.',
        ],
        [
            'id' => 154,
            'description' => 'With this spear in hand, the spirit world is just a thrust away - whether you want to visit or not...',
        ],
        [
            'id' => 159,
            'description' => 'Unleavened and uncomplicated. Sometimes less is more.',
        ],
        [
            'id' => 170,
            'description' => 'Versatile and nutritious. A kitchen must-have.',
        ],
        [
            'id' => 171,
            'description' => "These magic beans promise to sprout something extraordinary!\n\n... but if that sounds too complicated, you can still just eat 'em."
        ],
        [
            'id' => 185,
            'description' => 'A favorite among HERG researchers. Apparently they have a storeroom full of the stuff!',
        ],
        [
            'id' => 205,
            'description' => 'Equip this basket when you feel like gathering fruits and veggies, or toss it into the fireplace when you\'re feeling cold and uninspired - the choice, as they say, is yours.',
        ],
        [
            'id' => 239,
            'description' => 'Crafted with the finest island ingredients, squeezed from the finest island goats.',
        ]
    ];

    public function up(Schema $schema): void
    {
        foreach(self::New_Item_Descriptions as $itemDescription)
            $this->addSql('UPDATE item SET description=:description WHERE id=:id', $itemDescription);
    }

    public function down(Schema $schema): void
    {
    }
}
