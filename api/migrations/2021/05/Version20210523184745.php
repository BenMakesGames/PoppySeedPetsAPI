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
final class Version20210523184745 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pet_craving (id INT AUTO_INCREMENT NOT NULL, pet_id INT NOT NULL, food_group_id INT NOT NULL, created_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', satisfied_on DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_FA3E46EA966F7FB6 (pet_id), INDEX IDX_FA3E46EAD619FE05 (food_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pet_craving ADD CONSTRAINT FK_FA3E46EA966F7FB6 FOREIGN KEY (pet_id) REFERENCES pet (id)');
        $this->addSql('ALTER TABLE pet_craving ADD CONSTRAINT FK_FA3E46EAD619FE05 FOREIGN KEY (food_group_id) REFERENCES item_group (id)');
        $this->addSql('ALTER TABLE item_group ADD is_craving TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE pet_craving');
        $this->addSql('ALTER TABLE item_group DROP is_craving');
    }
}
