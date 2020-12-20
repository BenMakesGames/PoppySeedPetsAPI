<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201215044621 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE letter ADD title VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX series_idx ON letter (series)');
        $this->addSql('ALTER TABLE user_letter ADD is_read TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX series_idx ON letter');
        $this->addSql('ALTER TABLE letter DROP title');
        $this->addSql('ALTER TABLE user_letter DROP is_read');
    }
}
