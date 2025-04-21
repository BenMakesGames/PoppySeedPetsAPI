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

final class Version20240928090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Sword
        $this->addSql(<<<EOSQL
        INSERT INTO `item_group` (`id`, `name`, `is_craving`, `is_gift_shop`) VALUES (41, 'Sword', '0', '0')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        $this->addSql(<<<EOSQL
        INSERT INTO item_group_item (item_group_id,item_id)
        SELECT 41 AS item_group_id, id AS item_id
        FROM `item`
        WHERE image LIKE 'tool/sword/%'
        ON DUPLICATE KEY UPDATE `item_group_id` = `item_group_id`;
        EOSQL);

        // Wand
        $this->addSql(<<<EOSQL
        INSERT INTO `item_group` (`id`, `name`, `is_craving`, `is_gift_shop`) VALUES (42, 'Wand', '0', '0')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        $this->addSql(<<<EOSQL
        INSERT INTO item_group_item (item_group_id,item_id)
        SELECT 42 AS item_group_id, id AS item_id
        FROM `item`
        WHERE image LIKE 'tool/wand/%'
        ON DUPLICATE KEY UPDATE `item_group_id` = `item_group_id`;
        EOSQL);

        // Spear
        $this->addSql(<<<EOSQL
        INSERT INTO `item_group` (`id`, `name`, `is_craving`, `is_gift_shop`) VALUES (43, 'Spear', '0', '0')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        $this->addSql(<<<EOSQL
        INSERT INTO item_group_item (item_group_id,item_id)
        SELECT 43 AS item_group_id, id AS item_id
        FROM `item`
        WHERE image LIKE 'tool/spear/%'
        ON DUPLICATE KEY UPDATE `item_group_id` = `item_group_id`;
        EOSQL);

        // Scroll
        $this->addSql(<<<EOSQL
        INSERT INTO `item_group` (`id`, `name`, `is_craving`, `is_gift_shop`) VALUES (44, 'Scroll', '0', '0')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        $this->addSql(<<<EOSQL
        INSERT INTO item_group_item (item_group_id,item_id)
        SELECT 44 AS item_group_id, id AS item_id
        FROM `item`
        WHERE image LIKE 'scroll/%'
        ON DUPLICATE KEY UPDATE `item_group_id` = `item_group_id`;
        EOSQL);

        // Bag
        $this->addSql(<<<EOSQL
        INSERT INTO `item_group` (`id`, `name`, `is_craving`, `is_gift_shop`) VALUES (45, 'Bag', '0', '0')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        $this->addSql(<<<EOSQL
        INSERT INTO item_group_item (item_group_id,item_id)
        SELECT 45 AS item_group_id, id AS item_id
        FROM `item`
        WHERE image LIKE 'bag/%'
        ON DUPLICATE KEY UPDATE `item_group_id` = `item_group_id`;
        EOSQL);

        $this->addSql(<<<EOSQL
        INSERT INTO item_group_item (item_group_id,item_id)
        SELECT 45 AS item_group_id, id AS item_id
        FROM `item`
        WHERE name LIKE '% Fertilizer'
        ON DUPLICATE KEY UPDATE `item_group_id` = `item_group_id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
