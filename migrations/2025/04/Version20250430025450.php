<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250430025450 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // plastic shovel - replace +brawl with +mining
        $this->addSql(<<<EOSQL
        UPDATE `item_tool` SET `brawl` = '0', `mining` = '1' WHERE `item_tool`.`id` = 95; 
        EOSQL);

        // invisible shovel - replace +brawl with +mining
        $this->addSql(<<<EOSQL
        UPDATE `item_tool` SET `brawl` = '0', `mining` = '1' WHERE `item_tool`.`id` = 119; 
        EOSQL);

        // gizubi's shovel - replace +fishing with +mining
        $this->addSql(<<<EOSQL
        UPDATE `item_tool` SET `fishing` = '0', `mining` = '1' WHERE `item_tool`.`id` = 69; 
        EOSQL);

        // heavy hammer - even worse for climbing; +2 mining
        $this->addSql(<<<EOSQL
        UPDATE `item_tool` SET `climbing` = '-4', `mining` = '2' WHERE `item_tool`.`id` = 34; 
        EOSQL);

        // crazy-hot torch & tig's memory - replace +arcana with +umbra
        $this->addSql(<<<EOSQL
        UPDATE `item_tool` SET `arcana` = '0', `umbra` = '2' WHERE `item_tool`.`id` IN (24, 77); 
        EOSQL);

        // brute force - replace +science with +hacking
        $this->addSql(<<<EOSQL
        UPDATE `item_tool` SET `science` = '0', `hacking` = '2' WHERE `item_tool`.`id` = 140; 
        EOSQL);

        // compiler & debugger - replace +science & +smithing with +hacking & +electronics
        $this->addSql(<<<EOSQL
        UPDATE `item_tool` SET `science` = 0, `smithing` = 0, `hacking` = 2, `electronics` = 2 WHERE `item_tool`.`id` IN (18, 164); 
        EOSQL);

        // electrical engineering handbook - move +science -> +electronics & +physics
        $this->addSql(<<<EOSQL
        UPDATE `item_tool` SET `science` = 0, `electronics` = 2, `physics` = 1 WHERE `item_tool`.`id` = 137; 
        EOSQL);

        // l33t h4xx0r - replace +science with +hacking
        $this->addSql(<<<EOSQL
        UPDATE `item_tool` SET `science` = '0', `hacking` = 5 WHERE `item_tool`.`id` = 54; 
        EOSQL);

        // phishing rod & regex - replace +2 science with +2 hacking
        $this->addSql(<<<EOSQL
        UPDATE `item_tool` SET `science` = '0', `hacking` = 2 WHERE `item_tool`.`id` IN (54, 17); 
        EOSQL);

        // high tide - replace +science with +physics
        $this->addSql(<<<EOSQL
        UPDATE `item_tool` SET `science` = 0, `physics` = '4' WHERE `item_tool`.`id` = 375; 
        EOSQL);

        // wood's metal - replace nature & science with electronics
        $this->addSql(<<<EOSQL
        UPDATE `item_tool` SET `nature` = 0, `science` = '0', `electronics` = '2' WHERE `item_tool`.`id` = 264; 
        EOSQL);

        // astral tuning fork - replace crafts with science
        $this->addSql(<<<EOSQL
        UPDATE `item_tool` SET `crafts` = '0', `science` = 2 WHERE `item_tool`.`id` = 75; 
        EOSQL);

        // enchanted compass - replace arcana with umbra
        $this->addSql(<<<EOSQL
        UPDATE `item_tool` SET `arcana` = '0', `umbra` = '2' WHERE `item_tool`.`id` = 143; 
        EOSQL);

        // gold compass - replace some arcana with umbra
        $this->addSql(<<<EOSQL
        UPDATE `item_tool` SET `arcana` = '2', `umbra` = '3' WHERE `item_tool`.`id` = 144; 
        EOSQL);

        // strawberry-covered nanner - replace arcana with magic-binding
        $this->addSql(<<<EOSQL
        UPDATE `item_tool` SET `arcana` = '0', `magic_binding` = '2' WHERE `item_tool`.`id` = 492; 
        EOSQL);

        // mjolnir - replace arcana with magic-binding; replace science with mining; added electronics penalty
        $this->addSql(<<<EOSQL
        UPDATE `item_tool` SET `arcana` = '0', `magic_binding` = '3', `science` = '0', `mining` = '3' WHERE `item_tool`.`id` = 341; 
        EOSQL);

        // wunderbuss - replace arcana with magic-binding; replace science with physics
        $this->addSql(<<<EOSQL
        UPDATE `item_tool` SET `arcana` = 0, `magic_binding` = '4', `science` = 0, `physics` = '4' WHERE `item_tool`.`id` = 299; 
        EOSQL);

        // rainbowsaber - replace science with physics
        $this->addSql(<<<EOSQL
        UPDATE `item_tool` SET `science` = '0', `physics` = '2' WHERE `item_tool`.`id` = 372; 
        EOSQL);

        // glitched-out rainbowsaber - replace science with hacking and physics
        $this->addSql(<<<EOSQL
        UPDATE `item_tool` SET `science` = '0', `physics` = '2', `hacking` = '5' WHERE `item_tool`.`id` = 427; 
        EOSQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
