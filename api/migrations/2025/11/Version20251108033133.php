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

final class Version20251108033133 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'EOSQL'
        UPDATE user_menu_order
        SET menu_order = REPLACE(menu_order, 'starKindred', 'library')
        WHERE menu_order LIKE '%starKindred%';
        EOSQL);

        $this->addSql(<<<'EOSQL'
        UPDATE user_menu_order
        SET menu_order = REPLACE(menu_order, 'hollowEarth,', '')
        WHERE menu_order LIKE '%hollowEarth,%';
        EOSQL);

        $this->addSql(<<<'EOSQL'
        UPDATE user_menu_order
        SET menu_order = REPLACE(menu_order, ',hollowEarth', '')
        WHERE menu_order LIKE '%,hollowEarth%';
        EOSQL);

        $this->addSql(<<<'EOSQL'
        INSERT INTO user_unlocked_feature
        (user_id, feature, unlocked_on)
        SELECT uuf.user_id,'Library' AS feature, MIN(uuf.unlocked_on)
        FROM user_unlocked_feature uuf
        WHERE uuf.feature IN ('Hollow Earth', 'Star Kindred')
        GROUP BY uuf.user_id
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
