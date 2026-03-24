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

final class Version20250920192629 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // rain
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-raindrops' WHERE `pet_activity_log_tag`.`id` = 79;");

        // thanksgiving
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-turkey' WHERE `pet_activity_log_tag`.`id` = 45;");

        // mining
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-pickaxe' WHERE `pet_activity_log_tag`.`id` = 16;");

        // relationship change
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-comments' WHERE `pet_activity_log_tag`.`id` = 78;");

        // leonids
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-star-shooting' WHERE `pet_activity_log_tag`.`id` = 71;");

        // painting
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-paintbrush-fine' WHERE `pet_activity_log_tag`.`id` = 54;");

        // mail
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-envelope' WHERE `pet_activity_log_tag`.`id` = 34;");

        // physics
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-atom-simple' WHERE `pet_activity_log_tag`.`id` = 50;");

        // jousting
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-horse-saddle' WHERE `pet_activity_log_tag`.`id` = 28;");
    }

    public function down(Schema $schema): void
    {
    }
}
