<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210629184141 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item_group ADD is_gift_shop TINYINT(1) NOT NULL');

        // --- wow! it's like a real migration! -------------------------------

        // adding "Skill Scroll" item group, and items
        $this->addSql('INSERT INTO `item_group` (`id`, `name`, `is_craving`, `is_gift_shop`) VALUES (NULL, \'Skill Scroll\', 0, 1)');
        $this->addSql('
            INSERT INTO item_group_item (item_group_id, item_id)
            SELECT item_group.id AS item_group_id, item.id AS item_id
            FROM item
            LEFT JOIN item_group ON item_group.name="Skill Scroll"
            WHERE item.name LIKE "Skill Scroll: %"
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item_group DROP is_gift_shop');
    }
}
