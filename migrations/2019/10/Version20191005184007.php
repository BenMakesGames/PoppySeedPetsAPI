<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191005184007 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE hollow_earth_player (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, current_tile_id INT NOT NULL, current_action JSON DEFAULT NULL, moves_remaining INT NOT NULL, UNIQUE INDEX UNIQ_E7F1524BA76ED395 (user_id), INDEX IDX_E7F1524BC987710E (current_tile_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE hollow_earth_player ADD CONSTRAINT FK_E7F1524BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE hollow_earth_player ADD CONSTRAINT FK_E7F1524BC987710E FOREIGN KEY (current_tile_id) REFERENCES hollow_earth_tile (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE hollow_earth_player');
    }
}
