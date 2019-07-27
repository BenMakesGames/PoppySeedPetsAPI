<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190727183852 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE park_event_prize (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, prize_id INT NOT NULL, place INT NOT NULL, INDEX IDX_1F983A2E71F7E88B (event_id), UNIQUE INDEX UNIQ_1F983A2EBBE43214 (prize_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE park_event_prize ADD CONSTRAINT FK_1F983A2E71F7E88B FOREIGN KEY (event_id) REFERENCES park_event (id)');
        $this->addSql('ALTER TABLE park_event_prize ADD CONSTRAINT FK_1F983A2EBBE43214 FOREIGN KEY (prize_id) REFERENCES inventory (id)');
        $this->addSql('ALTER TABLE park_event ADD results LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE park_event_prize');
        $this->addSql('ALTER TABLE park_event DROP results');
    }
}
