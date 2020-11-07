<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201106204907 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inventory ADD spice_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE inventory ADD CONSTRAINT FK_B12D4A36CF04D12D FOREIGN KEY (spice_id) REFERENCES spice (id)');
        $this->addSql('CREATE INDEX IDX_B12D4A36CF04D12D ON inventory (spice_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inventory DROP FOREIGN KEY FK_B12D4A36CF04D12D');
        $this->addSql('DROP INDEX IDX_B12D4A36CF04D12D ON inventory');
        $this->addSql('ALTER TABLE inventory DROP spice_id');
    }
}
