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
final class Version20211128182138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE beehive ADD helper_id INT DEFAULT NULL, DROP specialization');
        $this->addSql('ALTER TABLE beehive ADD CONSTRAINT FK_75878082D7693E95 FOREIGN KEY (helper_id) REFERENCES pet (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_75878082D7693E95 ON beehive (helper_id)');
        $this->addSql('ALTER TABLE pet ADD location VARCHAR(20) NOT NULL');

        $this->addSql('UPDATE pet SET location=\'daycare\' WHERE in_daycare=1');
        $this->addSql('UPDATE pet SET location=\'home\' WHERE in_daycare=0');

        $this->addSql('DROP INDEX in_daycare_idx ON pet');
        $this->addSql('ALTER TABLE pet DROP in_daycare');
        $this->addSql('CREATE INDEX location_idx ON pet (location)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE beehive DROP FOREIGN KEY FK_75878082D7693E95');
        $this->addSql('DROP INDEX UNIQ_75878082D7693E95 ON beehive');
        $this->addSql('ALTER TABLE beehive ADD specialization VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP helper_id');
        $this->addSql('DROP INDEX location_idx ON pet');
        $this->addSql('ALTER TABLE pet ADD in_daycare TINYINT(1) NOT NULL, DROP location');
        $this->addSql('CREATE INDEX in_daycare_idx ON pet (in_daycare)');
    }
}
