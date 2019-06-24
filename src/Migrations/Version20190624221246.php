<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190624221246 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE pet LEFT JOIN pet_species ON pet_species.image=pet.image SET pet.species_id=pet_species.id');

        $this->addSql('ALTER TABLE pet ADD CONSTRAINT FK_E4529B85B2A1D860 FOREIGN KEY (species_id) REFERENCES pet_species (id)');
        $this->addSql('CREATE INDEX IDX_E4529B85B2A1D860 ON pet (species_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet DROP FOREIGN KEY FK_E4529B85B2A1D860');
        $this->addSql('DROP INDEX IDX_E4529B85B2A1D860 ON pet');
    }
}
