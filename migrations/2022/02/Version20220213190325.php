<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220213190325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fireplace ADD alcohol INT NOT NULL, ADD gnome_points INT NOT NULL');
        $this->addSql('CREATE INDEX alcohol_index ON fireplace (alcohol)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX alcohol_index ON fireplace');
        $this->addSql('ALTER TABLE fireplace DROP alcohol, DROP gnome_points');
    }
}
