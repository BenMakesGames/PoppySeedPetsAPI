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
final class Version20191015164643 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pet_baby (id INT AUTO_INCREMENT NOT NULL, species_id INT NOT NULL, other_parent_id INT NOT NULL, growth INT NOT NULL, affection INT NOT NULL, INDEX IDX_9C246454B2A1D860 (species_id), UNIQUE INDEX UNIQ_9C246454AC8543C5 (other_parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pet_baby ADD CONSTRAINT FK_9C246454B2A1D860 FOREIGN KEY (species_id) REFERENCES pet_species (id)');
        $this->addSql('ALTER TABLE pet_baby ADD CONSTRAINT FK_9C246454AC8543C5 FOREIGN KEY (other_parent_id) REFERENCES pet (id)');
        $this->addSql('ALTER TABLE pet ADD pregnancy_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pet ADD CONSTRAINT FK_E4529B857DE6EAAB FOREIGN KEY (pregnancy_id) REFERENCES pet_baby (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E4529B857DE6EAAB ON pet (pregnancy_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet DROP FOREIGN KEY FK_E4529B857DE6EAAB');
        $this->addSql('DROP TABLE pet_baby');
        $this->addSql('DROP INDEX UNIQ_E4529B857DE6EAAB ON pet');
        $this->addSql('ALTER TABLE pet DROP pregnancy_id');
    }
}
