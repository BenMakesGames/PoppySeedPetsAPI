<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260308032638 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD basement_size INT NOT NULL');
        $this->addSql('UPDATE user SET basement_size = 10000');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP basement_size');
    }
}
