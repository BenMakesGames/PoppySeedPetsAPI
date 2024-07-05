<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240705020420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `monster_of_the_week_contribution` CHANGE `rewards_claimed` `rewards_claimed` TINYINT(1) NOT NULL;');
    }

    public function down(Schema $schema): void
    {
    }
}
