<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191021174001 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE item_hat (id INT AUTO_INCREMENT NOT NULL, head_x DOUBLE PRECISION NOT NULL, head_y DOUBLE PRECISION NOT NULL, head_angle DOUBLE PRECISION NOT NULL, head_scale DOUBLE PRECISION NOT NULL, head_angle_fixed TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE item ADD hat_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E8C6A5980 FOREIGN KEY (hat_id) REFERENCES item_hat (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1F1B251E8C6A5980 ON item (hat_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E8C6A5980');
        $this->addSql('DROP TABLE item_hat');
        $this->addSql('DROP INDEX UNIQ_1F1B251E8C6A5980 ON item');
        $this->addSql('ALTER TABLE item DROP hat_id');
    }
}
