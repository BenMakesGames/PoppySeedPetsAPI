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

use App\Enum\SpiritCompanionStarEnum;
use App\Service\Xoshiro;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190724202334 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE spirit_companion CHANGE skill star VARCHAR(40) NOT NULL');
    }

    public function postUp(Schema $schema): void
    {
        parent::postUp($schema);

        $rng = new Xoshiro();
        $companions = $this->connection->fetchAllAssociative('SELECT id FROM spirit_companion');

        foreach($companions as $companion)
        {
            $this->connection->executeQuery(
                'UPDATE spirit_companion SET star=:star WHERE id=:id LIMIT 1',
                [
                    'id' => $companion['id'],
                    'star' => $rng->rngNextFromArray(SpiritCompanionStarEnum::cases())->value
                ]
            );
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE spirit_companion CHANGE star skill VARCHAR(40) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('UPDATE spirit_companion SET skill=\'\'');
    }
}
