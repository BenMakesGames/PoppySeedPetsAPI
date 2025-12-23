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

final class Version20240104210001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'fairy floss! (2 of 2)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `use_actions` = '[[\"Say Hello\",\"fairy/#/hello\"],[\"Build a Fireplace\",\"fairy/#/buildFireplace\"],[\"Ask about Quintessence\",\"fairy/#/quintessence\"],[\"Make Fairy Floss\",\"fairy/#/makeFairyFloss\"]]' WHERE `item`.`id` = 333; 
        EOSQL);

        $this->addSql('UPDATE `pet_species` SET `sheds_id` = 1358 WHERE `pet_species`.`id` = 3;');
    }

    public function down(Schema $schema): void
    {
    }
}
