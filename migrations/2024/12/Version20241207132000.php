<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241207132000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update cooking buddies';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
        UPDATE item
        SET use_actions = '[["Install","cookingBuddy/#/addOrReplace"]]'
        WHERE NAME IN ('Cooking Buddy', 'Cooking "Alien"')
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
