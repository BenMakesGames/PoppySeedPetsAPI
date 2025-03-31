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
final class Version20210629190003 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item ADD museum_points SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE user ADD museum_points INT NOT NULL');

        // base value
        $this->addSql('UPDATE item SET museum_points=IF(recycle_value<5,1,FLOOR(recycle_value/5)*10)');

        // exceptions
        $this->addSql('UPDATE item SET museum_points=5 WHERE name IN (
            "Red Umbrella", "Bungee Cord", "Sand Dollar", "Tower Chest", "Chocolate Chest",
            "William, Shush", "White Firework", "Blue Firework", "Red Firework", "Yellow Firework"
        )');
        $this->addSql('UPDATE item SET museum_points=7 WHERE name = "Musical Scales"');
        $this->addSql('UPDATE item SET museum_points=10 WHERE name IN (
            "Weird Beetle", "Meteorite", "Magpie\'s Deal", "Fluff Heart", "Yes, Chef!", "Nón Lá",
            "Gold Dragon Ingot"
        )');
        $this->addSql('UPDATE item SET museum_points=15 WHERE name IN (
            "Cosmologer\'s Promise", "Witch\'s Hat", "Santa Hat", "Top Hat", "Fez", "Jolliest Roger",
            "Creamiest Roger", "White Animal Ears", "Black Animal Ears", "Chocolate Top Hat",
            "Chocolate Feather Bonnet"
        )');
        $this->addSql('UPDATE item SET museum_points=20 WHERE name IN (
            "Heartstone", "Sentient Beetle", "Renaming Scroll", "Turkey King",
            "Cetgueli\'s Treasure Map"
        )');
        $this->addSql('UPDATE item SET museum_points=25 WHERE name IN (
            "Heart Beetle", "Stereotypical Bone", "Fish Bones",
            "Gold Crown", "Dragon Vase", "Lengthy Scroll of Skill",
            "Forgetting Scroll", "Behatting Scroll", "Unicorn Horn"
        )');
        $this->addSql('UPDATE item SET museum_points=30 WHERE name IN (
            "Dino Skull", "Double Crown"
        )');
        $this->addSql('UPDATE item SET museum_points=35 WHERE name IN (
            "Eggplant Bow", "Green Bow", "Yellow Bow",
            "Gray Bow", "Brown Bow", "Pink Bow", "Blue Bow",
            "Red Bow", "Black Bow", "White Bow", "Cyan Bow",
            "Orange Bow", "Transparent Bow", "Triple Crown"
        )');

        // notable item groups
        $this->addSql('UPDATE item SET museum_points=5 WHERE id IN (
            SELECT item_id FROM item_group_item
            LEFT JOIN item_group ON item_group_item.item_group_id=item_group.id
            WHERE item_group.name="Hollow Earth Booster Pack: Uncommon"
        )');
        $this->addSql('UPDATE item SET museum_points=15 WHERE id IN (
            SELECT item_id FROM item_group_item
            LEFT JOIN item_group ON item_group_item.item_group_id=item_group.id
            WHERE item_group.name="Hollow Earth Booster Pack: Rare"
        )');
        $this->addSql('UPDATE item SET museum_points=50 WHERE id IN (
            SELECT item_id FROM item_group_item
            LEFT JOIN item_group ON item_group_item.item_group_id=item_group.id
            WHERE item_group.name="Skill Scroll"
        )');

        // all other scrolls
        $this->addSql('UPDATE item SET museum_points=5 WHERE name LIKE "%scroll%" AND museum_points<5');

        $this->addSql('
            UPDATE user
            INNER JOIN (
                SELECT SUM(item.museum_points) AS total,user_id
                FROM museum_item
                LEFT JOIN item ON item.id=museum_item.item_id
                GROUP BY user_id
            ) AS d ON user.id=d.user_id
            SET user.museum_points=d.total
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item DROP museum_points');
        $this->addSql('ALTER TABLE user DROP museum_points');
    }
}
