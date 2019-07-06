<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190706180916 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('
            INSERT INTO `user_stats` (user_id, stat, `value`, first_time, last_time) 
            SELECT mi.user_id,\'Items Donated to Museum\',COUNT(mi.id),MIN(mi.donated_on),MAX(mi.donated_on) FROM museum_item AS mi GROUP BY mi.user_id
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
