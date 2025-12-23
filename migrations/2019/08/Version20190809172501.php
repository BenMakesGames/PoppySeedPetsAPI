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
final class Version20190809172501 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE INDEX time_idx ON pet (time)');
        $this->addSql('ALTER TABLE greenhouse_plant ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE greenhouse_plant ADD CONSTRAINT FK_477F79E37E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_477F79E37E3C61F9 ON greenhouse_plant (owner_id)');
        $this->addSql('CREATE INDEX weeds_idx ON greenhouse_plant (weeds)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE greenhouse_plant DROP FOREIGN KEY FK_477F79E37E3C61F9');
        $this->addSql('DROP INDEX IDX_477F79E37E3C61F9 ON greenhouse_plant');
        $this->addSql('DROP INDEX weeds_idx ON greenhouse_plant');
        $this->addSql('ALTER TABLE greenhouse_plant DROP owner_id');
        $this->addSql('DROP INDEX time_idx ON pet');
    }
}
