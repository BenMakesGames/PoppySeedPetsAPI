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

final class Version20250929220351 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // three new tags: Event Exclusive, Triangles, and Tea
        $this->addSql(<<<'EOSQL'
        INSERT INTO `item_group` (`id`, `name`, `is_craving`, `is_gift_shop`) VALUES ('55', 'Event Exclusive', '0', '0'), ('56', 'Triangles', '1', '0'), (57, 'Tea', '1', '0')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // add all teas to Tea group
        $this->addSql(<<<'EOSQL'
        INSERT INTO item_group_item (item_group_id, item_id)
        SELECT 57 AS item_group_id, item.id AS item_id
        FROM item WHERE item.name IN (
            'Tea Leaves',
            'Ginger Tea',
            'Sweet Ginger Tea',
            'Black Tea',
            'Sweet Black Tea',
            'Coffee Bean Tea',
            'Tea with Mammal Extract',
            'Sweet Tea with Mammal Extract',
            'Sweet Coffee Bean Tea',
            'Coffee Bean Tea with Mammal Extract',
            'Sweet Coffee Bean Tea with Mammal Extract',
            'Dreamwalker''s Tea',
            'Tiny Tea',
            'Tremendous Tea',
            'Totally Tea',
            'Tea Trowel',
            'Apricot Coffee Bean Tea with Mammal Extract',
            'Fancy Teapot'
        )
        ON DUPLICATE KEY UPDATE `item_group_id` = `item_group_id`;
        EOSQL);

        // add items to Event Exclusive group
        $this->addSql(<<<'EOSQL'
        INSERT INTO item_group_item (item_group_id, item_id)
        SELECT 55 AS item_group_id, item.id AS item_id
        FROM item WHERE item.name IN (
            '"Roy" Plushy',
            '"Wolf" Balloon',
            '4th of July Box',
            '8',
            'Amigasa',
            'Anniversary Poppy Seed* Muffin',
            'Apricot Coffee Bean Tea with Mammal Extract',
            'Apricrate',
            'Aprihat',
            'Ashen Yew',
            'Awa Odori Box',
            'Bastille Day Box',
            'Bat Hat',
            'Black Animal Ears',
            'Bleached Turkey Head',
            'Blue Firework',
            'Blue Magic',
            'Blue Plastic Egg',
            'Blush of Life',
            'Bûche De Noël Recipe',
            'Bungee Cord',
            'Candle',
            'Chocolate Bomb',
            'Chocolate Bunny',
            'Cinco de Mayo Box',
            'Compass (the Math Kind)',
            'Creepy Mask Day',
            'Crystalline',
            'Cupid',
            'Dapper Swan Lantern',
            'Dark Chocolate Bunny',
            'Distressed Pumpkin Bucket',
            'Dreidel',
            'Earth Tree Seed',
            'Empty Cauldron',
            'Empty Horn of Plenty',
            'Giant Turkey Leg',
            'Glitter Bomb',
            'Gold and Apricots',
            'Gold Devil',
            'Gold Dragon Ingot',
            'Green Scroll',
            'Headsnake',
            'Horn of Plenty',
            'Jelephant Aminal Crackers',
            'Jester''s Cap',
            'Knitting Needles',
            'La Feuille',
            'Large Radish',
            'Lightpike',
            'Lunar New Year Box',
            'Lutefisk',
            'Magic Brush',
            'Magic Pinecone',
            'Marshmallow Bulbun',
            'Mooncake',
            'Moonlight Lantern',
            'Mysterious Seed',
            'New Year Box',
            'Odori 0.0%',
            'On Vampires',
            'One-year Anniversary Gift',
            'Pallid Batling',
            'Perse Batling',
            'Pi Lantern',
            'Pi Pie',
            'Pinecone',
            'Pink Balloon',
            'Pink Bow',
            'Pink Plastic Egg',
            'Pot of Gold',
            'Purple PSP B-day Present',
            'Red Envelope',
            'Red Firework',
            'Red PSP B-day Present',
            'Red Umbrella',
            'Rock-painting Kit (for Kids)',
            'Santa Hat',
            'Slice of Poppy Seed* Pie',
            'Smallish Pumpkin Spice',
            'Sneqos & Ladders',
            'Summer Goodie Bag',
            'Terror Seed',
            'The Unicorn',
            'Tile: Everice Cream',
            'Tile: Lovely Haberdashers',
            'Tile: Pluot Parade',
            'Treelight Lantern',
            'Turkey King',
            'Twu Wuv',
            'Unconvinced Pumpkin Bucket',
            'Wed Bawwoon',
            'White Animal Ears',
            'White Firework',
            'Winter Goodie Bag',
            'Witch''s Hat',
            'Yellow Firework',
            'Yellow Plastic Egg',
            'Yellow PSP B-day Present'
        )
        ON DUPLICATE KEY UPDATE `item_group_id` = `item_group_id`;
        EOSQL);

        // add items to Triangles group
        $this->addSql(<<<'EOSQL'
        INSERT INTO item_group_item (item_group_id, item_id)
        SELECT 56 AS item_group_id, item.id AS item_id
        FROM item
        WHERE
            item.name IN (
                'Apricot PB&J',
                'Blackberry PB&J',
                'Blueberry PB&J',
                'Carrot PB&J',
                'Egg Salad Sammy',
                'Glowing Eight-sided Die',
                'Glowing Four-sided Die',
                'Glowing Twenty-sided Die',
                'Gold Triangle',
                'Gold Trifecta',
                'Gold Triskaidecta',
                'Green Balloon',
                'Grilled Cheese',
                'Hakuna Frittata',
                'Hyperchromatic Prism',
                'Jellyfish PB&J',
                'Merkaba of Air',
                'Metatron''s Fire',
                'Naner PB&J',
                'Orange PB&J',
                'Pamplemousse PB&J',
                'Pizzaface',
                'Red PB&J',
                'Ribbely''s Composite',
                'Royal PB&J',
                'Slice of Cheese Pizza',
                'Slice of Chicken BBQ Pizza',
                'Slice of Flan Pâtissier',
                'Slice of Mixed Mushroom Pizza',
                'Slice of Pineapple Pizza',
                'Slice of Spicy Calamari Pizza',
                'Spanish Tortilla',
                'Spicy Tuna Salad Sammy',
                'Tuna Salad Sammy',
                'Zongzi'
            )
            OR item.name LIKE '%Onigiri%'
            OR item.name LIKE 'Slice of % Pie'
        ON DUPLICATE KEY UPDATE `item_group_id` = `item_group_id`;
        EOSQL);

        // add coins to Circles group:
        $this->addSql(<<<'EOSQL'
        INSERT INTO item_group_item (item_group_id, item_id)
        SELECT 28 AS item_group_id, item.id AS item_id
        FROM item
        WHERE item.name LIKE '% Coin'
        ON DUPLICATE KEY UPDATE `item_group_id` = `item_group_id`;
        EOSQL);

        // new item: Fancy Teapot
        $this->addSql(<<<'EOSQL'
        -- tool effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`, `physics`, `electronics`, `hacking`, `umbra`, `magic_binding`, `mining`) VALUES (520,0,0,0,0,0,0.15,0.6,-25,0.51,0,0,0,0,0,0,NULL,0,0,0,0,21,46,0,0,0,0,0,0,0,0,0,0,0,NULL,"Cordial",120,0,0,0,0,0,0) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- hat
        INSERT INTO item_hat (`id`, `head_x`, `head_y`, `head_angle`, `head_scale`, `head_angle_fixed`) VALUES (300,0.525,0.92,0,0.51,0) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1486,"Fancy Teapot","Some people have wondered if there\'s a teapot, too small to be seen by telescopes, orbiting the Sun somewhere in space between the Earth and Mars. Others have wondered if there\'s an optimal number of teapots to have in a room.
        
        It seems teapots are the source of many questions, both philosophical and practical.","tool/teapot","[[\"Smash\",\"fancyTeapot\\/#\\/smash\"]]",520,NULL,0,NULL,300,0,0,NULL,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1570,1486,"a") ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
