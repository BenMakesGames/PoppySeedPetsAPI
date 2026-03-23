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

use App\Entity\Merit;
use App\Enum\MeritEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191018165705 extends AbstractMigration
{
    const MERITS = [
        [
            'name' => MeritEnum::EIDETIC_MEMORY,
            'description' => '%pet.name% will never forget.',
        ],
        [
            'name' => MeritEnum::BLACK_HOLE_TUM,
            'description' => '%pet.name% will be able to store a bit more food in their stomach. Oh: and will poop Dark Matter???',
        ],
        [
            'name' => MeritEnum::LUCKY,
            'description' => '%pet.name% will stumble upon fortune more often than most! Lucky~!',
        ],
        [
            'name' => MeritEnum::MOON_BOUND,
            'description' => '%pet.name% will be physically stronger near a full moon, but slightly weaker on new moons.',
        ],
        [
            'name' => MeritEnum::NATURAL_CHANNEL,
            'description' => '%pet.name% will be able to slip into and out of the Umbra at will - sometimes accidentally!',
        ],
        [
            'name' => MeritEnum::NO_SHADOW_OR_REFLECTION,
            'description' => '%pet.name% will no longer cast a shadow, and will no longer be visible in reflective surfaces! (Creepy...)',
        ],
        [
            'name' => MeritEnum::SOOTHING_VOICE,
            'description' => '%pet.name%\'s voice will calm all who hear it...',
        ],
        [
            'name' => MeritEnum::SPIRIT_COMPANION,
            'description' => '%pet.name% will attract a friendly spirit companion!',
        ],
        [
            'name' => MeritEnum::PROTOCOL_7,
            'description' => '%pet.name% will be able to access Project-E directly, without the need for a device.',
        ],
        [
            'name' => MeritEnum::INTROSPECTIVE,
            'description' => '%pet.name% will know clearly what they want in their relationships... and so will you!',
        ],
        [
            'name' => MeritEnum::VOLAGAMY,
            'description' => '%pet.name% will be able to become pregnant or lay eggs, and you\'ll be able to turn this ability on or off at will.',
        ]
    ];

    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE merit (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pet CHANGE merits old_merits JSON NOT NULL');

        foreach(self::MERITS as $merit)
            $this->addSql('INSERT INTO merit (`name`, `description`) VALUES ("' . $merit['name'] . '", "' . $merit['description'] . '")');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE merit');
        $this->addSql('ALTER TABLE pet CHANGE old_merits merits JSON NOT NULL');
    }
}
