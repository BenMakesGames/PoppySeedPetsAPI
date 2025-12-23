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
final class Version20200101223539 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet_group CHANGE last_met_date last_met_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE INDEX created_on_idx ON pet_group (created_on)');
        $this->addSql('CREATE INDEX last_met_on_idx ON pet_group (last_met_on)');
        $this->addSql('CREATE INDEX type_idx ON pet_group (type)');
        $this->addSql('CREATE INDEX name_idx ON pet_group (name)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX created_on_idx ON pet_group');
        $this->addSql('DROP INDEX last_met_on_idx ON pet_group');
        $this->addSql('DROP INDEX type_idx ON pet_group');
        $this->addSql('DROP INDEX name_idx ON pet_group');
        $this->addSql('ALTER TABLE pet_group CHANGE last_met_on last_met_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
