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
final class Version20190804170347 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pet_relationship (id INT AUTO_INCREMENT NOT NULL, pet_id INT NOT NULL, relationship_id INT NOT NULL, intimacy INT NOT NULL, passion INT NOT NULL, commitment INT NOT NULL, met_description VARCHAR(255) NOT NULL, met_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_C48C5FF1966F7FB6 (pet_id), INDEX IDX_C48C5FF12C41D668 (relationship_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pet_relationship ADD CONSTRAINT FK_C48C5FF1966F7FB6 FOREIGN KEY (pet_id) REFERENCES pet (id)');
        $this->addSql('ALTER TABLE pet_relationship ADD CONSTRAINT FK_C48C5FF12C41D668 FOREIGN KEY (relationship_id) REFERENCES pet (id)');

        // increase all pets' stomach sizes by 6
        $this->addSql('UPDATE pet SET stomach_size=stomach_size+6');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE pet_relationship');
    }
}
