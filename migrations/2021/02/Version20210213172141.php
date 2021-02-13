<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210213172141 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inventory ADD full_item_name VARCHAR(100) NOT NULL');

        $this->addSql('
            UPDATE inventory AS i
            LEFT JOIN item AS item ON item.id=i.item_id
            LEFT JOIN enchantment AS e ON i.enchantment_id=e.id
            LEFT JOIN spice AS s ON i.spice_id=s.id
            
            SET i.full_item_name=CONCAT(
                IF(e.name IS NULL OR e.is_suffix=1, '', CONCAT(e.name, ' ')),
                IF(s.name IS NULL OR s.is_suffix=1, '', CONCAT(s.name, ' ')),
                item.name,
                IF(e.name IS NULL OR e.is_suffix=0, '', CONCAT(e.name, ' ')),
                IF(s.name IS NULL OR s.is_suffix=0, '', CONCAT(s.name, ' '))
            )
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inventory DROP full_item_name');
    }
}
