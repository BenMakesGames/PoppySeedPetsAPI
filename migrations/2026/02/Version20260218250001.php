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

final class Version20260218250001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed songs and backfill starter songs for existing jukebox owners';
    }

    public function up(Schema $schema): void
    {
        // Add the Music player activity log tag
        $this->addSql("INSERT INTO user_activity_log_tag (title, color, emoji) VALUES ('Music', '9B59B6', '🎵')");

        // Seed the song table with the initial set of songs
        $this->addSql("INSERT INTO song (name, filename) VALUES ('frogs_life', 'music/frogs_life.ogg')");
        $this->addSql("INSERT INTO song (name, filename) VALUES ('good_morning', 'music/good_morning.ogg')");
        $this->addSql("INSERT INTO song (name, filename) VALUES ('walking_home', 'music/walking_home.ogg')");

        // Backfill: grant all starter songs to users who already have a Jukebox installed
        $this->addSql(<<<'EOSQL'
        INSERT INTO user_unlocked_song (user_id, song_id, unlocked_on, comment)
        SELECT l.owner_id, s.id, NOW(), 'This song came with your Jukebox.'
        FROM library l
        CROSS JOIN song s
        WHERE l.has_jukebox = 1
        AND s.name IN ('frogs_life', 'good_morning', 'walking_home')
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
