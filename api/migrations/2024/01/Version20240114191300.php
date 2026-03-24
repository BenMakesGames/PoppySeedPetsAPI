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
final class Version20240114191300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'remove duplicate recipes';
    }

    public function up(Schema $schema): void
    {
        // HEY, LISTEN: this way of deleting duplicates is really inefficient.
        // 'delete duplicate museum donations' contains a better way.
        $this->addSql('
            DELETE t1 FROM known_recipes t1
            JOIN known_recipes t2 
            WHERE 
                t1.id > t2.id AND 
                t1.user_id = t2.user_id AND 
                t1.recipe = t2.recipe;
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
