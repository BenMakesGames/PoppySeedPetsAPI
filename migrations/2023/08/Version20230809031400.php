<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230809031400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // update anniversary letter to refer to HERG, instead of PEaRL
        $this->addSql(<<<EOSQL
UPDATE `letter` SET `body`='Can you believe it\'s already been a year? (And what a year it\'s been!) I had hoped to visit everyone individually for their one-year anniversaries, but the number of people who arrived just within the first year was beyond our expectations and wildest dreams! It would take me a year just to say "hello" to all of you!\r\n\r\nThis town started as a research outpost, back in 2004. When we started, there were only eight people, but over the course of seven years, thanks to volunteers such as yourself, we grew to the thousands! Unfortunately, we later lost our head researchers, and with them, our funding, forcing us to close down most of our operations for a number of years. But a fortunate turn of events, and new sponsorship, has allowed us to resume operations, and we\'ve been delighted with the response!\r\n\r\nI know that I\'m new myself, having only joined in 2019, but allow me to welcome and thank you on behalf of everyone at Hollow Earth Research Group. Your enthusiasm and positivity motivates us every day! With your help, we know we can uncover the secrets of this island, its portal, and the strange (and cute) creatures that live there!\r\n\r\nThanks again for being a part of HERG!\r\n\r\nPlease enjoy this One-year Anniversary Gift. (And don\'t worry: I had one donated to the Museum on your behalf!)' WHERE  `id`=8;
EOSQL);

        // combine Praised a Pet into Petted a Pet
        $this->addSql('
            UPDATE user_stats AS t1
            JOIN (SELECT user_id,value FROM user_stats WHERE stat="Praised a Pet") AS t2 ON t1.user_id=t2.user_id
            SET t1.value = t1.value + t2.value
            WHERE t1.stat = "Petted a Pet"
        ');
        $this->addSql('DELETE FROM user_stats WHERE stat="Praised a Pet"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_badge DROP FOREIGN KEY FK_1C32B345A76ED395');
        $this->addSql('DROP TABLE user_badge');
    }
}
