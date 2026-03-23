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

final class Version20250209165000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Sleet
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = '\"what if a keen of a lean wind flays<br>\r\nscreaming hills with sleet and snow:<br>\r\nstrangles valleys by ropes of thing<br>\r\nand stifles forests in white ago?\"<br>\r\n~ e.e. cummings' WHERE `item`.`id` = 856;
        EOSQL);

        // Horizon Mirror
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = '\"what if a dawn of a doom of a dream<br>\r\nbites this universe in two,<br>\r\npeels forever out of his grave<br>\r\nand sprinkles nowhere with me and you?\"<br>\r\n~ e.e. cummings' WHERE `item`.`id` = 1082;  
        EOSQL);

        // deleting old data migration which implemented the now-deleted ContainerAwareInterface
        $this->addSql(<<<EOSQL
        DELETE FROM doctrine_migration_versions WHERE `doctrine_migration_versions`.`version` = 'DoctrineMigrations\Version20191018181048'
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
