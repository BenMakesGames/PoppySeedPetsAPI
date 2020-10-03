<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190921193752 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet ADD sex_drive SMALLINT NOT NULL, CHANGE poly poly SMALLINT NOT NULL');
        $this->addSql('UPDATE pet SET sex_drive=-1 WHERE would_bang_fraction = 4');
        $this->addSql('UPDATE pet SET sex_drive=0 WHERE would_bang_fraction IN (5, 6)');
        $this->addSql('UPDATE pet SET sex_drive=1 WHERE would_bang_fraction > 7');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet DROP sex_drive, CHANGE poly poly TINYINT(1) NOT NULL');
    }
}
