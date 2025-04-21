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
final class Version20240505190744 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pet_activity_log_item (id INT AUTO_INCREMENT NOT NULL, log_id INT NOT NULL, item_id INT NOT NULL, INDEX IDX_F9203150EA675D86 (log_id), INDEX IDX_F9203150126F525E (item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pet_activity_log_item ADD CONSTRAINT FK_F9203150EA675D86 FOREIGN KEY (log_id) REFERENCES pet_activity_log (id)');
        $this->addSql('ALTER TABLE pet_activity_log_item ADD CONSTRAINT FK_F9203150126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pet_activity_log_item DROP FOREIGN KEY FK_F9203150EA675D86');
        $this->addSql('ALTER TABLE pet_activity_log_item DROP FOREIGN KEY FK_F9203150126F525E');
        $this->addSql('DROP TABLE pet_activity_log_item');
    }
}
