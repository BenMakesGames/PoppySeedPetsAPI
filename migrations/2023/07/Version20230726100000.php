<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230726100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE `hollow_earth_tile_card` SET `image` = 'eye' WHERE `hollow_earth_tile_card`.`id` = 16;");
        $this->addSql("UPDATE `item` SET `image` = 'tile/apiary' WHERE `item`.`id` = 1198;");
        $this->addSql("UPDATE `item` SET `image` = 'tile/inspiring-valley' WHERE `item`.`id` = 1024;");
        $this->addSql("UPDATE `item` SET `image` = 'tile/tangled-up-in-green' WHERE `item`.`id` = 1195;");
        $this->addSql("UPDATE `item` SET `image` = 'tile/waterway-robbers' WHERE `item`.`id` = 1055;");
        $this->addSql("UPDATE `item` SET `image` = 'tile/glowing-protojelly' WHERE `item`.`id` = 1194;");
    }

    public function down(Schema $schema): void
    {
    }
}
