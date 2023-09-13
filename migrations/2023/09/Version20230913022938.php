<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230913022938 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX interesingness_idx ON pet_activity_log');
        $this->addSql('CREATE INDEX created_on_idx ON pet_activity_log (created_on)');
        $this->addSql('CREATE INDEX viewed_idx ON pet_activity_log (viewed)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX created_on_idx ON pet_activity_log');
        $this->addSql('DROP INDEX viewed_idx ON pet_activity_log');
        $this->addSql('CREATE INDEX interesingness_idx ON pet_activity_log (interestingness)');
    }
}
