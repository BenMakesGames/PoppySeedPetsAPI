<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200208161549 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet ADD love_language VARCHAR(10) NOT NULL');

        $this->addSql('UPDATE pet SET love_language=\'gifts\' WHERE MOD(id, 5) = 0');
        $this->addSql('UPDATE pet SET love_language=\'time\' WHERE MOD(id, 5) = 0');
        $this->addSql('UPDATE pet SET love_language=\'words\' WHERE MOD(id, 5) = 0');
        $this->addSql('UPDATE pet SET love_language=\'acts\' WHERE MOD(id, 5) = 0');
        $this->addSql('UPDATE pet SET love_language=\'touch\' WHERE MOD(id, 5) = 0');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet DROP love_language');
    }
}
