<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250113201000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // description for Thaumatoxic Cookies
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'These cookies radiate dangerous levels of pure, magical energy. They definitely should NOT be eaten, but perhaps your pets can find some other use for them...' WHERE `item`.`id` = 1433; 
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
