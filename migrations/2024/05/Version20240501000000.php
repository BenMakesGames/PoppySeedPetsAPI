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

final class Version20240501000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'new location tags';
    }

    public const New_Tags = [
        [ 'id' => 79, 'title' => 'Rain', 'color' => '19b6cf', 'emoji' => '🌧' ],
        [ 'id' => 80, 'title' => 'Location: Neighborhood', 'color' => 'baaa96', 'emoji' => '🏘' ],
        [ 'id' => 81, 'title' => 'Location: Micro-jungle', 'color' => '1a7c2c', 'emoji' => '🌳' ],
        [ 'id' => 82, 'title' => 'Location: Stream', 'color' => '4d8abd', 'emoji' => '〰' ],
        [ 'id' => 83, 'title' => 'Location: Small Lake', 'color' => '4d8abd', 'emoji' => '〰' ],
        [ 'id' => 84, 'title' => 'Location: Under a Bridge', 'color' => '4d8abd', 'emoji' => '🌉' ],
        [ 'id' => 85, 'title' => 'Location: Roadside Creek', 'color' => '4d8abd', 'emoji' => '〰' ],
        [ 'id' => 86, 'title' => 'Location: At Home', 'color' => 'baaa96', 'emoji' => '🏠' ],
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
        }
    }

    public function down(Schema $schema): void
    {
    }
}
