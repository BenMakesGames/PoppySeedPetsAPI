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
final class Version20240518124421 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_listing DROP FOREIGN KEY FK_C296A054CF04D12D');
        $this->addSql('ALTER TABLE market_listing DROP FOREIGN KEY FK_C296A054F3927CF3');
        $this->addSql('DROP INDEX market_listing_unique ON market_listing');
        $this->addSql('DROP INDEX IDX_C296A054F3927CF3 ON market_listing');
        $this->addSql('DROP INDEX IDX_C296A054CF04D12D ON market_listing');
        $this->addSql('ALTER TABLE market_listing DROP enchantment_id, DROP spice_id, DROP full_item_name, DROP non_nullable_enchantment, DROP non_nullable_spice');

        $this->addSql('
            DELETE FROM market_listing WHERE id NOT IN (
                SELECT id FROM (
                    SELECT MIN(id) as id FROM market_listing GROUP BY item_id
                ) as t
            )
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_listing ADD enchantment_id INT DEFAULT NULL, ADD spice_id INT DEFAULT NULL, ADD full_item_name VARCHAR(100) NOT NULL, ADD non_nullable_enchantment INT NOT NULL, ADD non_nullable_spice INT NOT NULL');
        $this->addSql('ALTER TABLE market_listing ADD CONSTRAINT FK_C296A054CF04D12D FOREIGN KEY (spice_id) REFERENCES spice (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE market_listing ADD CONSTRAINT FK_C296A054F3927CF3 FOREIGN KEY (enchantment_id) REFERENCES enchantment (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX market_listing_unique ON market_listing (item_id, non_nullable_enchantment, non_nullable_spice)');
        $this->addSql('CREATE INDEX IDX_C296A054F3927CF3 ON market_listing (enchantment_id)');
        $this->addSql('CREATE INDEX IDX_C296A054CF04D12D ON market_listing (spice_id)');
    }
}
