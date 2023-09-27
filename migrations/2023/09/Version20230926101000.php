<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230926101000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE status_effect SET status=\'Focused (Arcana)\' WHERE status=\'Focused (Umbra)\'');
        $this->addSql('UPDATE item SET name=\'Skill Scroll: Arcana\' WHERE name=\'Skill Scroll: Umbra\'');
        $this->addSql('UPDATE item SET name=\'Potion of Arcana\' WHERE name=\'Potion of Umbra\'');
    }

    public function down(Schema $schema): void
    {
    }
}
