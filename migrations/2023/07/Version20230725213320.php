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
final class Version20230725213320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX market_listing_unique ON market_listing');
        $this->addSql('ALTER TABLE market_listing ADD non_nullable_enchantment INT NOT NULL, ADD non_nullable_spice INT NOT NULL');

        $this->addSql('UPDATE market_listing SET non_nullable_enchantment = enchantment_id WHERE enchantment_id IS NOT NULL');
        $this->addSql('UPDATE market_listing SET non_nullable_enchantment = -1 WHERE enchantment_id IS NULL');

        $this->addSql('UPDATE market_listing SET non_nullable_spice = spice_id WHERE spice_id IS NOT NULL');
        $this->addSql('UPDATE market_listing SET non_nullable_spice = -1 WHERE spice_id IS NULL');

        $this->addSql('CREATE UNIQUE INDEX market_listing_unique ON market_listing (item_id, non_nullable_enchantment, non_nullable_spice)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX market_listing_unique ON market_listing');
        $this->addSql('ALTER TABLE market_listing DROP non_nullable_enchantment, DROP non_nullable_spice');
        $this->addSql('CREATE UNIQUE INDEX market_listing_unique ON market_listing (item_id, enchantment_id, spice_id)');
    }
}
