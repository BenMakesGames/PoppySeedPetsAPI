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

final class Version20251129182639 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add Serapin species to the game';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'EOSQL'
        INSERT INTO `pet_species`
        (`id`, `name`, `image`, `description`, `hand_x`, `hand_y`, `hand_angle`, `flip_x`, `hand_behind`, `available_from_pet_shelter`, `pregnancy_style`, `egg_image`, `hat_x`, `hat_y`, `hat_angle`, `available_from_breeding`, `sheds_id`, `family`, `name_sort`, `physical_description`)
        VALUES
        (114, 'Serapin', 'bird/serapin', 'A bird species evolved to fly on six wings. Since emerging from the Hollow Earth, many have settled in the cliffs around a lagoon on the east side of Poppy Seed Pets Island.', '0.485', '0.775', '-94', '1', '0', '1', '0', 'striped', '0.215', '0.375', '-20', '1', '144', 'bird', 'Serapin', 'It has three pairs of wings, and a long tail with a decorative marking on its tip. It wears a ring of dry grass and cobwebs around its neck, given to it by its mother when its born.')
        ON DUPLICATE KEY UPDATE id=id;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
