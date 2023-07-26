<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230725230000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE `pet_activity_log_tag` SET `title` = \'Dark\' WHERE `title`=\'Light Needed\';');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE `pet_activity_log_tag` SET `title` = \'Light Needed\' WHERE `title`=\'Dark\';');
    }
}
