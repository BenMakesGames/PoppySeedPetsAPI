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

final class Version20240120133119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'delete duplicate museum donations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            DELETE FROM museum_item
            WHERE id IN (
                SELECT id FROM (
                    SELECT id, ROW_NUMBER() OVER (PARTITION BY user_id, item_id ORDER BY id) AS rn
                    FROM museum_item
                ) AS t
                WHERE rn > 1
            );
        ');

        $this->addSql('CREATE UNIQUE INDEX user_id_item_id_idx ON museum_item (user_id, item_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX user_id_item_id_idx ON museum_item');
    }
}
