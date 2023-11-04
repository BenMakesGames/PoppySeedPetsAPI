<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231104154000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
-- Dragonbreath - when you get scales, get a dragon tongue, instead
UPDATE item_tool SET when_gather_id=35, when_gather_also_gather_id=1341, when_gather_prevent_gather=1 WHERE id=434;

-- Dragondrop - when you get a pointer, get a dragon tongue, instead
UPDATE item_tool SET when_gather_id=188, when_gather_also_gather_id=1341, when_gather_prevent_gather=1 WHERE id=373;
EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
