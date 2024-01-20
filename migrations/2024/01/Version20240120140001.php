<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240120140001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'put books into book item group';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO item_group_item (item_group_id, item_id) SELECT 40, item.id FROM item WHERE item.image LIKE 'book/%';");
    }

    public function down(Schema $schema): void
    {
    }
}
