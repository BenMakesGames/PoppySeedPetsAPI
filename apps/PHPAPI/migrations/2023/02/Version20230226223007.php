<?php

declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230226223007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE market_listing (id INT AUTO_INCREMENT NOT NULL, item_id INT NOT NULL, enchantment_id INT DEFAULT NULL, spice_id INT DEFAULT NULL, minimum_sell_price INT DEFAULT NULL, full_item_name VARCHAR(100) NOT NULL, INDEX IDX_C296A054126F525E (item_id), INDEX IDX_C296A054F3927CF3 (enchantment_id), INDEX IDX_C296A054CF04D12D (spice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE market_listing ADD CONSTRAINT FK_C296A054126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE market_listing ADD CONSTRAINT FK_C296A054F3927CF3 FOREIGN KEY (enchantment_id) REFERENCES enchantment (id)');
        $this->addSql('ALTER TABLE market_listing ADD CONSTRAINT FK_C296A054CF04D12D FOREIGN KEY (spice_id) REFERENCES spice (id)');

        $this->addSql('
            INSERT INTO `market_listing` (item_id, enchantment_id, spice_id, full_item_name, minimum_sell_price)
            SELECT inventory.item_id,inventory.enchantment_id,inventory.spice_id,inventory.full_item_name,MIN(sell_price) AS minimum_sell_price
            FROM inventory WHERE inventory.sell_price IS NOT NULL
            GROUP BY inventory.item_id,inventory.enchantment_id,inventory.spice_id,inventory.full_item_name
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_listing DROP FOREIGN KEY FK_C296A054126F525E');
        $this->addSql('ALTER TABLE market_listing DROP FOREIGN KEY FK_C296A054F3927CF3');
        $this->addSql('ALTER TABLE market_listing DROP FOREIGN KEY FK_C296A054CF04D12D');
        $this->addSql('DROP TABLE market_listing');
    }
}
