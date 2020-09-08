<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191005182858 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE hollow_earth_tile (id INT AUTO_INCREMENT NOT NULL, zone_id INT NOT NULL, x INT NOT NULL, y INT NOT NULL, event JSON NOT NULL, required_action INT NOT NULL, move_direction VARCHAR(1) NOT NULL, INDEX IDX_5BEE24529F2C3FAB (zone_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hollow_earth_zone (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(40) NOT NULL, image VARCHAR(40) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE hollow_earth_tile ADD CONSTRAINT FK_5BEE24529F2C3FAB FOREIGN KEY (zone_id) REFERENCES hollow_earth_zone (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE hollow_earth_tile DROP FOREIGN KEY FK_5BEE24529F2C3FAB');
        $this->addSql('DROP TABLE hollow_earth_tile');
        $this->addSql('DROP TABLE hollow_earth_zone');
    }
}
