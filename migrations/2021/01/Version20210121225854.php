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
final class Version20210121225854 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE guild ADD junior_title VARCHAR(20) NOT NULL, ADD member_title VARCHAR(20) NOT NULL, ADD senior_title VARCHAR(20) NOT NULL, ADD master_title VARCHAR(20) NOT NULL, DROP titles');

        $this->addSql("UPDATE `guild` SET `name` = 'Time\'s Arrow',`emblem` = 'times-arrow',`starter_tool_id` = 445,`quote` = '',`junior_title` = 'Planck',`member_title` = 'Archaeologist',`senior_title` = 'Chronicler',`master_title` = 'Oracle' WHERE `guild`.`id` = 1;");
        $this->addSql("UPDATE `guild` SET `name` = 'Light and Shadow',`emblem` = 'light-and-shadow',`starter_tool_id` = 242,`quote` = '',`junior_title` = 'Echo',`member_title` = 'Link',`senior_title` = 'Guardian',`master_title` = 'Emissary' WHERE `guild`.`id` = 2;");
        $this->addSql("UPDATE `guild` SET `name` = 'Tapestries',`emblem` = 'tapestries',`starter_tool_id` = 261,`quote` = '',`junior_title` = 'Thimble',`member_title` = 'Needle',`senior_title` = 'Seamster',`master_title` = 'Starweaver' WHERE `guild`.`id` = 3;");
        $this->addSql("UPDATE `guild` SET `name` = 'Inner Sanctum',`emblem` = 'inner-sanctum',`starter_tool_id` = 228,`quote` = '',`junior_title` = 'Disciple',`member_title` = 'Tisane',`senior_title` = 'Sage',`master_title` = 'Center' WHERE `guild`.`id` = 4;");
        $this->addSql("UPDATE `guild` SET `name` = 'Dwarfcraft',`emblem` = 'dwarfcraft',`starter_tool_id` = 229,`quote` = '',`junior_title` = 'Apprentice',`member_title` = 'Miner',`senior_title` = 'Artisan',`master_title` = 'Master Artificer' WHERE `guild`.`id` = 5;");
        $this->addSql("UPDATE `guild` SET `name` = 'Gizubi\'s Garden',`emblem` = 'gizubis-garden',`starter_tool_id` = 238,`quote` = '',`junior_title` = 'Seedling',`member_title` = 'Sapling',`senior_title` = 'Blossom',`master_title` = 'Oak' WHERE `guild`.`id` = 6;");
        $this->addSql("UPDATE `guild` SET `name` = 'High Impact',`emblem` = 'high-impact',`starter_tool_id` = 463,`quote` = '',`junior_title` = 'Noob',`member_title` = 'Tinkerer',`senior_title` = 'Watt',`master_title` = 'Ace' WHERE `guild`.`id` = 7;");
        $this->addSql("UPDATE `guild` SET `name` = 'The Universe Forgets',`emblem` = 'the-universe-forgets',`starter_tool_id` = 139,`quote` = '',`junior_title` = 'Canary',`member_title` = 'Investigator',`senior_title` = 'Blade',`master_title` = 'Judge' WHERE `guild`.`id` = 8;");
        $this->addSql("UPDATE `guild` SET `name` = 'Correspondence',`emblem` = 'correspondence',`starter_tool_id` = 462,`quote` = '',`junior_title` = 'Page',`member_title` = 'Runner',`senior_title` = 'Pathfinder',`master_title` = 'Mercury' WHERE `guild`.`id` = 9;");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE guild ADD titles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', DROP junior_title, DROP member_title, DROP senior_title, DROP master_title');
    }
}
