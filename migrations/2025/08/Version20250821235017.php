<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250821235017 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX pet_badge_unique ON pet_badge (pet_id, badge)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX pet_badge_unique ON pet_badge');
    }
}
