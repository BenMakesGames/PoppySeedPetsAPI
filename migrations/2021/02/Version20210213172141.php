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
                IF(e.name IS NULL OR e.is_suffix=1, \'\', CONCAT(e.name, \' \')),
                IF(s.name IS NULL OR s.is_suffix=1, \'\', CONCAT(s.name, \' \')),
                item.name,
                IF(e.name IS NULL OR e.is_suffix=0, \'\', CONCAT(e.name, \' \')),
                IF(s.name IS NULL OR s.is_suffix=0, \'\', CONCAT(s.name, \' \'))
            )
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inventory DROP full_item_name');
    }
}
