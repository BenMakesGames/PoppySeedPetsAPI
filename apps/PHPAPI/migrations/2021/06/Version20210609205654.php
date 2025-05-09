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
final class Version20210609205654 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE aura (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(40) NOT NULL, image VARCHAR(40) NOT NULL, size SMALLINT NOT NULL, center_x DOUBLE PRECISION NOT NULL, center_y DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE enchantment ADD aura_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE enchantment ADD CONSTRAINT FK_DBE1035772CA3BB6 FOREIGN KEY (aura_id) REFERENCES aura (id)');
        $this->addSql('CREATE INDEX IDX_DBE1035772CA3BB6 ON enchantment (aura_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE enchantment DROP FOREIGN KEY FK_DBE1035772CA3BB6');
        $this->addSql('DROP TABLE aura');
        $this->addSql('DROP INDEX IDX_DBE1035772CA3BB6 ON enchantment');
        $this->addSql('ALTER TABLE enchantment DROP aura_id');
    }
}
