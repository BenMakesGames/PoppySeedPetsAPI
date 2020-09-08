<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Enum\RelationshipEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200613174517 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet_relationship ADD energy INT NOT NULL');

        $this->addSql('UPDATE pet_relationship SET energy=energy+100 WHERE relationship_goal=\'' . RelationshipEnum::MATE . '\'');
        $this->addSql('UPDATE pet_relationship SET energy=energy+80 WHERE relationship_goal=\'' . RelationshipEnum::FWB . '\'');
        $this->addSql('UPDATE pet_relationship SET energy=energy+70 WHERE relationship_goal=\'' . RelationshipEnum::BFF . '\'');
        $this->addSql('UPDATE pet_relationship SET energy=energy+50 WHERE relationship_goal=\'' . RelationshipEnum::FRIEND . '\'');
        $this->addSql('UPDATE pet_relationship SET energy=energy+20 WHERE relationship_goal=\'' . RelationshipEnum::FRIENDLY_RIVAL . '\'');

        $this->addSql('UPDATE pet_relationship SET energy=energy+30 WHERE current_relationship=\'' . RelationshipEnum::MATE . '\'');
        $this->addSql('UPDATE pet_relationship SET energy=energy+20 WHERE current_relationship=\'' . RelationshipEnum::FWB . '\'');
        $this->addSql('UPDATE pet_relationship SET energy=energy+18 WHERE current_relationship=\'' . RelationshipEnum::BFF . '\'');
        $this->addSql('UPDATE pet_relationship SET energy=energy+12 WHERE current_relationship=\'' . RelationshipEnum::FRIEND . '\'');
        $this->addSql('UPDATE pet_relationship SET energy=energy+5 WHERE current_relationship=\'' . RelationshipEnum::FRIENDLY_RIVAL . '\'');

        $this->addSql('UPDATE pet_relationship SET energy=energy+FLOOR(RAND() * 30)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet_relationship DROP energy');
    }
}
