<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250621223910 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Sportsball update';
    }

    public function up(Schema $schema): void
    {
        // Orange Sportsball Ball
        $this->addSql(<<<'EOSQL'
        UPDATE `item_tool`
        SET
            `adventure_description` = 'do a hoop',
            `leads_to_adventure` = '1'
        WHERE `item_tool`.`id` = 368; 
        EOSQL);

        // Sportsball Pin
        $this->addSql(<<<'EOSQL'
        UPDATE `item_tool`
        SET
            `adventure_description` = 'go skittling',
            `leads_to_adventure` = '1'
        WHERE `item_tool`.`id` = 369; 
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
