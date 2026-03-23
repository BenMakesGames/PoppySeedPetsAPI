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
final class Version20220122154858 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pet_activity_log_pet_activity_log_tag (pet_activity_log_id INT NOT NULL, pet_activity_log_tag_id INT NOT NULL, INDEX IDX_71B15D7C9F3A396D (pet_activity_log_id), INDEX IDX_71B15D7CCEF233DC (pet_activity_log_tag_id), PRIMARY KEY(pet_activity_log_id, pet_activity_log_tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pet_activity_log_tag (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(40) NOT NULL, color VARCHAR(6) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pet_activity_log_pet_activity_log_tag ADD CONSTRAINT FK_71B15D7C9F3A396D FOREIGN KEY (pet_activity_log_id) REFERENCES pet_activity_log (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pet_activity_log_pet_activity_log_tag ADD CONSTRAINT FK_71B15D7CCEF233DC FOREIGN KEY (pet_activity_log_tag_id) REFERENCES pet_activity_log_tag (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pet_activity_log_pet_activity_log_tag DROP FOREIGN KEY FK_71B15D7CCEF233DC');
        $this->addSql('DROP TABLE pet_activity_log_pet_activity_log_tag');
        $this->addSql('DROP TABLE pet_activity_log_tag');
    }
}
