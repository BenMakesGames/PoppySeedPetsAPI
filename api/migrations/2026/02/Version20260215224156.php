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

final class Version20260215224156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'what is bird? who can say. this item group claims to be an authority, at any rate.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO `item_group` (`id`, `name`, `is_craving`, `is_gift_shop`) VALUES (62, 'Bird Stuff', '0', '0') ON DUPLICATE KEY UPDATE id = id;");

        $this->addSql(<<<EOSQL
        INSERT INTO item_group_item (`item_group_id`, `item_id`)
        SELECT 62 AS item_group_id, item.id AS item_id
        FROM item
        WHERE item.name IN (
            'Bag of Feathers',
            'Bird Bath Blueprint',
            'Bird\'s-foot Trefoil',
            'Black Feathers',
            'Bleached Turkey Head',
            'Century Egg',
            'Crow\'s Eye',
            'Duck Pond',
            'Duck Sauce',
            'Earth\'s Egg',
            'Egg',
            'Feathers',
            'Fried Egg',
            'Giant Turkey Leg',
            'Goosecap',
            'Green Egg',
            'Imperturbable Toucan',
            'Magpie Pouch',
            'Magpie\'s Deal',
            'Owl Trowel',
            'Peacock Plushy',
            'Phoenix Plushy',
            'Tile: Thieving Magpie',
            'Tile: Worm "Merchant"',
            'Turkey King',
            'Ruby Feather',
            'White Feathers'
        )
        ON DUPLICATE KEY UPDATE `item_group_id` = `item_group_id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
