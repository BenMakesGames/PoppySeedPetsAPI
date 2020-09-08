<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191016203852 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet ADD mom_id INT DEFAULT NULL, ADD dad_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pet ADD CONSTRAINT FK_E4529B854E866E47 FOREIGN KEY (mom_id) REFERENCES pet (id)');
        $this->addSql('ALTER TABLE pet ADD CONSTRAINT FK_E4529B85ABB1CE64 FOREIGN KEY (dad_id) REFERENCES pet (id)');
        $this->addSql('CREATE INDEX IDX_E4529B854E866E47 ON pet (mom_id)');
        $this->addSql('CREATE INDEX IDX_E4529B85ABB1CE64 ON pet (dad_id)');
        $this->addSql('ALTER TABLE pet_baby ADD color_a VARCHAR(6) NOT NULL, ADD color_b VARCHAR(6) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet DROP FOREIGN KEY FK_E4529B854E866E47');
        $this->addSql('ALTER TABLE pet DROP FOREIGN KEY FK_E4529B85ABB1CE64');
        $this->addSql('DROP INDEX IDX_E4529B854E866E47 ON pet');
        $this->addSql('DROP INDEX IDX_E4529B85ABB1CE64 ON pet');
        $this->addSql('ALTER TABLE pet DROP mom_id, DROP dad_id');
        $this->addSql('ALTER TABLE pet_baby DROP color_a, DROP color_b');
    }
}
