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

final class Version20260218250000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create song and user_unlocked_song tables for Jukebox song management';
    }

    public function up(Schema $schema): void
    {
        // Create song table
        $this->addSql('CREATE TABLE song (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, filename VARCHAR(200) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');

        // Create user_unlocked_song table
        $this->addSql('CREATE TABLE user_unlocked_song (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, song_id INT NOT NULL, unlocked_on DATETIME NOT NULL, comment VARCHAR(255) NOT NULL, UNIQUE INDEX user_id_song_id_idx (user_id, song_id), INDEX IDX_USER_UNLOCKED_SONG_USER (user_id), INDEX IDX_USER_UNLOCKED_SONG_SONG (song_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user_unlocked_song ADD CONSTRAINT FK_USER_UNLOCKED_SONG_USER FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_unlocked_song ADD CONSTRAINT FK_USER_UNLOCKED_SONG_SONG FOREIGN KEY (song_id) REFERENCES song (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_unlocked_song DROP FOREIGN KEY FK_USER_UNLOCKED_SONG_USER');
        $this->addSql('ALTER TABLE user_unlocked_song DROP FOREIGN KEY FK_USER_UNLOCKED_SONG_SONG');
        $this->addSql('DROP TABLE user_unlocked_song');
        $this->addSql('DROP TABLE song');
    }
}
