<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200502033747 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE trades_unlocked (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, trades INT NOT NULL, INDEX IDX_5DFCD454A76ED395 (user_id), UNIQUE INDEX trades_unique (user_id, trades), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE trades_unlocked ADD CONSTRAINT FK_5DFCD454A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD unlocked_trader DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE lunchbox_item DROP INDEX IDX_7440444C536BF4A2, ADD UNIQUE INDEX UNIQ_7440444C536BF4A2 (inventory_item_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE trades_unlocked');
        $this->addSql('ALTER TABLE lunchbox_item DROP INDEX UNIQ_7440444C536BF4A2, ADD INDEX IDX_7440444C536BF4A2 (inventory_item_id)');
        $this->addSql('ALTER TABLE user DROP unlocked_trader');
    }
}
