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
final class Version20200308150300 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('
UPDATE `guild` SET `id` = 1,`name` = \'Time\\\'s Arrow\',`titles` = \'Noob,Bronze Hooligan,Silver Hooligan,Gold Hooligan,Bronze Jumper,Silver Jumper,Gold Jumper\',`emblem` = \'times-arrow\' WHERE `guild`.`id` = 1;
UPDATE `guild` SET `id` = 2,`name` = \'Light and Shadow\',`titles` = \'Trainee,Bronze Acolyte,Silver Acolyte,Gold Acolyte,Bronze Protector,Silver Protector,Gold Protector\',`emblem` = \'light-and-shadow\' WHERE `guild`.`id` = 2;
UPDATE `guild` SET `id` = 3,`name` = \'Tapestries\',`titles` = \'Trainee,Bronze Acolyte,Silver Acolyte,Gold Acolyte,Bronze Weaver,Silver Weaver,Gold Weaver\',`emblem` = \'tapestries\' WHERE `guild`.`id` = 3;
UPDATE `guild` SET `id` = 4,`name` = \'Inner Sanctum\',`titles` = \'Page,Bronze Apprentice,Silver Apprentice,Gold Apprentice,Bronze Librarian,Silver Librarian,Gold Librarian\',`emblem` = \'inner-sanctum\' WHERE `guild`.`id` = 4;
UPDATE `guild` SET `id` = 5,`name` = \'Dwarfcraft\',`titles` = \'Trial Inductee,Bronze Apprentice,Silver Apprentice,Gold Apprentice,Bronze Miner,Silver Miner,Gold Miner\',`emblem` = \'dwarfcraft\' WHERE `guild`.`id` = 5;
UPDATE `guild` SET `id` = 6,`name` = \'Gizubi\\\'s Garden\',`titles` = \'Seedling,Sprout,Sapling,Tree,White Blossom,Red Blossom,Purple Blossom\',`emblem` = \'gizubis-garden\' WHERE `guild`.`id` = 6;
UPDATE `guild` SET `id` = 7,`name` = \'High Impact\',`titles` = \'Noob,Red Apprentice,Orange Apprentice,Yellow Apprentice,Green Apprentice,Blue Apprentice,Purple Apprentice\',`emblem` = \'high-impact\' WHERE `guild`.`id` = 7;
UPDATE `guild` SET `id` = 8,`name` = \'The Universe Forgets\',`titles` = \'Trial Inductee,Bronze Acolyte,Silver Acolyte,Gold Acolyte,Bronze Librarian,Silver Librarian,Gold Librarian\',`emblem` = \'the-universe-forgets\' WHERE `guild`.`id` = 8;
UPDATE `guild` SET `id` = 9,`name` = \'Correspondence\',`titles` = \'Trainee,Bronze Apprentice,Silver Apprentice,Gold Apprentice,Bronze Runner,Silver Runner,Gold Runner\',`emblem` = \'correspondence\' WHERE `guild`.`id` = 9;
');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
