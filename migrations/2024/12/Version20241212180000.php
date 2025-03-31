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

final class Version20241212180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
        UPDATE `design_goal` SET `name` = 'Cute & Nerdy', `description` = 'I started this game because I wanted to simulate Maslow\'s Hierarchy of Needs, and in doing so accidentally created a cute & nerdy game filled with affection, love, sci-fi, magic, and mythology! That cuteness & nerdiness is what drew people to the game in the first place, so it\'s something I want to be intentional about going forward!' WHERE `design_goal`.`id` = 5; 
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
