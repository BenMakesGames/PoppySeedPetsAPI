<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220904191423 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE monthly_story_adventure_step DROP FOREIGN KEY FK_2D91EBCA72CA3BB6');
        $this->addSql('ALTER TABLE monthly_story_adventure_step ADD CONSTRAINT FK_2D91EBCA72CA3BB6 FOREIGN KEY (aura_id) REFERENCES enchantment (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE monthly_story_adventure_step DROP FOREIGN KEY FK_2D91EBCA72CA3BB6');
        $this->addSql('ALTER TABLE monthly_story_adventure_step ADD CONSTRAINT FK_2D91EBCA72CA3BB6 FOREIGN KEY (aura_id) REFERENCES aura (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
