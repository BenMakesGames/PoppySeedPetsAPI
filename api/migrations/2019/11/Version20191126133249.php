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
final class Version20191126133249 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pet_activity_stats (id INT AUTO_INCREMENT NOT NULL, pet_id INT NOT NULL, craft_success INT NOT NULL, craft_failure INT NOT NULL, craft_time INT NOT NULL, magic_bind_success INT NOT NULL, magic_bind_failure INT NOT NULL, magic_bind_time INT NOT NULL, smith_success INT NOT NULL, smith_failure INT NOT NULL, smith_time INT NOT NULL, plastic_print_success INT NOT NULL, plastic_print_failure INT NOT NULL, plastic_print_time INT NOT NULL, fish_success INT NOT NULL, fish_failure INT NOT NULL, fish_time INT NOT NULL, gather_success INT NOT NULL, gather_failure INT NOT NULL, gather_time INT NOT NULL, hunt_success INT NOT NULL, hunt_failure INT NOT NULL, hunt_time INT NOT NULL, protocol7success INT NOT NULL, protocol7failure INT NOT NULL, protocol7time INT NOT NULL, program_success INT NOT NULL, program_failure INT NOT NULL, program_time INT NOT NULL, umbra_success INT NOT NULL, umbra_failure INT NOT NULL, umbra_time INT NOT NULL, hang_out INT NOT NULL, hang_out_time INT NOT NULL, park_event INT NOT NULL, park_event_time INT NOT NULL, other INT NOT NULL, other_time INT NOT NULL, UNIQUE INDEX UNIQ_60801068966F7FB6 (pet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pet_activity_stats ADD CONSTRAINT FK_60801068966F7FB6 FOREIGN KEY (pet_id) REFERENCES pet (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE pet_activity_stats');
    }
}
