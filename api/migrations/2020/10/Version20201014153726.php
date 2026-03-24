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
final class Version20201014153726 extends AbstractMigration
{
    const ALTERNATE_ITEM_IDS = [ 61, 24, 831, 115, 36, 7, 437, 536, 112, 169, 11, 719 ];

    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE beehive ADD alternate_requested_item_id INT NOT NULL');

        foreach(self::ALTERNATE_ITEM_IDS as $i=>$id)
            $this->addSql('UPDATE beehive SET alternate_requested_item_id=' . $id . ' WHERE MOD(id, ' . count(self::ALTERNATE_ITEM_IDS) . ') = ' . $i);

        $this->addSql('ALTER TABLE beehive ADD CONSTRAINT FK_75878082F7E16CD4 FOREIGN KEY (alternate_requested_item_id) REFERENCES item (id)');
        $this->addSql('CREATE INDEX IDX_75878082F7E16CD4 ON beehive (alternate_requested_item_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE beehive DROP FOREIGN KEY FK_75878082F7E16CD4');
        $this->addSql('DROP INDEX IDX_75878082F7E16CD4 ON beehive');
        $this->addSql('ALTER TABLE beehive DROP alternate_requested_item_id');
    }
}
