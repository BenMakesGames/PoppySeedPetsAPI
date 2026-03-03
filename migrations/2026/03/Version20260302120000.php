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
use Symfony\Component\Uid\Ulid;

final class Version20260302120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert item_treasure.id from auto-increment INT to ULID BINARY(16), and update item.treasure_id FK accordingly.';
    }

    public function up(Schema $schema): void
    {
        // 1. Drop FK constraint and unique index on item.treasure_id
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E4DF05F8E');
        $this->addSql('DROP INDEX UNIQ_1F1B251E4DF05F8E ON item');

        // 2. Add new BINARY(16) columns
        $this->addSql('ALTER TABLE item_treasure ADD new_id BINARY(16) NULL AFTER id');
        $this->addSql('ALTER TABLE item ADD new_treasure_id BINARY(16) NULL AFTER treasure_id');
    }

    public function postUp(Schema $schema): void
    {
        // 3. Generate ULIDs for each existing item_treasure row
        $rows = $this->connection->fetchAllAssociative('SELECT id FROM item_treasure');
        $idMap = [];

        foreach ($rows as $row) {
            $oldId = $row['id'];
            $ulid = new Ulid();
            $binary = $ulid->toBinary();
            $idMap[$oldId] = $binary;

            $this->connection->executeStatement(
                'UPDATE item_treasure SET new_id = :newId WHERE id = :oldId',
                ['newId' => $binary, 'oldId' => $oldId]
            );
        }

        // 4. Copy FK mappings: set item.new_treasure_id from the ULID map
        foreach ($idMap as $oldId => $binary) {
            $this->connection->executeStatement(
                'UPDATE item SET new_treasure_id = :newTreasureId WHERE treasure_id = :oldTreasureId',
                ['newTreasureId' => $binary, 'oldTreasureId' => $oldId]
            );
        }

        // 5. Drop old columns
        $this->connection->executeStatement('ALTER TABLE item DROP COLUMN treasure_id');
        $this->connection->executeStatement('ALTER TABLE item_treasure DROP PRIMARY KEY, DROP COLUMN id');

        // 6. Rename new columns
        $this->connection->executeStatement('ALTER TABLE item_treasure CHANGE new_id id BINARY(16) NOT NULL');
        $this->connection->executeStatement('ALTER TABLE item CHANGE new_treasure_id treasure_id BINARY(16) DEFAULT NULL');

        // 7. Re-add PK, unique index, and FK constraint
        $this->connection->executeStatement('ALTER TABLE item_treasure ADD PRIMARY KEY (id)');
        $this->connection->executeStatement('CREATE UNIQUE INDEX UNIQ_1F1B251E4DF05F8E ON item (treasure_id)');
        $this->connection->executeStatement('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E4DF05F8E FOREIGN KEY (treasure_id) REFERENCES item_treasure (id)');
    }

    public function down(Schema $schema): void
    {
        throw new \RuntimeException('Cannot reverse ULID migration — original auto-increment IDs are lost.');
    }
}
