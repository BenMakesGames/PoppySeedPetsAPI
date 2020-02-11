<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200210225132 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE greenhouse (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, max_plants INT NOT NULL, bird_feeder_levels INT NOT NULL, UNIQUE INDEX UNIQ_DC68F11B7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE greenhouse ADD CONSTRAINT FK_DC68F11B7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');

        $this->addSql('
            INSERT INTO greenhouse (owner_id, max_plants, bird_feeder_levels)
            SELECT u.id AS owner_id,u.max_plants, 0 as bird_feeder_levels FROM user AS u WHERE u.unlocked_greenhouse IS NOT NULL
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE greenhouse');
    }
}
