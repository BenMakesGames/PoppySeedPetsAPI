<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230726140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE `hollow_earth_tile_card` SET `image` = 'box-of-ores' WHERE `hollow_earth_tile_card`.`id` = 29;");
        $this->addSql("UPDATE `item` SET `image` = 'tile/box-of-ores' WHERE `item`.`id` = 1046;");

        $this->addSql("UPDATE `hollow_earth_tile_card` SET `image` = 'torch' WHERE `hollow_earth_tile_card`.`id` = 30;");
        $this->addSql("UPDATE `item` SET `image` = 'tile/stereotypical-torch' WHERE `item`.`id` = 1035;");

        $this->addSql("UPDATE `hollow_earth_tile_card` SET `image` = 'noisy-goblin' WHERE `hollow_earth_tile_card`.`id` = 14;");
        $this->addSql("UPDATE `item` SET `image` = 'tile/noisy-goblin' WHERE `item`.`id` = 1023;");
    }

    public function down(Schema $schema): void
    {
    }
}
