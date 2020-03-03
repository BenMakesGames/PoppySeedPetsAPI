<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200303012715 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE totem_pole ADD height_in_kilometers INT NOT NULL, ADD reward_extra LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', DROP reward10m, DROP reward50m, DROP reward9000m');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE totem_pole ADD reward10m TINYINT(1) NOT NULL, ADD reward50m TINYINT(1) NOT NULL, ADD reward9000m TINYINT(1) NOT NULL, DROP height_in_kilometers, DROP reward_extra');
    }
}
