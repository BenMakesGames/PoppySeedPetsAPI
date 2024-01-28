<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240122816150 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO `merit` (`id`, `name`, `description`) VALUES (NULL, 'Caching', '%pet.name% has secret caches of food hidden around the island, and may dig them up when hungry!') ON DUPLICATE KEY UPDATE `id` = `id`;");
    }

    public function down(Schema $schema): void
    {
    }
}
