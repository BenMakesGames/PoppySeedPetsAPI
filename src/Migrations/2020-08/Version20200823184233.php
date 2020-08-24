<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200823184233 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE enchantment (id INT AUTO_INCREMENT NOT NULL, effects_id INT NOT NULL, name VARCHAR(20) NOT NULL, is_suffix TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_DBE10357568FBDB9 (effects_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE enchantment ADD CONSTRAINT FK_DBE10357568FBDB9 FOREIGN KEY (effects_id) REFERENCES item_tool (id)');
        $this->addSql('ALTER TABLE inventory ADD enchantment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE inventory ADD CONSTRAINT FK_B12D4A36F3927CF3 FOREIGN KEY (enchantment_id) REFERENCES enchantment (id)');
        $this->addSql('CREATE INDEX IDX_B12D4A36F3927CF3 ON inventory (enchantment_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE inventory DROP FOREIGN KEY FK_B12D4A36F3927CF3');
        $this->addSql('DROP TABLE enchantment');
        $this->addSql('DROP INDEX IDX_B12D4A36F3927CF3 ON inventory');
        $this->addSql('ALTER TABLE inventory DROP enchantment_id');
    }
}
