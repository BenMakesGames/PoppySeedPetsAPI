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
final class Version20231006193834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE known_recipes DROP FOREIGN KEY FK_D636E6E159D8A214');
        $this->addSql('DROP INDEX IDX_D636E6E159D8A214 ON known_recipes');
        $this->addSql('ALTER TABLE known_recipes ADD recipe VARCHAR(45) NOT NULL');

        $this->addSql('UPDATE known_recipes SET known_recipes.recipe=(SELECT recipe.name FROM recipe WHERE recipe.id=known_recipes.recipe_id)');

        $this->addSql('ALTER TABLE known_recipes DROP recipe_id');
        $this->addSql('DROP TABLE recipe');
    }

    public function down(Schema $schema): void
    {
        throw new \Exception('No :P');
    }
}
