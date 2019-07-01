<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190701172403 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE item DROP color_a_range, DROP color_b_range');
        $this->addSql('ALTER TABLE inventory DROP color_a, DROP color_b');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE inventory ADD color_a VARCHAR(6) NOT NULL COLLATE utf8mb4_unicode_ci, ADD color_b VARCHAR(6) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE item ADD color_a_range VARCHAR(40) NOT NULL COLLATE utf8mb4_unicode_ci, ADD color_b_range VARCHAR(40) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
