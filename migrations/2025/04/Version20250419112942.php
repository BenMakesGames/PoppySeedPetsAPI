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

final class Version20250419112942 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Some item descriptions';
    }

    public function up(Schema $schema): void
    {
        // propeller beanie
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'It would provide a +climbing bonus, from the lift provided by the propeller, but because the hat mainly just lifts itself off of pets\' heads, the pets are then constantly distracted trying to keep it on, negating the bonus.\n\nSo it goes with all hats. And that\'s why hats don\'t provide equipment bonuses in Poppy Seed Pets.\n\nYep.\n\nThat\'s the reason.' WHERE `item`.`id` = 1097;
        EOSQL);

        // compiler
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = '\"Il semble que la perfection soit atteinte non quand il n\'y a plus rien à ajouter, mais quand il n\'y a plus rien à retrancher.\" ~Antoine de Saint Exupéry' WHERE `item`.`id` = 193;
        EOSQL);

        // ceremony of sand and sea
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = '\"Building a boat isn\'t about weaving canvas, forging nails, or reading the sky. It\'s about giving a shared taste for the sea.\" ~Antoine de Saint Exupéry' WHERE `item`.`id` = 260; 
        EOSQL);

        // benjamin franklin
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = '\"Die Thiere spielen nicht, weil sie jung sind, sondern sie haben eine Jugend, weil sie spielen müssen.\" ~not Benjamin Franklin' WHERE `item`.`id` = 292;
        EOSQL);

        // gold key
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'What good is a gold trophy? A Gold _Key_ will reveal yet more treasures... and is more portable!' WHERE `item`.`id` = 103;
        EOSQL);

        // cockroach
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Cockroaches\' natural resistance to radiation also renders them immune to the effects of Megalium.\n\nThank goodness! I mean, can you _imagine???_ \\*shudders\\*' WHERE `item`.`id` = 162;
        EOSQL);

        // naner preserves
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Naner preserves _what?_\n\nWHY WON\'T YOU TELL ME?!?!? \\*shakes jar vigorously\\*' WHERE `item`.`id` = 182;
        EOSQL);

        // Rotary Phone
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Now even pizza can be delivered, just like you were delivered all those years ago: warm, sticky, and covered in pineapple. (Don\'t tell me you\'re one of those \"pineapple doesn\'t belong on babies\" people!)' WHERE `item`.`id` = 1249;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
