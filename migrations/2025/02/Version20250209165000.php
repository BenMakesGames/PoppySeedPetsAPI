<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250209165000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Sleet
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = '\"what if a keen of a lean wind flays<br>\r\nscreaming hills with sleet and snow:<br>\r\nstrangles valleys by ropes of thing<br>\r\nand stifles forests in white ago?\"<br>\r\n~ e.e. cummings' WHERE `item`.`id` = 856;
        EOSQL);

        // Horizon Mirror
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = '\"what if a dawn of a doom of a dream<br>\r\nbites this universe in two,<br>\r\npeels forever out of his grave<br>\r\nand sprinkles nowhere with me and you?\"<br>\r\n~ e.e. cummings' WHERE `item`.`id` = 1082;  
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
