<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200606180311 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE INDEX name_idx ON pet (name)');
        $this->addSql('CREATE INDEX name_idx ON pet_species (name)');
        $this->addSql('CREATE INDEX family_idx ON pet_species (family)');
        $this->addSql('ALTER TABLE pet_activity_log ADD viewed TINYINT(1) NOT NULL');

        $this->addSql('UPDATE pet_activity_log SET viewed=1');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX name_idx ON pet');
        $this->addSql('ALTER TABLE pet_activity_log DROP viewed');
        $this->addSql('DROP INDEX name_idx ON pet_species');
        $this->addSql('DROP INDEX family_idx ON pet_species');
    }
}
