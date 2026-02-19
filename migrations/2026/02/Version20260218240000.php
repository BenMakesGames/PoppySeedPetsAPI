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

final class Version20260218240000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Strip legacy DBAL DC2Type comments from all datetime columns';
    }

    public function up(Schema $schema): void
    {
        $rows = $this->connection->fetchAllAssociative(<<<'SQL'
            SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND COLUMN_COMMENT LIKE '%(DC2Type:%'
            ORDER BY TABLE_NAME, COLUMN_NAME
        SQL);

        foreach ($rows as $row) {
            $table  = $row['TABLE_NAME'];
            $column = $row['COLUMN_NAME'];
            $type   = $row['COLUMN_TYPE'];

            $nullable = $row['IS_NULLABLE'] === 'YES' ? 'NULL' : 'NOT NULL';

            $default = '';
            if ($row['COLUMN_DEFAULT'] !== null) {
                $default = sprintf("DEFAULT '%s'", $row['COLUMN_DEFAULT']);
            } elseif ($row['IS_NULLABLE'] === 'YES') {
                $default = 'DEFAULT NULL';
            }

            $extra = '';
            if (!empty($row['EXTRA']) && $row['EXTRA'] !== 'DEFAULT_GENERATED') {
                $extra = $row['EXTRA'];
            }

            $this->addSql(
                "ALTER TABLE `$table` MODIFY `$column` $type $nullable $default $extra COMMENT ''"
            );
        }
    }

    public function down(Schema $schema): void
    {
    }
}
