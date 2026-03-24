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
final class Version20210507000816 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item_food ADD bonus_item_group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE item_food ADD CONSTRAINT FK_1C086D0CB8E960BD FOREIGN KEY (bonus_item_group_id) REFERENCES item_group (id)');
        $this->addSql('CREATE INDEX IDX_1C086D0CB8E960BD ON item_food (bonus_item_group_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item_food DROP FOREIGN KEY FK_1C086D0CB8E960BD');
        $this->addSql('DROP INDEX IDX_1C086D0CB8E960BD ON item_food');
        $this->addSql('ALTER TABLE item_food DROP bonus_item_group_id');
    }
}
