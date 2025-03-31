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

final class Version20240505140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'more new location tags';
    }

    public const New_Tags = [
        [ 'id' => 87, 'title' => 'Location: Noetala\'s Cocoon', 'color' => '6fc56d', 'emoji' => '👁️' ],
        [ 'id' => 88, 'title' => 'Location: Cryovolcano', 'color' => '9DCFEE', 'emoji' => '🧊' ],
        [ 'id' => 89, 'title' => 'Location: Escaping Icy Moon', 'color' => '9DCFEE', 'emoji' => '🚀' ],
    ];

    public function up(Schema $schema): void
    {
        foreach(self::New_Tags as $tag)
        {
            $this->addSql(<<<EOSQL
            INSERT INTO pet_activity_log_tag
            (id, title, color, emoji)
            VALUES
            ({$tag['id']}, "{$tag['title']}", "{$tag['color']}", "{$tag['emoji']}")
            ON DUPLICATE KEY UPDATE `id` = `id`;
            EOSQL);

            $this->addSql('UPDATE pet_activity_log_tag SET title="Location: Icy Moon" WHERE title="Icy Moon"');
        }
    }

    public function down(Schema $schema): void
    {
    }
}
