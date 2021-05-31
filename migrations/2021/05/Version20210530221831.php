<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210530221831 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE daily_stats ADD unlocked_trader1_day INT DEFAULT NULL, ADD unlocked_trader3_day INT DEFAULT NULL, ADD unlocked_trader7_day INT DEFAULT NULL, ADD unlocked_trader28_day INT DEFAULT NULL, ADD unlocked_trader_lifetime INT DEFAULT NULL, ADD unlocked_fireplace1_day INT DEFAULT NULL, ADD unlocked_fireplace3_day INT DEFAULT NULL, ADD unlocked_fireplace7_day INT DEFAULT NULL, ADD unlocked_fireplace28_day INT DEFAULT NULL, ADD unlocked_fireplace_lifetime INT DEFAULT NULL, ADD unlocked_greenhouse1_day INT DEFAULT NULL, ADD unlocked_greenhouse3_day INT DEFAULT NULL, ADD unlocked_greenhouse7_day INT DEFAULT NULL, ADD unlocked_greenhouse28_day INT DEFAULT NULL, ADD unlocked_greenhouse_lifetime INT DEFAULT NULL, ADD unlocked_beehive1_day INT DEFAULT NULL, ADD unlocked_beehive3_day INT DEFAULT NULL, ADD unlocked_beehive7_day INT DEFAULT NULL, ADD unlocked_beehive28_day INT DEFAULT NULL, ADD unlocked_beehive_lifetime INT DEFAULT NULL, ADD unlocked_portal1_day INT DEFAULT NULL, ADD unlocked_portal3_day INT DEFAULT NULL, ADD unlocked_portal7_day INT DEFAULT NULL, ADD unlocked_portal28_day INT DEFAULT NULL, ADD unlocked_portal_lifetime INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE daily_stats DROP unlocked_trader1_day, DROP unlocked_trader3_day, DROP unlocked_trader7_day, DROP unlocked_trader28_day, DROP unlocked_trader_lifetime, DROP unlocked_fireplace1_day, DROP unlocked_fireplace3_day, DROP unlocked_fireplace7_day, DROP unlocked_fireplace28_day, DROP unlocked_fireplace_lifetime, DROP unlocked_greenhouse1_day, DROP unlocked_greenhouse3_day, DROP unlocked_greenhouse7_day, DROP unlocked_greenhouse28_day, DROP unlocked_greenhouse_lifetime, DROP unlocked_beehive1_day, DROP unlocked_beehive3_day, DROP unlocked_beehive7_day, DROP unlocked_beehive28_day, DROP unlocked_beehive_lifetime, DROP unlocked_portal1_day, DROP unlocked_portal3_day, DROP unlocked_portal7_day, DROP unlocked_portal28_day, DROP unlocked_portal_lifetime');
    }
}
