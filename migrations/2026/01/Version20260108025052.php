<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260108025052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inventory_enchantment (hue INT NOT NULL, inventory_id INT NOT NULL, enchantment_id INT NOT NULL, INDEX IDX_AF133F98F3927CF3 (enchantment_id), PRIMARY KEY (inventory_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE inventory_enchantment ADD CONSTRAINT FK_AF133F989EEA759 FOREIGN KEY (inventory_id) REFERENCES inventory (id)');
        $this->addSql('ALTER TABLE inventory_enchantment ADD CONSTRAINT FK_AF133F98F3927CF3 FOREIGN KEY (enchantment_id) REFERENCES enchantment (id)');

        // Migrate existing enchantment data to the new bridge table
        $this->addSql('INSERT INTO inventory_enchantment (inventory_id, enchantment_id, hue) SELECT id, enchantment_id, 0 FROM inventory WHERE enchantment_id IS NOT NULL');

        $this->addSql('ALTER TABLE inventory DROP FOREIGN KEY `FK_B12D4A36F3927CF3`');
        $this->addSql('DROP INDEX IDX_B12D4A36F3927CF3 ON inventory');
        $this->addSql('ALTER TABLE inventory DROP enchantment_id');
    }

    public function down(Schema $schema): void
    {
    }
}
