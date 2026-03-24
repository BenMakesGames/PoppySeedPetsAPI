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

final class Version20250114144000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE known_recipes SET recipe="Rice Noodles (A)" WHERE recipe="Rice Noodles"');
        $this->addSql('UPDATE known_recipes SET recipe="Rice Noodles (B)" WHERE recipe="Rice Noodles B"');

        $this->addSql('UPDATE known_recipes SET recipe="Mackin Cheese (A)" WHERE recipe="Mackin Cheese"');
        $this->addSql('UPDATE known_recipes SET recipe="Mackin Cheese (B)" WHERE recipe="Mackin Cheese B"');

        $this->addSql('UPDATE known_recipes SET recipe="Coffee Jelly (A)" WHERE recipe="Coffee Jelly"');
        $this->addSql('UPDATE known_recipes SET recipe="Coffee Jelly (B)" WHERE recipe="Coffee Jelly B"');
    }

    public function down(Schema $schema): void
    {
    }
}
