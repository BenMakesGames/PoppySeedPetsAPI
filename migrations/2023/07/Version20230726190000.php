<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230726190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE `hollow_earth_tile_card` SET `image` = 'hidden-alcove' WHERE `hollow_earth_tile_card`.`id` = 40;");
        $this->addSql("UPDATE `item` SET `image` = 'tile/hidden-alcove' WHERE `item`.`id` = 1047;");

        $this->addSql("UPDATE `hollow_earth_tile_card` SET `image` = 'sand-worm' WHERE `hollow_earth_tile_card`.`id` = 69;");
        $this->addSql("UPDATE `item` SET `image` = 'tile/sand-worm' WHERE `item`.`id` = 1196;");

        $this->addSql("UPDATE `hollow_earth_tile_card` SET `image` = 'shimmering-waterfall' WHERE `hollow_earth_tile_card`.`id` = 45;");
        $this->addSql("UPDATE `item` SET `image` = 'tile/shimmering-waterfall' WHERE `item`.`id` = 1034;");

        $this->addSql("UPDATE `hollow_earth_tile_card` SET `image` = 'orchard' WHERE `hollow_earth_tile_card`.`id` = 19;");
        $this->addSql("UPDATE `item` SET `image` = 'tile/orchard' WHERE `item`.`id` = 1031;");

        $this->addSql("UPDATE `hollow_earth_tile_card` SET `image` = 'sandstorm' WHERE `hollow_earth_tile_card`.`id` = 48;");
    }

    public function down(Schema $schema): void
    {
    }
}
