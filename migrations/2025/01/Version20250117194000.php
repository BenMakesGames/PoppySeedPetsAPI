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

final class Version20250117194000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        for($level = 20; $level <= 100; $level += 20)
        {
            // level 20 badge:
            $this->addSql(<<<EOSQL
            INSERT INTO pet_badge (pet_id, badge, date_acquired)
            SELECT pet.id AS pet_id, 'level{$level}' AS badge, NOW() AS date_acquired
            FROM pet
            LEFT JOIN pet_skills ON pet.skills_id=pet_skills.id
            WHERE
                pet_skills.nature + 
                pet_skills.brawl + 
                pet_skills.arcana + 
                pet_skills.stealth + 
                pet_skills.crafts + 
                pet_skills.music + 
                pet_skills.science >= {$level}
            EOSQL);
        }
    }

    public function down(Schema $schema): void
    {
    }
}
