<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231002161851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inventory ADD illusion_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE inventory ADD CONSTRAINT FK_B12D4A367EC2F841 FOREIGN KEY (illusion_id) REFERENCES item (id)');
        $this->addSql('CREATE INDEX IDX_B12D4A367EC2F841 ON inventory (illusion_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inventory DROP FOREIGN KEY FK_B12D4A367EC2F841');
        $this->addSql('DROP INDEX IDX_B12D4A367EC2F841 ON inventory');
        $this->addSql('ALTER TABLE inventory DROP illusion_id');
    }
}
