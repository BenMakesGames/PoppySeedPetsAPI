<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210124190336 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE daily_stats (id INT AUTO_INCREMENT NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', number_of_players1_day INT NOT NULL, number_of_players3_day INT NOT NULL, number_of_players7_day INT NOT NULL, number_of_players28_day INT NOT NULL, number_of_players_lifetime INT NOT NULL, total_moneys1_day INT NOT NULL, total_moneys3_day INT NOT NULL, total_moneys7_day INT NOT NULL, total_moneys28_day INT NOT NULL, total_moneys_lifetime INT NOT NULL, new_players1_day INT NOT NULL, new_players3_day INT NOT NULL, new_players7_day INT NOT NULL, new_players28_day INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE daily_stats');
    }
}
