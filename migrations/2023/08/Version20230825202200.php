<?php

declare(strict_types=1);

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
