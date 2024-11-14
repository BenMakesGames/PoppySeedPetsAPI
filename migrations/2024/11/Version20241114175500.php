<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241114175500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '4-function Calculator changes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
            UPDATE `item` SET `use_actions` = '[[\"Read Manual\",\"fourFunctionCalculator/#/read\"]]' WHERE `item`.`id` = 1232;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
