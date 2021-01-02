<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210101215924 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD max_market_bids SMALLINT NOT NULL');
        $this->addSql('UPDATE user SET max_market_bids=5');
        $this->addSql('UPDATE user SET max_market_bids=max_market_bids+10 WHERE id IN (SELECT user_id FROM user_stats WHERE stat=\'Items Donated to Museum\' AND value>=400)');
        $this->addSql('UPDATE user SET max_market_bids=max_market_bids+5 WHERE id IN (SELECT user_id FROM user_stats WHERE stat=\'Items Sold in Market\' AND value>=50)');
        $this->addSql('UPDATE user SET max_market_bids=max_market_bids+5 WHERE id IN (SELECT user_id FROM user_stats WHERE stat=\'Items Bought in Market\' AND value>=50)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP max_market_bids');
    }
}
