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

final class Version20250109154300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Wheat Flour should have the Cooking group!
        $this->addSql(<<<EOSQL
        INSERT INTO item_group_item (item_group_id, item_id)
        SELECT 46 AS item_group_id, item.id AS item_id
        FROM item
        WHERE item.name IN (
            'Wheat Flour'
        )
        ON DUPLICATE KEY UPDATE `item_group_id` = `item_group_id`;
        EOSQL);

        // cleaning up some inappropriate pet names
        $this->addSql("UPDATE pet SET name='Benmakesgames is a ne\\'er-do-well' WHERE id=2238");
        $this->addSql("UPDATE pet SET name='Sbutt' WHERE id=1686");
        $this->addSql("UPDATE pet SET name='umplsing opo pete' WHERE id=12716");
        $this->addSql("UPDATE pet SET name='Derumplsing S' WHERE id=48100");
    }

    public function down(Schema $schema): void
    {
    }
}
