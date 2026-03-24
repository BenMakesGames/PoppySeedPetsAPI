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
final class Version20200308155433 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE guild ADD starter_tool_id INT NOT NULL');

        $this->addSql('UPDATE guild SET starter_tool_id=445 WHERE id=1'); // Time's Arrow - Phishing Rod
        $this->addSql('UPDATE guild SET starter_tool_id=242 WHERE id=2'); // Light and Shadow - Witch's Broom
        $this->addSql('UPDATE guild SET starter_tool_id=261 WHERE id=3'); // Tapestries - Umbra (book)
        $this->addSql('UPDATE guild SET starter_tool_id=228 WHERE id=4'); // Inner Sanctum - Glass Pendulum
        $this->addSql('UPDATE guild SET starter_tool_id=229 WHERE id=5'); // Dwarfcraft - Iron Tongs
        $this->addSql('UPDATE guild SET starter_tool_id=238 WHERE id=6'); // Gizubi's Garden - Garden Shovel
        $this->addSql('UPDATE guild SET starter_tool_id=463 WHERE id=7'); // High Impact - Iron Sword
        $this->addSql('UPDATE guild SET starter_tool_id=139 WHERE id=8'); // The Universe Forgets - Scythe
        $this->addSql('UPDATE guild SET starter_tool_id=462 WHERE id=9'); // Correspondence - Wings

        $this->addSql('ALTER TABLE guild ADD CONSTRAINT FK_75407DAB11548A12 FOREIGN KEY (starter_tool_id) REFERENCES item (id)');
        $this->addSql('CREATE INDEX IDX_75407DAB11548A12 ON guild (starter_tool_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE guild DROP FOREIGN KEY FK_75407DAB11548A12');
        $this->addSql('DROP INDEX IDX_75407DAB11548A12 ON guild');
        $this->addSql('ALTER TABLE guild DROP starter_tool_id');
    }
}
