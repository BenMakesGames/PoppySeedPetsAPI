<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250703144239 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Way Too Much Lightning item groups fix';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'EOSQL'
        DELETE FROM item_group_item WHERE item_id=1466 AND item_group_id IN (11, 28, 46);
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
