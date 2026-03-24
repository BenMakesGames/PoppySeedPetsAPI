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
final class Version20190616132830 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pet_skills (id INT AUTO_INCREMENT NOT NULL, strength INT NOT NULL, dexterity INT NOT NULL, intelligence INT NOT NULL, perception INT NOT NULL, stealth INT NOT NULL, stamina INT NOT NULL, nature INT NOT NULL, brawl INT NOT NULL, umbra INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pet ADD skills_id INT NOT NULL');
        $this->addSql('ALTER TABLE pet ADD CONSTRAINT FK_E4529B857FF61858 FOREIGN KEY (skills_id) REFERENCES pet_skills (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E4529B857FF61858 ON pet (skills_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet DROP FOREIGN KEY FK_E4529B857FF61858');
        $this->addSql('DROP TABLE pet_skills');
        $this->addSql('DROP INDEX UNIQ_E4529B857FF61858 ON pet');
        $this->addSql('ALTER TABLE pet DROP skills_id');
    }
}
