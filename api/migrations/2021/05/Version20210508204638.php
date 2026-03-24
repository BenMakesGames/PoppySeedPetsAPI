<?php

declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210508204638 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hollow_earth_tile DROP FOREIGN KEY FK_5BEE24529F2C3FAB');
        $this->addSql('CREATE TABLE hollow_earth_player_tile (id INT AUTO_INCREMENT NOT NULL, player_id INT NOT NULL, tile_id INT NOT NULL, card_id INT NOT NULL, INDEX IDX_C0B40BA299E6F5DF (player_id), INDEX IDX_C0B40BA2638AF48B (tile_id), INDEX IDX_C0B40BA24ACC9A20 (card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hollow_earth_tile_hollow_earth_tile_type (hollow_earth_tile_id INT NOT NULL, hollow_earth_tile_type_id INT NOT NULL, INDEX IDX_B6730FB2BF23116C (hollow_earth_tile_id), INDEX IDX_B6730FB2FE7800F2 (hollow_earth_tile_type_id), PRIMARY KEY(hollow_earth_tile_id, hollow_earth_tile_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hollow_earth_tile_card (id INT AUTO_INCREMENT NOT NULL, type_id INT NOT NULL, name VARCHAR(40) NOT NULL, event JSON NOT NULL, required_action INT NOT NULL, INDEX IDX_DC81353FC54C8C93 (type_id), UNIQUE INDEX name_idx (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hollow_earth_tile_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(40) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE hollow_earth_player_tile ADD CONSTRAINT FK_C0B40BA299E6F5DF FOREIGN KEY (player_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE hollow_earth_player_tile ADD CONSTRAINT FK_C0B40BA2638AF48B FOREIGN KEY (tile_id) REFERENCES hollow_earth_tile (id)');
        $this->addSql('ALTER TABLE hollow_earth_player_tile ADD CONSTRAINT FK_C0B40BA24ACC9A20 FOREIGN KEY (card_id) REFERENCES hollow_earth_tile_card (id)');
        $this->addSql('ALTER TABLE hollow_earth_tile_hollow_earth_tile_type ADD CONSTRAINT FK_B6730FB2BF23116C FOREIGN KEY (hollow_earth_tile_id) REFERENCES hollow_earth_tile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hollow_earth_tile_hollow_earth_tile_type ADD CONSTRAINT FK_B6730FB2FE7800F2 FOREIGN KEY (hollow_earth_tile_type_id) REFERENCES hollow_earth_tile_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hollow_earth_tile_card ADD CONSTRAINT FK_DC81353FC54C8C93 FOREIGN KEY (type_id) REFERENCES hollow_earth_tile_type (id)');
        $this->addSql('DROP TABLE hollow_earth_zone');
        $this->addSql('DROP INDEX IDX_5BEE24529F2C3FAB ON hollow_earth_tile');
        $this->addSql('ALTER TABLE hollow_earth_tile DROP zone_id, CHANGE event event JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE item ADD hollow_earth_tile_card_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E71F81641 FOREIGN KEY (hollow_earth_tile_card_id) REFERENCES hollow_earth_tile_card (id)');
        $this->addSql('CREATE INDEX IDX_1F1B251E71F81641 ON item (hollow_earth_tile_card_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hollow_earth_player_tile DROP FOREIGN KEY FK_C0B40BA24ACC9A20');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E71F81641');
        $this->addSql('ALTER TABLE hollow_earth_tile_hollow_earth_tile_type DROP FOREIGN KEY FK_B6730FB2FE7800F2');
        $this->addSql('ALTER TABLE hollow_earth_tile_card DROP FOREIGN KEY FK_DC81353FC54C8C93');
        $this->addSql('CREATE TABLE hollow_earth_zone (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(40) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, image VARCHAR(40) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, width INT NOT NULL, height INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE hollow_earth_player_tile');
        $this->addSql('DROP TABLE hollow_earth_tile_hollow_earth_tile_type');
        $this->addSql('DROP TABLE hollow_earth_tile_card');
        $this->addSql('DROP TABLE hollow_earth_tile_type');
        $this->addSql('ALTER TABLE hollow_earth_tile ADD zone_id INT NOT NULL, CHANGE event event JSON NOT NULL');
        $this->addSql('ALTER TABLE hollow_earth_tile ADD CONSTRAINT FK_5BEE24529F2C3FAB FOREIGN KEY (zone_id) REFERENCES hollow_earth_zone (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_5BEE24529F2C3FAB ON hollow_earth_tile (zone_id)');
        $this->addSql('DROP INDEX IDX_1F1B251E71F81641 ON item');
        $this->addSql('ALTER TABLE item DROP hollow_earth_tile_card_id');
    }
}
