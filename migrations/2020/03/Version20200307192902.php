<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200307192902 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE guild_membership (id INT AUTO_INCREMENT NOT NULL, pet_id INT NOT NULL, guild_id INT NOT NULL, joined_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', reputation INT NOT NULL, level INT NOT NULL, UNIQUE INDEX UNIQ_E7D8D2A966F7FB6 (pet_id), INDEX IDX_E7D8D2A5F2131EF (guild_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE guild (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE guild_membership ADD CONSTRAINT FK_E7D8D2A966F7FB6 FOREIGN KEY (pet_id) REFERENCES pet (id)');
        $this->addSql('ALTER TABLE guild_membership ADD CONSTRAINT FK_E7D8D2A5F2131EF FOREIGN KEY (guild_id) REFERENCES guild (id)');

        $this->addSql('
            INSERT INTO `guild` (`id`, `name`) VALUES
            (1, \'Time\\\'s Arrow\'),
            (2, \'Light and Shadow\'),
            (3, \'Tapestries\'),
            (4, \'Inner Sanctum\'),
            (5, \'Dwarfcraft\'),
            (6, \'Gizubi\\\'s Garden\'),
            (7, \'High Impact\'),
            (8, \'The Universe Forgets\'),
            (9, \'Correspondence\');
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE guild_membership DROP FOREIGN KEY FK_E7D8D2A5F2131EF');
        $this->addSql('DROP TABLE guild_membership');
        $this->addSql('DROP TABLE guild');
    }
}
