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

final class Version20251223192501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'More & better uses for Kat\'s Gift Package';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `use_actions` = '[[\"67 Liquid-hot Magma\",\"katsGift/#/67magma\"],[\"14 Baabbles\",\"katsGift/#/baabbles\"],[\"Two Renaming Scrolls\",\"katsGift/#/renamingScrolls\"],[\"One Scroll of Illusions\",\"katsGift/#/scrollOfIllusions\"],[\"½ Species Transmigration Serum\",\"katsGift/#/serum\"]]' WHERE `item`.`id` = 1391; 
        EOSQL);

        $this->addSql(<<<EOSQL
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1502,"½ Species Transmigration Serum",NULL,"potion/species-transmigration-half",NULL,NULL,NULL,0,NULL,NULL,0,0,NULL,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        UPDATE `item` SET `description` = 'Eh? Only ½??! That\'s not enough dosage for one pet!\n\nOh, what, so I suppose I\'m just supposed to combine two of them to get a whole one, right?\n\nUgh! Ridiculous!' WHERE `item`.`id` = 1502;
        EOSQL);

        $this->addSql(<<<EOSQL
        INSERT INTO `item_food` (`id`, `food`, `love`, `junk`, `alcohol`, `earthy`, `fruity`, `tannic`, `spicy`, `creamy`, `meaty`, `planty`, `fishy`, `floral`, `fatty`, `oniony`, `chemically`, `caffeine`, `psychedelic`, `granted_skill`, `chance_for_bonus_item`, `random_flavor`, `contains_tentacles`, `granted_status_effect`, `granted_status_effect_duration`, `is_candy`, `leftovers_id`, `bonus_item_group_id`) VALUES (536, '0', '0', '0', '0', '0', '0', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', NULL, NULL, '0', '0', NULL, NULL, '0', '36', NULL)
        ON DUPLICATE KEY UPDATE `id` = `id`;

        INSERT INTO `spice` (`id`, `effects_id`, `name`, `is_suffix`) VALUES (47, '536', 'Bleating', '0')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
