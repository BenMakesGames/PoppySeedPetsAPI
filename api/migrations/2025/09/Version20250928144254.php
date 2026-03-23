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

final class Version20250928144254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // 1-on-1 Hangout
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-people' WHERE `pet_activity_log_tag`.`id` = 2;");

        // Add-on Assistance
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-hand' WHERE `pet_activity_log_tag`.`id` = 7;");

        // Dragon Den
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-dragon' WHERE `pet_activity_log_tag`.`id` = 10;");

        // Easter
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-egg' WHERE `pet_activity_log_tag`.`id` = 35;");

        // Gaming Group
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-dice' WHERE `pet_activity_log_tag`.`id` = 26;");

        // Giving Tree
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-gift' WHERE `pet_activity_log_tag`.`id` = 33;");

        // Gourmand
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-stomach' WHERE `pet_activity_log_tag`.`id` = 77;");

        // Group Hangout
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-users-rays' WHERE `pet_activity_log_tag`.`id` = 1;");

        // Guild
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-shield-cat' WHERE `pet_activity_log_tag`.`id` = 31;");

        // Halloween
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-jack-o-lantern' WHERE `pet_activity_log_tag`.`id` = 5;");

        // Location: Noetala's Cocoon
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-eye' WHERE `pet_activity_log_tag`.`id` = 87;");

        // Lucky~!
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-horseshoe' WHERE `pet_activity_log_tag`.`id` = 73;");

        // Romance
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-hearts' WHERE `pet_activity_log_tag`.`id` = 63;");

        // Smithing
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-hammer-crash' WHERE `pet_activity_log_tag`.`id` = 53;");

        // Special Event
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-party-horn' WHERE `pet_activity_log_tag`.`id` = 4;");

        // Spirit Companion
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-ghost' WHERE `pet_activity_log_tag`.`id` = 60;");

        // Sportsball
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-shirt-jersey' WHERE `pet_activity_log_tag`.`id` = 25;");

        // St. Patrick's
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-clover' WHERE `pet_activity_log_tag`.`id` = 36;");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
