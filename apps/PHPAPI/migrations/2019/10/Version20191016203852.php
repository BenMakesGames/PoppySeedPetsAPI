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
final class Version20191016203852 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet ADD mom_id INT DEFAULT NULL, ADD dad_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pet ADD CONSTRAINT FK_E4529B854E866E47 FOREIGN KEY (mom_id) REFERENCES pet (id)');
        $this->addSql('ALTER TABLE pet ADD CONSTRAINT FK_E4529B85ABB1CE64 FOREIGN KEY (dad_id) REFERENCES pet (id)');
        $this->addSql('CREATE INDEX IDX_E4529B854E866E47 ON pet (mom_id)');
        $this->addSql('CREATE INDEX IDX_E4529B85ABB1CE64 ON pet (dad_id)');
        $this->addSql('ALTER TABLE pet_baby ADD color_a VARCHAR(6) NOT NULL, ADD color_b VARCHAR(6) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet DROP FOREIGN KEY FK_E4529B854E866E47');
        $this->addSql('ALTER TABLE pet DROP FOREIGN KEY FK_E4529B85ABB1CE64');
        $this->addSql('DROP INDEX IDX_E4529B854E866E47 ON pet');
        $this->addSql('DROP INDEX IDX_E4529B85ABB1CE64 ON pet');
        $this->addSql('ALTER TABLE pet DROP mom_id, DROP dad_id');
        $this->addSql('ALTER TABLE pet_baby DROP color_a, DROP color_b');
    }
}
