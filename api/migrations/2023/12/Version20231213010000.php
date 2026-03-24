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

final class Version20231213010000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Delete old known oniony smashed potatoes "known recipes", and update stats accordingly.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("DELETE FROM known_recipes WHERE recipe LIKE 'Oniony Smashed Potatoes%'");
        $this->addSql("UPDATE user_stats SET user_stats.value=(SELECT COUNT(id) FROM known_recipes WHERE known_recipes.user_id=user_stats.id) WHERE user_stats.stat='Recipes Learned by Cooking Buddy';");
    }

    public function down(Schema $schema): void
    {
    }
}
