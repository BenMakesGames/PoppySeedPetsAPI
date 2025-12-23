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
final class Version20201218060857 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE letter ADD attachment_id INT DEFAULT NULL, ADD bonus_id INT DEFAULT NULL, ADD spice_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE letter ADD CONSTRAINT FK_8E02EE0A464E68B FOREIGN KEY (attachment_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE letter ADD CONSTRAINT FK_8E02EE0A69545666 FOREIGN KEY (bonus_id) REFERENCES enchantment (id)');
        $this->addSql('ALTER TABLE letter ADD CONSTRAINT FK_8E02EE0ACF04D12D FOREIGN KEY (spice_id) REFERENCES spice (id)');
        $this->addSql('CREATE INDEX IDX_8E02EE0A464E68B ON letter (attachment_id)');
        $this->addSql('CREATE INDEX IDX_8E02EE0A69545666 ON letter (bonus_id)');
        $this->addSql('CREATE INDEX IDX_8E02EE0ACF04D12D ON letter (spice_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE letter DROP FOREIGN KEY FK_8E02EE0A464E68B');
        $this->addSql('ALTER TABLE letter DROP FOREIGN KEY FK_8E02EE0A69545666');
        $this->addSql('ALTER TABLE letter DROP FOREIGN KEY FK_8E02EE0ACF04D12D');
        $this->addSql('DROP INDEX IDX_8E02EE0A464E68B ON letter');
        $this->addSql('DROP INDEX IDX_8E02EE0A69545666 ON letter');
        $this->addSql('DROP INDEX IDX_8E02EE0ACF04D12D ON letter');
        $this->addSql('ALTER TABLE letter DROP attachment_id, DROP bonus_id, DROP spice_id');
    }
}
