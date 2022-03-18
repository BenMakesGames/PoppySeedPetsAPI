<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220317231523 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE greenhouse ADD butterflies_dismissed_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD bees_dismissed_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP can_use_bee_netting, DROP has_bee_netting');
        $this->addSql('ALTER TABLE greenhouse_plant ADD pollinators VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE greenhouse ADD can_use_bee_netting TINYINT(1) NOT NULL, ADD has_bee_netting TINYINT(1) NOT NULL, DROP butterflies_dismissed_on, DROP bees_dismissed_on');
        $this->addSql('ALTER TABLE greenhouse_plant DROP pollinators');
    }
}
