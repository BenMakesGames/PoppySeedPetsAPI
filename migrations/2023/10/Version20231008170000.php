<?php

declare(strict_types=1);

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
