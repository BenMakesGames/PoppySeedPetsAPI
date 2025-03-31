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

final class Version20241113201600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adding Leftovers pet activity log tag, and changing some other tags to use FontAwesome icons instead of unicode characters';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
            INSERT INTO `pet_activity_log_tag` (`id`, `title`, `color`, `emoji`) VALUES (93, 'Leftovers', 'e4840f', 'fa-solid fa-drumstick-bite')
            ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // Pooping:
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-poop' WHERE `pet_activity_log_tag`.`id` = 68;");

        // Magic-binding:
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-wand-sparkles' WHERE `pet_activity_log_tag`.`id` = 18;");

        // Birthday:
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-cake-candles' WHERE `pet_activity_log_tag`.`id` = 43;");

        // Band:
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-microphone-stand' WHERE `pet_activity_log_tag`.`id` = 24;");

        // Adventure!:
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-key-skeleton' WHERE `pet_activity_log_tag`.`id` = 75;");

        // Faekind:
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-solid fa-person-fairy' WHERE `pet_activity_log_tag`.`id` = 41;");

        // Stocking Stuffing Season:
        $this->addSql("UPDATE `pet_activity_log_tag` SET `emoji` = 'fa-regular fa-snowflake' WHERE `pet_activity_log_tag`.`id` = 48;");

        // oh, also: added an item description for Egg Carton
        $this->addSql("UPDATE `item` SET `description` = 'Eggs have been used as projectiles for protest and mischief since at least the 19th century.' WHERE `item`.`id` = 479;");
    }

    public function down(Schema $schema): void
    {
    }
}
