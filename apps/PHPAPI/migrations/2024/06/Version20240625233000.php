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
final class Version20240625233000 extends AbstractMigration
{
    public const IconUpdates = [
        [ 'title' => 'Moneys', 'icon' => 'fa-solid fa-coins' ],
        [ 'title' => 'Recycling', 'icon' => 'fa-solid fa-recycle' ],
        [ 'title' => 'Market', 'icon' => 'fa-solid fa-store' ],
        [ 'title' => 'Fireplace', 'icon' => 'fa-solid fa-fireplace' ],
        [ 'title' => 'Greenhouse', 'icon' => 'fa-solid fa-bag-seedling' ],
        [ 'title' => 'Beehive', 'icon' => 'fa-solid fa-bee' ],
        [ 'title' => 'Account & Security', 'icon' => 'fa-solid fa-lock-keyhole' ],
        [ 'title' => 'Grocer', 'icon' => 'fa-solid fa-apple-whole' ],
        [ 'title' => 'Hattier', 'icon' => 'fa-solid fa-hat-beach' ],
        [ 'title' => 'Satyr Dice', 'icon' => 'fa-regular fa-dice' ],
        [ 'title' => 'Earth Day', 'icon' => 'fa-solid fa-tree-deciduous' ],
        [ 'title' => 'Fae-kind', 'icon' => 'fa-solid fa-person-dress-fairy' ],
        [ 'title' => 'Trader', 'icon' => 'fa-solid fa-scale-balanced' ],
        [ 'title' => 'Shirikodama', 'icon' => 'fa-solid fa-circle-small' ],
        [ 'title' => 'Halloween', 'icon' => 'fa-solid fa-jack-o-lantern' ],
        [ 'title' => 'Stocking Stuffing Season', 'icon' => 'fa-solid fa-stocking' ],
        [ 'title' => 'Birdbath', 'icon' => 'fa-regular fa-bird' ],
    ];

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        foreach(self::IconUpdates as $update)
        {
            $this->addSql(<<<EOSQL
            UPDATE user_activity_log_tag
            SET emoji = "{$update['icon']}"
            WHERE title = "{$update['title']}";
            EOSQL);
        }
    }

    public function down(Schema $schema): void
    {
        foreach(self::IconUpdates as $update)
        {
            $this->addSql(<<<EOSQL
            UPDATE user_activity_log_tag
            SET emoji = ""
            WHERE title = "{$update['title']}";
            EOSQL);
        }
    }
}
