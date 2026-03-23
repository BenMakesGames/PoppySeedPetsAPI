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
final class Version20190730015808 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE park_event_pet DROP FOREIGN KEY FK_8BE50733000B791');
        $this->addSql('ALTER TABLE park_event_prize DROP FOREIGN KEY FK_1F983A2E71F7E88B');
        $this->addSql('DROP TABLE park_event');
        $this->addSql('DROP TABLE park_event_pet');
        $this->addSql('DROP TABLE park_event_prize');
        $this->addSql('ALTER TABLE pet DROP last_park_event_joined_on');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE park_event (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(40) NOT NULL COLLATE utf8mb4_unicode_ci, seats INT NOT NULL, ran_on DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', results LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, is_full TINYINT(1) NOT NULL, INDEX ran_on_idx (ran_on), INDEX is_full_idx (is_full), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE park_event_pet (park_event_id INT NOT NULL, pet_id INT NOT NULL, INDEX IDX_8BE5073966F7FB6 (pet_id), INDEX IDX_8BE50733000B791 (park_event_id), PRIMARY KEY(park_event_id, pet_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE park_event_prize (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, prize_id INT NOT NULL, place INT NOT NULL, UNIQUE INDEX UNIQ_1F983A2EBBE43214 (prize_id), INDEX IDX_1F983A2E71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE park_event_pet ADD CONSTRAINT FK_8BE50733000B791 FOREIGN KEY (park_event_id) REFERENCES park_event (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE park_event_pet ADD CONSTRAINT FK_8BE5073966F7FB6 FOREIGN KEY (pet_id) REFERENCES pet (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE park_event_prize ADD CONSTRAINT FK_1F983A2E71F7E88B FOREIGN KEY (event_id) REFERENCES park_event (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE park_event_prize ADD CONSTRAINT FK_1F983A2EBBE43214 FOREIGN KEY (prize_id) REFERENCES inventory (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE pet ADD last_park_event_joined_on DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
    }
}
