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

final class Version20241221075500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Ichthyastra
        $this->addSql(<<<EOSQL
        INSERT INTO `merit` (`id`, `name`, `description`) VALUES (60, 'Ichthyastra', 'A third of the Fish that %pet.name% acquires will have a random spice applied, however %pet.name% will never again find a Vesica Hydrargyrum.')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // Metatron's Touch
        $this->addSql(<<<EOSQL
        INSERT INTO `merit` (`id`, `name`, `description`) VALUES (61, 'Metatron\'s Touch', 'When %pet.name% makes something with Firestone, a Rock is leftover, however %pet.name% will never again find Metatron\'s Fire.')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // Manxome
        $this->addSql(<<<EOSQL
        INSERT INTO `merit` (`id`, `name`, `description`) VALUES (62, 'Manxome', 'If %pet.name% has less Dexterity than Stamina, it gets +1 Dexterity, otherwise it gets +1 Stamina. Regardless, %pet.name% will never again find Earth\'s Egg.')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // Lightning Reins
        $this->addSql(<<<EOSQL
        INSERT INTO `merit` (`id`, `name`, `description`) VALUES (63, 'Lightning Reins', 'Whenever %pet.name% collects Lightning in a Bottle, it also gets Quintessence, however %pet.name% will never again find a Merkaba of Air.')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
