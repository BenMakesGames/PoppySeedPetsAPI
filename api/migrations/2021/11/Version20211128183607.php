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
final class Version20211128183607 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dragon ADD helper_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dragon ADD CONSTRAINT FK_27D829B4D7693E95 FOREIGN KEY (helper_id) REFERENCES pet (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_27D829B4D7693E95 ON dragon (helper_id)');
        $this->addSql('ALTER TABLE fireplace ADD helper_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fireplace ADD CONSTRAINT FK_9725AC2BD7693E95 FOREIGN KEY (helper_id) REFERENCES pet (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9725AC2BD7693E95 ON fireplace (helper_id)');
        $this->addSql('ALTER TABLE greenhouse ADD helper_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE greenhouse ADD CONSTRAINT FK_DC68F11BD7693E95 FOREIGN KEY (helper_id) REFERENCES pet (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DC68F11BD7693E95 ON greenhouse (helper_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dragon DROP FOREIGN KEY FK_27D829B4D7693E95');
        $this->addSql('DROP INDEX UNIQ_27D829B4D7693E95 ON dragon');
        $this->addSql('ALTER TABLE dragon DROP helper_id');
        $this->addSql('ALTER TABLE fireplace DROP FOREIGN KEY FK_9725AC2BD7693E95');
        $this->addSql('DROP INDEX UNIQ_9725AC2BD7693E95 ON fireplace');
        $this->addSql('ALTER TABLE fireplace DROP helper_id');
        $this->addSql('ALTER TABLE greenhouse DROP FOREIGN KEY FK_DC68F11BD7693E95');
        $this->addSql('DROP INDEX UNIQ_DC68F11BD7693E95 ON greenhouse');
        $this->addSql('ALTER TABLE greenhouse DROP helper_id');
    }
}
