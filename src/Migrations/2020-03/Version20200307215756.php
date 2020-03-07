<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200307215756 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE guild ADD emblem VARCHAR(40) NOT NULL');

        $this->addSql('UPDATE `guild` SET `emblem` = \'times-arrow\' WHERE `guild`.`id` = 1;');
        $this->addSql('UPDATE `guild` SET `emblem` = \'light-and-shadow\' WHERE `guild`.`id` = 2;');
        $this->addSql('UPDATE `guild` SET `emblem` = \'tapestries\' WHERE `guild`.`id` = 3;');
        $this->addSql('UPDATE `guild` SET `emblem` = \'inner-sanctum\' WHERE `guild`.`id` = 4;');
        $this->addSql('UPDATE `guild` SET `emblem` = \'dwarfcraft\' WHERE `guild`.`id` = 5;');
        $this->addSql('UPDATE `guild` SET `emblem` = \'gizubis-garden\' WHERE `guild`.`id` = 6;');
        $this->addSql('UPDATE `guild` SET `emblem` = \'high-impact\' WHERE `guild`.`id` = 7;');
        $this->addSql('UPDATE `guild` SET `emblem` = \'the-universe-forgets\' WHERE `guild`.`id` = 8;');
        $this->addSql('UPDATE `guild` SET `emblem` = \'correspondence\' WHERE `guild`.`id` = 9;');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE guild DROP emblem');
    }
}
