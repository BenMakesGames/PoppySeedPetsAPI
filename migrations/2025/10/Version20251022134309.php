<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251022134309 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adding "Mirror" tag';
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
    }

    public function down(Schema $schema): void
    {
    }
}
