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
final class Version20240630195839 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE monster_of_the_week (id INT AUTO_INCREMENT NOT NULL, monster VARCHAR(100) NOT NULL, start_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', end_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', community_total INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE monster_of_the_week_contribution (id INT AUTO_INCREMENT NOT NULL, monster_of_the_week_id INT NOT NULL, user_id INT NOT NULL, points INT NOT NULL, modified_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', rewards_claimed SMALLINT NOT NULL, INDEX IDX_D09DEF0664BCBCDB (monster_of_the_week_id), INDEX IDX_D09DEF06A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE monster_of_the_week_contribution ADD CONSTRAINT FK_D09DEF0664BCBCDB FOREIGN KEY (monster_of_the_week_id) REFERENCES monster_of_the_week (id)');
        $this->addSql('ALTER TABLE monster_of_the_week_contribution ADD CONSTRAINT FK_D09DEF06A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE monster_of_the_week_contribution DROP FOREIGN KEY FK_D09DEF0664BCBCDB');
        $this->addSql('ALTER TABLE monster_of_the_week_contribution DROP FOREIGN KEY FK_D09DEF06A76ED395');
        $this->addSql('DROP TABLE monster_of_the_week');
        $this->addSql('DROP TABLE monster_of_the_week_contribution');
    }
}
