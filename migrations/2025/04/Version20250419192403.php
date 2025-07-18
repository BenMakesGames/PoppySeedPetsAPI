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

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250419192403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE pet_skills CHANGE level level INT NOT NULL
        SQL);

        $this->addSql(<<<'SQL'
            UPDATE pet_skills SET level = stealth + nature + brawl + arcana + crafts + music + science
        SQL);

        // Award level-based badges to qualifying pets
        $this->addSql(<<<'SQL'
            INSERT INTO pet_badge (pet_id, badge, date_acquired)
            SELECT p.id, 'level20', CURRENT_TIMESTAMP
            FROM pet p
            JOIN pet_skills ps ON p.skills_id = ps.id
            LEFT JOIN pet_badge pb ON p.id = pb.pet_id AND pb.badge = 'level20'
            WHERE ps.level >= 20 AND pb.pet_id IS NULL
        SQL);

        $this->addSql(<<<'SQL'
            INSERT INTO pet_badge (pet_id, badge, date_acquired)
            SELECT p.id, 'level40', CURRENT_TIMESTAMP
            FROM pet p
            JOIN pet_skills ps ON p.skills_id = ps.id
            LEFT JOIN pet_badge pb ON p.id = pb.pet_id AND pb.badge = 'level40'
            WHERE ps.level >= 40 AND pb.pet_id IS NULL
        SQL);

        $this->addSql(<<<'SQL'
            INSERT INTO pet_badge (pet_id, badge, date_acquired)
            SELECT p.id, 'level60', CURRENT_TIMESTAMP
            FROM pet p
            JOIN pet_skills ps ON p.skills_id = ps.id
            LEFT JOIN pet_badge pb ON p.id = pb.pet_id AND pb.badge = 'level60'
            WHERE ps.level >= 60 AND pb.pet_id IS NULL
        SQL);

        $this->addSql(<<<'SQL'
            INSERT INTO pet_badge (pet_id, badge, date_acquired)
            SELECT p.id, 'level80', CURRENT_TIMESTAMP
            FROM pet p
            JOIN pet_skills ps ON p.skills_id = ps.id
            LEFT JOIN pet_badge pb ON p.id = pb.pet_id AND pb.badge = 'level80'
            WHERE ps.level >= 80 AND pb.pet_id IS NULL
        SQL);

        $this->addSql(<<<'SQL'
            INSERT INTO pet_badge (pet_id, badge, date_acquired)
            SELECT p.id, 'level100', CURRENT_TIMESTAMP
            FROM pet p
            JOIN pet_skills ps ON p.skills_id = ps.id
            LEFT JOIN pet_badge pb ON p.id = pb.pet_id AND pb.badge = 'level100'
            WHERE ps.level >= 100 AND pb.pet_id IS NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE pet_skills CHANGE level level INT GENERATED ALWAYS AS (stealth + nature + brawl + arcana + crafts + music + science) STORED
        SQL);
    }
}
