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

final class Version20250114180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Badge! pet tag
        $this->addSql(<<<EOSQL
        INSERT INTO `pet_activity_log_tag` (`id`, `title`, `color`, `emoji`) VALUES (94, 'Badge!', 'E28024', 'fa-solid fa-badge-check')        
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // pets that have Metatron's Touch get the Brilliant badge:
        $this->addSql(<<<EOSQL
        INSERT INTO pet_badge (pet_id, badge, date_acquired)
        SELECT pet_id AS pet_id, 'foundMetatronsFire' AS badge, NOW() AS date_acquired
        FROM pet_merit WHERE merit_id=61
        EOSQL);

        // pets that have Ichthyastra get the Mercurial badge:
        $this->addSql(<<<EOSQL
        INSERT INTO pet_badge (pet_id, badge, date_acquired)
        SELECT pet_id AS pet_id, 'foundVesicaHydrargyrum' AS badge, NOW() AS date_acquired
        FROM pet_merit WHERE merit_id=60
        EOSQL);

        // pets that have Manxome get the The Most Egg badge:
        $this->addSql(<<<EOSQL
        INSERT INTO pet_badge (pet_id, badge, date_acquired)
        SELECT pet_id AS pet_id, 'foundEarthsEgg' AS badge, NOW() AS date_acquired
        FROM pet_merit WHERE merit_id=62
        EOSQL);

        // pets that have Manxome get the The Most Egg badge:
        $this->addSql(<<<EOSQL
        INSERT INTO pet_badge (pet_id, badge, date_acquired)
        SELECT pet_id AS pet_id, 'foundMerkabaOfAir' AS badge, NOW() AS date_acquired
        FROM pet_merit WHERE merit_id=63
        EOSQL);

        // pets that have children get the Baby badge:
        $this->addSql(<<<EOSQL
        INSERT INTO pet_badge (pet_id, badge, date_acquired)
        SELECT
          DISTINCT p.pet_id,'parent' AS badge,NOW() AS date_acquired FROM (
            SELECT DISTINCT p1.mom_id AS pet_id FROM `pet` AS p1 WHERE p1.mom_id IS NOT NULL
            UNION SELECT DISTINCT p2.dad_id AS pet_id FROM `pet` AS p2 WHERE p2.dad_id IS NOT NULL
          ) AS p
        EOSQL);

        // pets whose favorite flavor has been revealed get that badge:
        $this->addSql(<<<EOSQL
        INSERT INTO pet_badge (pet_id, badge, date_acquired)
        SELECT id AS pet_id, 'revealedFavoriteFlavor' AS badge, NOW() AS date_acquired
        FROM pet WHERE revealed_favorite_flavor>0
        EOSQL);

        // pets that already completed the heart dimension get that badge:
        $this->addSql(<<<EOSQL
        INSERT INTO pet_badge (pet_id, badge, date_acquired)
        SELECT id AS pet_id, 'completedHeartDimension' AS badge, NOW() AS date_acquired
        FROM pet WHERE affection_adventures>=6
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
