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

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200227021453 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE item_tool ADD when_gather_id INT DEFAULT NULL, ADD when_gather_also_gather_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE item_tool ADD CONSTRAINT FK_E8C37A2A2182ABDD FOREIGN KEY (when_gather_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE item_tool ADD CONSTRAINT FK_E8C37A2A7C7A1896 FOREIGN KEY (when_gather_also_gather_id) REFERENCES item (id)');
        $this->addSql('CREATE INDEX IDX_E8C37A2A2182ABDD ON item_tool (when_gather_id)');
        $this->addSql('CREATE INDEX IDX_E8C37A2A7C7A1896 ON item_tool (when_gather_also_gather_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE item_tool DROP FOREIGN KEY FK_E8C37A2A2182ABDD');
        $this->addSql('ALTER TABLE item_tool DROP FOREIGN KEY FK_E8C37A2A7C7A1896');
        $this->addSql('DROP INDEX IDX_E8C37A2A2182ABDD ON item_tool');
        $this->addSql('DROP INDEX IDX_E8C37A2A7C7A1896 ON item_tool');
        $this->addSql('ALTER TABLE item_tool DROP when_gather_id, DROP when_gather_also_gather_id');
    }
}
