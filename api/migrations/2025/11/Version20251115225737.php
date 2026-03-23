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

final class Version20251115225737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // adding Note item group
        $this->addSql(<<<'EOSQL'
        INSERT INTO `item_group` (`id`, `name`, `is_craving`, `is_gift_shop`)
        VALUES ('60', 'Note', '0', '0')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        $this->addSql(<<<'EOSQL'
        INSERT INTO `item_group` (`id`, `name`, `is_craving`, `is_gift_shop`)
        VALUES ('61', 'Pamphlet', '0', '0')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        $this->addSql(<<<'EOSQL'
        INSERT INTO item_group_item (item_group_id, item_id)
        SELECT 60 as item_group_id, item.id AS item_id
        FROM item
        WHERE item.name IN (
            'Welcome Note',
            'Bananananers Foster Recipe',
            'Bûche De Noël Recipe',
            'Carrot Wine Recipe',
            'Cobbler Recipe',
            'Gochujang Recipe',
            'Spirit Polymorph Potion Recipe',
            'Stroganoff Recipe',
            'Puddin\' Rec\'pes',
            'Creepy Mask Day'
        )
        ON DUPLICATE KEY UPDATE item_id = item_id;
        EOSQL);

        $this->addSql(<<<'EOSQL'
        DELETE FROM item_group_item
        WHERE
            item_group_id = (SELECT id FROM item_group WHERE name = 'Book')
            AND item_id = (SELECT id FROM item WHERE name = 'Formation')
        EOSQL);

        $this->addSql(<<<'EOSQL'
        INSERT INTO item_group_item (item_group_id, item_id)
        SELECT 61 as item_group_id, item.id AS item_id
        FROM item
        WHERE item.name IN (
            'A Guide to Our Weather',
            'Formation'
        )
        ON DUPLICATE KEY UPDATE item_id = item_id;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
