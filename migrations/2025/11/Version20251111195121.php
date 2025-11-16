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

final class Version20251111195121 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Habercraber pet species';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'EOSQL'
        INSERT INTO `pet_species` (`id`, `name`, `image`, `description`, `hand_x`, `hand_y`, `hand_angle`, `flip_x`, `hand_behind`, `available_from_pet_shelter`, `pregnancy_style`, `egg_image`, `hat_x`, `hat_y`, `hat_angle`, `available_from_breeding`, `sheds_id`, `family`, `name_sort`, `physical_description`) VALUES
        (112, 'Habercraber', 'crustacean/habercraber', 'It isn\'t a haber of dashes, but it\'s _definitely_ a haber of crabs. That much is plain to see.\n', 0.835, 0.745, 61, 0, 0, 1, 0, 'fish', 0.235, 0.71, -36, 1, 34, 'crustacean', 'Habercraber', 'A crab with a short, striped, and pointed tail, and what look like tufts of hair around its head and on the front of its chest. (It must eat a lot of Brussels or Broccoli or, you know, whatever the undersea thing-you-eat-to-get-a-hairy-chest is.)')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
