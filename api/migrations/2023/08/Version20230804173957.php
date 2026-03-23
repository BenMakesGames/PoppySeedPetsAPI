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

use App\Enum\UnlockableFeatureEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230804173957 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    private const FEATURES = [
        [ 'column' => 'unlocked_florist', 'name' => UnlockableFeatureEnum::Florist ],
        [ 'column' => 'unlocked_bookstore', 'name' => UnlockableFeatureEnum::Bookstore ],
        [ 'column' => 'unlocked_museum', 'name' => UnlockableFeatureEnum::Museum ],
        [ 'column' => 'unlocked_park', 'name' => UnlockableFeatureEnum::Park ],
        [ 'column' => 'unlocked_greenhouse', 'name' => UnlockableFeatureEnum::Greenhouse ],
        [ 'column' => 'unlocked_basement', 'name' => UnlockableFeatureEnum::Basement ],
        [ 'column' => 'unlocked_market', 'name' => UnlockableFeatureEnum::Market ],
        [ 'column' => 'unlocked_fireplace', 'name' => UnlockableFeatureEnum::Fireplace ],
        [ 'column' => 'unlocked_beehive', 'name' => UnlockableFeatureEnum::Beehive ],
        [ 'column' => 'unlocked_trader', 'name' => UnlockableFeatureEnum::Trader ],
        [ 'column' => 'unlocked_mailbox', 'name' => UnlockableFeatureEnum::Mailbox ],
        [ 'column' => 'unlocked_dragon_den', 'name' => UnlockableFeatureEnum::DragonDen ],
        [ 'column' => 'unlocked_bulk_selling', 'name' => UnlockableFeatureEnum::BulkSelling ],
        [ 'column' => 'unlocked_hattier', 'name' => UnlockableFeatureEnum::Hattier ],
        [ 'column' => 'unlocked_field_guide', 'name' => UnlockableFeatureEnum::FieldGuide ],
    ];

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_unlocked_feature (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, feature VARCHAR(40) NOT NULL, unlocked_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B2D014A76ED395 (user_id), UNIQUE INDEX user_id_feature_idx (user_id, feature), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_unlocked_feature ADD CONSTRAINT FK_B2D014A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');

        foreach(self::FEATURES as $feature) {
            $this->addSql('INSERT INTO user_unlocked_feature (user_id, feature, unlocked_on) SELECT id, \'' . $feature['name'] . '\', ' . $feature['column'] . ' FROM user WHERE ' . $feature['column'] . ' IS NOT NULL');
        }

        $this->addSql('ALTER TABLE user DROP unlocked_florist, DROP unlocked_bookstore, DROP unlocked_museum, DROP unlocked_park, DROP unlocked_greenhouse, DROP unlocked_basement, DROP unlocked_market, DROP unlocked_fireplace, DROP unlocked_beehive, DROP unlocked_recycling, DROP unlocked_trader, DROP unlocked_mailbox, DROP unlocked_dragon_den, DROP unlocked_bulk_selling, DROP unlocked_hattier, DROP unlocked_field_guide');
        $this->addSql('CREATE UNIQUE INDEX user_species_idx ON user_species_collected (user_id, species_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_unlocked_feature DROP FOREIGN KEY FK_B2D014A76ED395');
        $this->addSql('DROP TABLE user_unlocked_feature');
        $this->addSql('ALTER TABLE user ADD unlocked_florist DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD unlocked_bookstore DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD unlocked_museum DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD unlocked_park DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD unlocked_greenhouse DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD unlocked_basement DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD unlocked_market DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD unlocked_fireplace DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD unlocked_beehive DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD unlocked_recycling DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD unlocked_trader DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD unlocked_mailbox DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD unlocked_dragon_den DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD unlocked_bulk_selling DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD unlocked_hattier DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD unlocked_field_guide DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('DROP INDEX user_species_idx ON user_species_collected');
    }
}
