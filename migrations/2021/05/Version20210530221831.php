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
final class Version20210530221831 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE daily_stats ADD unlocked_trader1_day INT DEFAULT NULL, ADD unlocked_trader3_day INT DEFAULT NULL, ADD unlocked_trader7_day INT DEFAULT NULL, ADD unlocked_trader28_day INT DEFAULT NULL, ADD unlocked_trader_lifetime INT DEFAULT NULL, ADD unlocked_fireplace1_day INT DEFAULT NULL, ADD unlocked_fireplace3_day INT DEFAULT NULL, ADD unlocked_fireplace7_day INT DEFAULT NULL, ADD unlocked_fireplace28_day INT DEFAULT NULL, ADD unlocked_fireplace_lifetime INT DEFAULT NULL, ADD unlocked_greenhouse1_day INT DEFAULT NULL, ADD unlocked_greenhouse3_day INT DEFAULT NULL, ADD unlocked_greenhouse7_day INT DEFAULT NULL, ADD unlocked_greenhouse28_day INT DEFAULT NULL, ADD unlocked_greenhouse_lifetime INT DEFAULT NULL, ADD unlocked_beehive1_day INT DEFAULT NULL, ADD unlocked_beehive3_day INT DEFAULT NULL, ADD unlocked_beehive7_day INT DEFAULT NULL, ADD unlocked_beehive28_day INT DEFAULT NULL, ADD unlocked_beehive_lifetime INT DEFAULT NULL, ADD unlocked_portal1_day INT DEFAULT NULL, ADD unlocked_portal3_day INT DEFAULT NULL, ADD unlocked_portal7_day INT DEFAULT NULL, ADD unlocked_portal28_day INT DEFAULT NULL, ADD unlocked_portal_lifetime INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE daily_stats DROP unlocked_trader1_day, DROP unlocked_trader3_day, DROP unlocked_trader7_day, DROP unlocked_trader28_day, DROP unlocked_trader_lifetime, DROP unlocked_fireplace1_day, DROP unlocked_fireplace3_day, DROP unlocked_fireplace7_day, DROP unlocked_fireplace28_day, DROP unlocked_fireplace_lifetime, DROP unlocked_greenhouse1_day, DROP unlocked_greenhouse3_day, DROP unlocked_greenhouse7_day, DROP unlocked_greenhouse28_day, DROP unlocked_greenhouse_lifetime, DROP unlocked_beehive1_day, DROP unlocked_beehive3_day, DROP unlocked_beehive7_day, DROP unlocked_beehive28_day, DROP unlocked_beehive_lifetime, DROP unlocked_portal1_day, DROP unlocked_portal3_day, DROP unlocked_portal7_day, DROP unlocked_portal28_day, DROP unlocked_portal_lifetime');
    }
}
