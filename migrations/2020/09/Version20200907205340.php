<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200907205340 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pet_activity_stats CHANGE protocol7success protocol7_success INT NOT NULL');
        $this->addSql('ALTER TABLE pet_activity_stats CHANGE protocol7failure protocol7_failure INT NOT NULL');
        $this->addSql('ALTER TABLE pet_activity_stats CHANGE protocol7time protocol7_time INT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pet_activity_stats CHANGE protocol7_success protocol7success INT NOT NULL');
        $this->addSql('ALTER TABLE pet_activity_stats CHANGE protocol7_failure protocol7failure INT NOT NULL');
        $this->addSql('ALTER TABLE pet_activity_stats CHANGE protocol7_time protocol7time INT NOT NULL');
    }
}
