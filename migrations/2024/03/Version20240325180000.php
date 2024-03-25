<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240325180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'fix Holi activity log icons';
    }

    public function up(Schema $schema): void
    {
        // wearable mermaid eggs
        $this->addSql(<<<EOSQL
            UPDATE `pet_activity_log` SET icon='calendar/holidays/holi' WHERE icon='ui/holidays/holi' AND created_on >= '2024-03-24'
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
