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
final class Version20200823184233 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE enchantment (id INT AUTO_INCREMENT NOT NULL, effects_id INT NOT NULL, name VARCHAR(20) NOT NULL, is_suffix TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_DBE10357568FBDB9 (effects_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE enchantment ADD CONSTRAINT FK_DBE10357568FBDB9 FOREIGN KEY (effects_id) REFERENCES item_tool (id)');
        $this->addSql('ALTER TABLE inventory ADD enchantment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE inventory ADD CONSTRAINT FK_B12D4A36F3927CF3 FOREIGN KEY (enchantment_id) REFERENCES enchantment (id)');
        $this->addSql('CREATE INDEX IDX_B12D4A36F3927CF3 ON inventory (enchantment_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE inventory DROP FOREIGN KEY FK_B12D4A36F3927CF3');
        $this->addSql('DROP TABLE enchantment');
        $this->addSql('DROP INDEX IDX_B12D4A36F3927CF3 ON inventory');
        $this->addSql('ALTER TABLE inventory DROP enchantment_id');
    }
}
