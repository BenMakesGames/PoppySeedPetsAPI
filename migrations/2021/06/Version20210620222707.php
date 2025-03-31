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
final class Version20210620222707 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE letter ADD field_guide_entry_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE letter ADD CONSTRAINT FK_8E02EE0A9D9C0F06 FOREIGN KEY (field_guide_entry_id) REFERENCES field_guide_entry (id)');
        $this->addSql('CREATE INDEX IDX_8E02EE0A9D9C0F06 ON letter (field_guide_entry_id)');
        $this->addSql('ALTER TABLE plant ADD field_guide_entry_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE plant ADD CONSTRAINT FK_AB030D729D9C0F06 FOREIGN KEY (field_guide_entry_id) REFERENCES field_guide_entry (id)');
        $this->addSql('CREATE INDEX IDX_AB030D729D9C0F06 ON plant (field_guide_entry_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE letter DROP FOREIGN KEY FK_8E02EE0A9D9C0F06');
        $this->addSql('DROP INDEX IDX_8E02EE0A9D9C0F06 ON letter');
        $this->addSql('ALTER TABLE letter DROP field_guide_entry_id');
        $this->addSql('ALTER TABLE plant DROP FOREIGN KEY FK_AB030D729D9C0F06');
        $this->addSql('DROP INDEX IDX_AB030D729D9C0F06 ON plant');
        $this->addSql('ALTER TABLE plant DROP field_guide_entry_id');
    }
}
