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

final class Version20240105260000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'community booster pack';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
        INSERT INTO `item_group` (`id`, `name`, `is_craving`, `is_gift_shop`) VALUES
        (37, 'Community Booster Pack: Common', '0', '0'),
        (38, 'Community Booster Pack: Uncommon', '0', '0'),
        (39, 'Community Booster Pack: Rare', '0', '0')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        //$this->addSql();
    }

    public function down(Schema $schema): void
    {
    }
}
