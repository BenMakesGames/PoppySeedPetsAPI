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

final class Version20260218234944 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create library table for Library add-ons (Jukebox, etc.)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE library (id INT AUTO_INCREMENT NOT NULL, has_jukebox TINYINT NOT NULL, owner_id INT NOT NULL, UNIQUE INDEX UNIQ_A18098BC7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE library ADD CONSTRAINT FK_A18098BC7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE library DROP FOREIGN KEY FK_A18098BC7E3C61F9');
        $this->addSql('DROP TABLE library');
    }
}
