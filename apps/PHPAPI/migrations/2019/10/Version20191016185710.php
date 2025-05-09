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
final class Version20191016185710 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet_species ADD pregnancy_style INT NOT NULL, ADD egg_image VARCHAR(255) DEFAULT NULL');

        $this->addSql('UPDATE pet_species SET pregnancy_style=1 WHERE id NOT IN (1, 3, 4, 6, 8, 12, 14, 17, 18, 22)');
        $this->addSql('UPDATE pet_species SET egg_image="spotted" WHERE id IN (1, 8, 12, 17, 22)');
        $this->addSql('UPDATE pet_species SET egg_image="speckled" WHERE id IN (3)');
        $this->addSql('UPDATE pet_species SET egg_image="striped" WHERE id IN (14)');

        $this->addSql('UPDATE pet_species SET egg_image="speckled-small" WHERE id IN (4, 18)');
        $this->addSql('UPDATE pet_species SET egg_image="striped-small" WHERE id IN (6)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet_species DROP pregnancy_style, DROP egg_image');
    }
}
