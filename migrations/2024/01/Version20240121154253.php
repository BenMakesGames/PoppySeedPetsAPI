<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240121154253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE beehive ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE dragon ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE fireplace ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE greenhouse_plant ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE hollow_earth_player ADD version INT DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE beehive DROP version');
        $this->addSql('ALTER TABLE dragon DROP version');
        $this->addSql('ALTER TABLE fireplace DROP version');
        $this->addSql('ALTER TABLE greenhouse_plant DROP version');
        $this->addSql('ALTER TABLE hollow_earth_player DROP version');
    }
}
