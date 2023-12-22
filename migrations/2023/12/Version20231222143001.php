<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231222143001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'description correction';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE `item` SET `description` = '\\*splortch, squalsh, squick\\*' WHERE `item`.`id` = 1354;");

    }

    public function down(Schema $schema): void
    {
    }
}
