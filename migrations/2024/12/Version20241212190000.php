<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241212190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // add a "Cooking" item group
        $this->addSql(<<<EOSQL
        INSERT INTO `item_group` (`id`, `name`, `is_craving`, `is_gift_shop`) VALUES
        (46, 'Cooking', 0, 0)
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // add all foods & spices to the "Cooking" item group
        $this->addSql(<<<EOSQL
        INSERT INTO item_group_item (item_group_id, item_id)
        SELECT 46 AS item_group_id, item.id AS item_id
        FROM item
        WHERE item.food_id IS NOT NULL OR item.spice_id IS NOT NULL
        ON DUPLICATE KEY UPDATE `item_group_id` = `item_group_id`;
        EOSQL);

        // add items that "feel foodish" to the "Cooking" item group
        $this->addSql(<<<EOSQL
        INSERT INTO item_group_item (item_group_id, item_id)
        SELECT 46 AS item_group_id, item.id AS item_id
        FROM item
        WHERE item.name IN (
            'Aging Powder',
            'Baking Powder',
            'Baking Soda',
            'Bean Flour',
            'Charcoal',
            'Cocoa Beans',
            'Cocoa Powder',
            'Coffee Beans',
            'Corn Starch',
            'Corn Syrup',
            'Cream of Tartar',
            'Magic Beans',
            'Oil',
            'Rice Flour',
            'Soy Sauce',
            'Tea Leaves',
            'Vinegar'
        )
        ON DUPLICATE KEY UPDATE `item_group_id` = `item_group_id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
