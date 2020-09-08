<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200101223539 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet_group CHANGE last_met_date last_met_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE INDEX created_on_idx ON pet_group (created_on)');
        $this->addSql('CREATE INDEX last_met_on_idx ON pet_group (last_met_on)');
        $this->addSql('CREATE INDEX type_idx ON pet_group (type)');
        $this->addSql('CREATE INDEX name_idx ON pet_group (name)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX created_on_idx ON pet_group');
        $this->addSql('DROP INDEX last_met_on_idx ON pet_group');
        $this->addSql('DROP INDEX type_idx ON pet_group');
        $this->addSql('DROP INDEX name_idx ON pet_group');
        $this->addSql('ALTER TABLE pet_group CHANGE last_met_on last_met_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
