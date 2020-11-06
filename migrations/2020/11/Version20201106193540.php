<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201106193540 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE spice (id INT AUTO_INCREMENT NOT NULL, effects_id INT NOT NULL, name VARCHAR(20) NOT NULL, is_suffix TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_27619082568FBDB9 (effects_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE spice ADD CONSTRAINT FK_27619082568FBDB9 FOREIGN KEY (effects_id) REFERENCES item_food (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE spice');
    }
}
