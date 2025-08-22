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

final class Version20250822200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'fix - Add creamy flavor to Gulab Jamun, and increase food, love, and junk';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE item_food SET creamy = 1,food=3,love=3,junk=3 WHERE id = 523");
    }

    public function down(Schema $schema): void
    {
    }
}
