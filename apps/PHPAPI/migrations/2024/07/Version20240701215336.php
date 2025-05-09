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
final class Version20240701215336 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE monster_of_the_week ADD easy_prize_id INT NOT NULL, ADD medium_prize_id INT NOT NULL, ADD hard_prize_id INT NOT NULL');
        $this->addSql('ALTER TABLE monster_of_the_week ADD CONSTRAINT FK_F97420B7B1EEF6D2 FOREIGN KEY (easy_prize_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE monster_of_the_week ADD CONSTRAINT FK_F97420B7C26D21B6 FOREIGN KEY (medium_prize_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE monster_of_the_week ADD CONSTRAINT FK_F97420B7E51C4334 FOREIGN KEY (hard_prize_id) REFERENCES item (id)');
        $this->addSql('CREATE INDEX IDX_F97420B7B1EEF6D2 ON monster_of_the_week (easy_prize_id)');
        $this->addSql('CREATE INDEX IDX_F97420B7C26D21B6 ON monster_of_the_week (medium_prize_id)');
        $this->addSql('CREATE INDEX IDX_F97420B7E51C4334 ON monster_of_the_week (hard_prize_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE monster_of_the_week DROP FOREIGN KEY FK_F97420B7B1EEF6D2');
        $this->addSql('ALTER TABLE monster_of_the_week DROP FOREIGN KEY FK_F97420B7C26D21B6');
        $this->addSql('ALTER TABLE monster_of_the_week DROP FOREIGN KEY FK_F97420B7E51C4334');
        $this->addSql('DROP INDEX IDX_F97420B7B1EEF6D2 ON monster_of_the_week');
        $this->addSql('DROP INDEX IDX_F97420B7C26D21B6 ON monster_of_the_week');
        $this->addSql('DROP INDEX IDX_F97420B7E51C4334 ON monster_of_the_week');
        $this->addSql('ALTER TABLE monster_of_the_week DROP easy_prize_id, DROP medium_prize_id, DROP hard_prize_id');
    }
}
