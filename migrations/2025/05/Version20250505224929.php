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

final class Version20250505224929 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
        INSERT INTO `pet_species` (`id`, `name`, `image`, `description`, `hand_x`, `hand_y`, `hand_angle`, `flip_x`, `hand_behind`, `available_from_pet_shelter`, `pregnancy_style`, `egg_image`, `hat_x`, `hat_y`, `hat_angle`, `available_from_breeding`, `sheds_id`, `family`, `name_sort`, `physical_description`) VALUES (111, 'Dancing Sword', 'elemental/sword', 'A sword brought to life.\r\n\r\nSeems a little dangerous to keep as a pet, but... you do you, I guess.', '0.7', '0.875', '41', '0', '0', '0', '-1', NULL, '0.5', '0.41', '-47', '0', '36', 'elemental', 'Dancing Sword', 'A basic sword with two legs, no arms, and no discernible face. (How does this thing even work??)')
        ON DUPLICATE KEY UPDATE `id`=`id`;
        EOSQL);

        $this->addSql(<<<EOSQL
        UPDATE `pet_species` SET `physical_description` = 'A basic sword with two legs, but no arms, and no discernible face! (How does this thing even work?? I guess that\'s just the power of the Philosopher\'s Stone...)' WHERE `pet_species`.`id` = 111; 
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
