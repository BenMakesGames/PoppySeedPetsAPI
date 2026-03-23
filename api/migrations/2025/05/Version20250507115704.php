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

final class Version20250507115704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'set Eiri Persona level to level of most-recent Cardea';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
        UPDATE `monster_of_the_week` SET level=(
            SELECT q.level FROM (SELECT level FROM monster_of_the_week WHERE monster='Cardea' ORDER BY id DESC LIMIT 1) AS q)
        WHERE monster='Eiri Persona';
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
