<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191021172315 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet_species ADD hat_x DOUBLE PRECISION NOT NULL, ADD hat_y DOUBLE PRECISION NOT NULL, ADD hat_angle DOUBLE PRECISION NOT NULL, CHANGE hand_flip_x flip_x TINYINT(1) NOT NULL');

        $this->addSql('INSERT INTO merit (name, description) VALUES ("Behatted", "%pet.name% will be able to wear hats!")');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet_species DROP hat_x, DROP hat_y, DROP hat_angle, CHANGE flip_x hand_flip_x TINYINT(1) NOT NULL');
    }
}
