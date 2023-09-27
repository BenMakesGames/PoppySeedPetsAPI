<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230927022600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<ENDSQL
            UPDATE `item` SET `use_actions` = '[["Read","arcanaSkillScroll","page"]]' WHERE `item`.`id` = 1000;
ENDSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
