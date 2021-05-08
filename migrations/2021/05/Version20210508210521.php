<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210508210521 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hollow_earth_tile ADD card_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE hollow_earth_tile ADD CONSTRAINT FK_5BEE24524ACC9A20 FOREIGN KEY (card_id) REFERENCES hollow_earth_tile_card (id)');
        $this->addSql('CREATE INDEX IDX_5BEE24524ACC9A20 ON hollow_earth_tile (card_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hollow_earth_tile DROP FOREIGN KEY FK_5BEE24524ACC9A20');
        $this->addSql('DROP INDEX IDX_5BEE24524ACC9A20 ON hollow_earth_tile');
        $this->addSql('ALTER TABLE hollow_earth_tile DROP card_id');
    }
}
