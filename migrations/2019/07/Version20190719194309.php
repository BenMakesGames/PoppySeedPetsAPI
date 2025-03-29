<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Item;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190719194309 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE item ADD food_id INT DEFAULT NULL, CHANGE food old_food LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\'');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251EBA8E87C4 FOREIGN KEY (food_id) REFERENCES item_food (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1F1B251EBA8E87C4 ON item (food_id)');
    }

    public function postUp(Schema $schema): void
    {
        parent::postUp($schema);

        $foodItems = $this->connection->fetchAllAssociative('SELECT * FROM item WHERE item.old_food!=:phpNull', [ 'phpNull' => 'N;' ]);

        foreach($foodItems as $foodItem)
        {
            $food = unserialize($foodItem['old_food']);

            $this->connection->insert(
                'item_food',
                [
                    'food' => $food->food,
                    'love' => $food->love,
                    'junk' => $food->junk,
                    'whack' => $food->whack,
                    'earthy' => 0,
                    'fruity' => 0,
                    'tannic' => 0,
                    'spicy' => 0,
                    'creamy' => 0,
                    'meaty' => 0,
                    'planty' => 0,
                    'fishy' => 0,
                    'floral' => 0,
                    'fatty' => 0,
                    'oniony' => 0,
                    'chemicaly' => 0,
                ]
            );

            $newRowId = $this->connection->lastInsertId();

            $this->connection->executeQuery('UPDATE item SET food_id=:foodId WHERE id=:itemId LIMIT 1', [
                'foodId' => $newRowId,
                'itemId' => $foodItem['id']
            ]);
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251EBA8E87C4');
        $this->addSql('DROP INDEX UNIQ_1F1B251EBA8E87C4 ON item');
        $this->addSql('ALTER TABLE item DROP food_id, CHANGE old_food food LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:object)\'');
    }
}
