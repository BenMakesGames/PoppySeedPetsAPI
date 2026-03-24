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
final class Version20230707221407 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pet_baby ADD spirit_parent_id INT DEFAULT NULL, CHANGE other_parent_id other_parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pet_baby ADD CONSTRAINT FK_9C246454C3065B47 FOREIGN KEY (spirit_parent_id) REFERENCES spirit_companion (id)');
        $this->addSql('CREATE INDEX IDX_9C246454C3065B47 ON pet_baby (spirit_parent_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pet_baby DROP FOREIGN KEY FK_9C246454C3065B47');
        $this->addSql('DROP INDEX IDX_9C246454C3065B47 ON pet_baby');
        $this->addSql('ALTER TABLE pet_baby DROP spirit_parent_id, CHANGE other_parent_id other_parent_id INT NOT NULL');
    }
}
