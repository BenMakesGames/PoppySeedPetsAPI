<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200804023702 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX social_energy_idx ON pet');
        $this->addSql('DROP INDEX time_idx ON pet');
        $this->addSql('ALTER TABLE pet DROP time, DROP time_spent, DROP social_energy');
        $this->addSql('CREATE INDEX activity_time_idx ON pet_house_time (activity_time)');
        $this->addSql('CREATE INDEX social_energy_idx ON pet_house_time (social_energy)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet ADD time INT NOT NULL, ADD time_spent INT NOT NULL, ADD social_energy INT NOT NULL');
        $this->addSql('CREATE INDEX social_energy_idx ON pet (social_energy)');
        $this->addSql('CREATE INDEX time_idx ON pet (time)');
        $this->addSql('DROP INDEX activity_time_idx ON pet_house_time');
        $this->addSql('DROP INDEX social_energy_idx ON pet_house_time');
    }
}
