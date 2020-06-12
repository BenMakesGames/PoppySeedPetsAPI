<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200611220422 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE daily_market_inventory_transaction (id INT AUTO_INCREMENT NOT NULL, inventory_id INT NOT NULL, user_id INT NOT NULL, price INT NOT NULL, INDEX IDX_B0E9DC809EEA759 (inventory_id), INDEX IDX_B0E9DC80A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE daily_market_inventory_transaction ADD CONSTRAINT FK_B0E9DC809EEA759 FOREIGN KEY (inventory_id) REFERENCES inventory (id)');
        $this->addSql('ALTER TABLE daily_market_inventory_transaction ADD CONSTRAINT FK_B0E9DC80A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('DROP TABLE daily_market_inventory_transactions');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE daily_market_inventory_transactions (id INT AUTO_INCREMENT NOT NULL, inventory_id INT NOT NULL, user_id INT NOT NULL, price INT NOT NULL, INDEX IDX_F606A5F79EEA759 (inventory_id), INDEX IDX_F606A5F7A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE daily_market_inventory_transactions ADD CONSTRAINT FK_F606A5F79EEA759 FOREIGN KEY (inventory_id) REFERENCES inventory (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE daily_market_inventory_transactions ADD CONSTRAINT FK_F606A5F7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('DROP TABLE daily_market_inventory_transaction');
    }
}
