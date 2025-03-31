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
final class Version20210629184141 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item_group ADD is_gift_shop TINYINT(1) NOT NULL');

        // --- wow! it's like a real migration! -------------------------------

        // adding "Skill Scroll" item group, and items
        $this->addSql('INSERT INTO `item_group` (`id`, `name`, `is_craving`, `is_gift_shop`) VALUES (NULL, \'Skill Scroll\', 0, 1)');
        $this->addSql('
            INSERT INTO item_group_item (item_group_id, item_id)
            SELECT item_group.id AS item_group_id, item.id AS item_id
            FROM item
            LEFT JOIN item_group ON item_group.name="Skill Scroll"
            WHERE item.name LIKE "Skill Scroll: %"
        ');

        $this->addSql('INSERT INTO `item_group` (`id`, `name`, `is_craving`, `is_gift_shop`) VALUES (NULL, \'Bone\', 0, 1)');
        $this->addSql('
            INSERT INTO item_group_item (item_group_id, item_id)
            SELECT item_group.id AS item_group_id, item.id AS item_id
            FROM item
            LEFT JOIN item_group ON item_group.name="Bone"
            WHERE item.name IN ("Fish Bones", "Stereotypical Bone", "Talon", "Dino Skull", "Rib")
        ');

        $this->addSql('INSERT INTO `item_group` (`id`, `name`, `is_craving`, `is_gift_shop`) VALUES (NULL, \'Musical Instrument\', 0, 1)');
        $this->addSql('
            INSERT INTO item_group_item (item_group_id, item_id)
            SELECT item_group.id AS item_group_id, item.id AS item_id
            FROM item
            LEFT JOIN item_group ON item_group.name="Musical Instrument"
            WHERE
                (item.image LIKE "tool/instrument/%" AND item.name!="Bermuda Triangle")
                OR item.name IN ("Fishing Recorder", "Kokopelli", "Gold Trifecta", "Gold Triskaidecta")
        ');

        $this->addSql('INSERT INTO `item_group` (`id`, `name`, `is_craving`, `is_gift_shop`) VALUES (NULL, \'Candy Shop Candy\', 1, 1)');
        $this->addSql('
            INSERT INTO item_group_item (item_group_id, item_id)
            SELECT item_group.id AS item_group_id, item.id AS item_id
            FROM item
            LEFT JOIN item_group ON item_group.name="Candy Shop Candy"
            WHERE
                item.name LIKE "% Gummies"
                OR item.name = "Gummy Worms"
                OR item.name LIKE "% Hard Candy"
                OR item.name LIKE "%Chocolate Bar"
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item_group DROP is_gift_shop');
    }
}
