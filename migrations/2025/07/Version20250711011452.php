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

final class Version20250711011452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'EOSQL'
        INSERT INTO `item_group` (`id`, `name`, `is_craving`, `is_gift_shop`)
        VALUES
        (51, 'Italian Food', '1', '0'),
        (52, 'Japanese Food', '1', '0'),
        (53, 'Chinese Food', '1', '0'),
        (54, 'Mexican Food', '1', '0')
        ON DUPLICATE KEY UPDATE id = id;

        INSERT INTO item_group_item (item_group_id, item_id)
        SELECT 51 AS item_group_id, item.id AS item_id
        FROM item
        WHERE
            name IN (
                'Bruschetta',
                'Minestrone',
                'Zabaglione',
                'Super-simple Spaghet',
                'Spaghetti with Meatless Meatballs',
                'Cheese Ravioli',
                'Cacio e Peps'
            )
            OR name LIKE '%pizza%'
        ON DUPLICATE KEY UPDATE `item_group_id` = `item_group_id`;

        INSERT INTO item_group_item (item_group_id, item_id)
        SELECT 52 AS item_group_id, item.id AS item_id
        FROM item
        WHERE
            name IN (
                'Takoyaki',
                'Konpeitō',
                'Mochi',
                'Shoyu Tamago',
                'Odori 0.0%',
                'Miso Soup',
                'Dashi',
                'Castella Cake',
                'Pan-fried Tofu',
                'Soy-ginger Fish',
                'TKG'
            )
            OR name LIKE '%nigiri%'
            OR name LIKE '%sushi%'
            OR name LIKE '%ramen%'
        ON DUPLICATE KEY UPDATE `item_group_id` = `item_group_id`;

        INSERT INTO item_group_item (item_group_id, item_id)
        SELECT 53 AS item_group_id, item.id AS item_id
        FROM item
        WHERE
            name IN (
                'Zongzi',
                'Century Egg',
                'Grass Jelly',
                'Mooncake',
                'Pan-fried Tofu',
                'Soy-ginger Fish'
            )
            OR name LIKE '%fried rice%'
            OR name LIKE '%ramen%'
        ON DUPLICATE KEY UPDATE `item_group_id` = `item_group_id`;

        INSERT INTO item_group_item (item_group_id, item_id)
        SELECT 54 AS item_group_id, item.id AS item_id
        FROM item
        WHERE
            name IN (
                'Elote',
                'Cheese Quesadilla',
                'Horchata',
                'Salsa',
                'Tepache'
            )
            OR name LIKE '%taco%'
        ON DUPLICATE KEY UPDATE `item_group_id` = `item_group_id`;
        EOSQL);

    }

    public function down(Schema $schema): void
    {
    }
}
