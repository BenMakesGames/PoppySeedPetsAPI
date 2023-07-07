<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230707214627 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pet ADD spirit_dad_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pet ADD CONSTRAINT FK_E4529B85E2117428 FOREIGN KEY (spirit_dad_id) REFERENCES spirit_companion (id)');
        $this->addSql('CREATE INDEX IDX_E4529B85E2117428 ON pet (spirit_dad_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pet DROP FOREIGN KEY FK_E4529B85E2117428');
        $this->addSql('DROP INDEX IDX_E4529B85E2117428 ON pet');
        $this->addSql('ALTER TABLE pet DROP spirit_dad_id');
    }
}
