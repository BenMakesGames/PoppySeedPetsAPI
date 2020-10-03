<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201003161854 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE item_grammar (id INT AUTO_INCREMENT NOT NULL, item_id INT NOT NULL, is_plural TINYINT(1) NOT NULL, article VARCHAR(10) NOT NULL, UNIQUE INDEX UNIQ_7136442D126F525E (item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE item_grammar ADD CONSTRAINT FK_7136442D126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
        $this->addSql('DROP TABLE pet_room');
        $this->addSql('DROP TABLE room_furnishing');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE room_furnishing (id INT AUTO_INCREMENT NOT NULL, pet_id INT NOT NULL, inventory_id INT NOT NULL, x DOUBLE PRECISION NOT NULL, y DOUBLE PRECISION NOT NULL, scale DOUBLE PRECISION NOT NULL, flip_x TINYINT(1) NOT NULL, angle DOUBLE PRECISION NOT NULL, z_index INT NOT NULL, INDEX IDX_84FC128E966F7FB6 (pet_id), UNIQUE INDEX UNIQ_84FC128E9EEA759 (inventory_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE pet_room (id INT AUTO_INCREMENT NOT NULL, pet_id INT NOT NULL, wallpaper VARCHAR(40) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, floor VARCHAR(40) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, max_furnishings INT NOT NULL, UNIQUE INDEX UNIQ_69D7B4F1966F7FB6 (pet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE room_furnishing ADD CONSTRAINT FK_84FC128E966F7FB6 FOREIGN KEY (pet_id) REFERENCES pet (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE room_furnishing ADD CONSTRAINT FK_84FC128E9EEA759 FOREIGN KEY (inventory_id) REFERENCES inventory (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE pet_room ADD CONSTRAINT FK_69D7B4F1966F7FB6 FOREIGN KEY (pet_id) REFERENCES pet (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('DROP TABLE item_grammar');
    }
}
