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

final class Version20230825202200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
UPDATE `item` SET `use_actions` = '[[\"Read\",\"note/cobblers/#/read\"],[\"Show to Cooking Buddy\",\"note/cobblers/#/upload\"],[\"Erase\",\"note/#/erase\"]]' WHERE `item`.`id` = 298; 
UPDATE `item` SET `use_actions` = '[[\"Read\",\"note/stroganoff/#/read\"],[\"Show to Cooking Buddy\",\"note/stroganoff/#/upload\"],[\"Erase\",\"note/#/erase\"]]' WHERE `item`.`id` = 307; 
UPDATE `item` SET `use_actions` = '[[\"Read\",\"note/spiritPolymorphPotion/#/read\"],[\"Show to Cooking Buddy\",\"note/spiritPolymorphPotion/#/upload\"],[\"Erase\",\"note/#/erase\"]]' WHERE `item`.`id` = 341; 
UPDATE `item` SET `use_actions` = '[[\"Read\",\"note/bananananersFoster/#/read\"],[\"Show to Cooking Buddy\",\"note/bananananersFoster/#/upload\"],[\"Erase\",\"note/#/erase\"]]' WHERE `item`.`id` = 666; 
UPDATE `item` SET `use_actions` = '[[\"Read\",\"note/puddin/#/read\"],[\"Show to Cooking Buddy\",\"note/puddin/#/upload\"],[\"Erase\",\"note/#/erase\"]]' WHERE `item`.`id` = 535; 
UPDATE `item` SET `use_actions` = '[[\"Read\",\"note/gochujang/#/read\"],[\"Show to Cooking Buddy\",\"note/gochujang/#/upload\"],[\"Erase\",\"note/#/erase\"]]' WHERE `item`.`id` = 1121; 
UPDATE `item` SET `use_actions` = '[[\"Read Sticker\",\"yellowyLime/#/read\"],[\"Show to Cooking Buddy\",\"yellowyLime/#/upload\"]]' WHERE `item`.`id` = 1205; 
EOSQL
);
    }

    public function down(Schema $schema): void
    {
    }
}
