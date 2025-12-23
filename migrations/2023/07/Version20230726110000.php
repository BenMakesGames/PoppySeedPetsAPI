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

final class Version20230726110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO `field_guide_entry` (`id`, `type`, `name`, `image`, `description`) VALUES (20, 'location', 'The Hollow Earth', 'hollow-earth', 'When Edmond Halley wasn\'t looking for comets, he was also looking for an explanation as to why the Earth\'s magnetic poles drifted over time. His proposal was that the Earth might be composed of concentric, rotating, hollow spheres, and that as those spheres turned, they pulled at the Earth\'s magnetic field.\n\nSince then, numerous explorers have attempted to find entrances to the inside of the Earth. When the Portal was first discovered on Poppy Seed Pets island, the \"theory\" that the Earth might be hollow rose dramatically in popularity. Today, it\'s estimated that a worrying 10-15% of people now believe the Earth to actually be hollow!\n\nIn the scientific community, the two dominant theories surrounding the Portal are: 1. that it\'s a wormhole to some distant location, and evidence that negative mass may be physically possible, or 2. that it\'s an opening into some unfurled dimensions of space, and evidence in support of 11-dimensional String Theory.\n\nWhatever the case, it seems you\'ve also found one inside your house.\n\nSo that\'s neat.');");
    }

    public function down(Schema $schema): void
    {
    }
}
