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

final class Version20251022134309 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adding "Mirror" tag & hedge maze locations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO item_group (name, is_craving, is_gift_shop) VALUES ('Mirror', 1, 0)");

        $mirrorItemNames = [
            'Dark Mirror',
            'Enchanted Compass',
            'Gold Bar',
            'Gold Compass',
            'Horizon Mirror',
            'Iron Bar',
            'LP',
            'Magic Mirror',
            'Mirror',
            'Mirror Shield',
            'Pandemirrorum',
            'Silver Bar',
            'Single',
        ];

        foreach($mirrorItemNames as $itemName) {
            $this->addSql(
                'INSERT INTO item_group_item (item_group_id, item_id) ' .
                'SELECT ig.id, i.id FROM item_group ig, item i ' .
                'WHERE ig.name = \'Mirror\' AND i.name = ?',
                [$itemName]
            );
        }

        $this->addSql(<<<'EOSQL'
            INSERT INTO `pet_activity_log_tag`
            (`id`, `title`, `color`, `emoji`)
            VALUES
            (98, 'Location: Hedge Maze', '4d6b49', 'fa-solid fa-trees'),
            (99, 'Location: Hedge Maze Sphinx', '4d6b49', 'fa-solid fa-cat'),
            (100, 'Location: Hedge Maze Light Puzzle', '4d6b49', 'fa-solid fa-angle')
            ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
