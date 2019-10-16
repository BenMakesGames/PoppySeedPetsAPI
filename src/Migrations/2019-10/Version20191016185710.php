<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191016185710 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet_species ADD pregnancy_style INT NOT NULL, ADD egg_image VARCHAR(255) DEFAULT NULL');

        $this->addSql('UPDATE pet_species SET pregnancy_style=1 WHERE id NOT IN (1, 3, 4, 6, 8, 12, 14, 17, 18)');
        $this->addSql('UPDATE pet_species SET egg_image="spotted" WHERE id IN (1, 8, 12, 17)');
        $this->addSql('UPDATE pet_species SET egg_image="speckled" WHERE id IN (3, 4, 18)');
        $this->addSql('UPDATE pet_species SET egg_image="striped" WHERE id IN (6, 14)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet_species DROP pregnancy_style, DROP egg_image');
    }
}
