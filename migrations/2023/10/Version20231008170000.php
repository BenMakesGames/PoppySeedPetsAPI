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

final class Version20231008170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO item_group (name, is_craving, is_gift_shop) VALUES ('Flower', 1, 0)");

        $this->addSql(<<<EOSQL
INSERT INTO item_group_item (item_group_id, item_id)
SELECT item_group.id,item.id
FROM item
LEFT JOIN item_group ON item_group.name = 'Flower'
WHERE item.image LIKE 'flower/%';
EOSQL);

        $this->addSql(<<<EOSQL
INSERT INTO item_group_item (item_group_id, item_id)
SELECT item_group.id,item.id
FROM item
LEFT JOIN item_group ON item_group.name = 'Flower'
WHERE item.name IN(
    'Candied Lotus Petals',
    'Lotusjar',
    'Sunflower Stick',
    'Mericarp',
    'Silvered Mericarp',
    'Gilded Mericarp',
    'Sunless Mericarp',
    'Flowerbomb',
    'Flower Basket',
    'Book of Flowers',
    'Scroll of Flowers',
    'Tile: Flower Basket'
);
EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
