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
final class Version20210101215924 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD max_market_bids SMALLINT NOT NULL');
        $this->addSql('UPDATE user SET max_market_bids=5');
        $this->addSql('UPDATE user SET max_market_bids=max_market_bids+10 WHERE id IN (SELECT user_id FROM user_stats WHERE stat=\'Items Donated to Museum\' AND value>=400)');
        $this->addSql('UPDATE user SET max_market_bids=max_market_bids+5 WHERE id IN (SELECT user_id FROM user_stats WHERE stat=\'Items Sold in Market\' AND value>=50)');
        $this->addSql('UPDATE user SET max_market_bids=max_market_bids+5 WHERE id IN (SELECT user_id FROM user_stats WHERE stat=\'Items Bought in Market\' AND value>=50)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP max_market_bids');
    }
}
