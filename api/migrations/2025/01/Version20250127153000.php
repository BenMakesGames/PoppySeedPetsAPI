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

final class Version20250127153000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Yellow Dye description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'The label clearly states this can be used to dye things yellow. Like buckets. Or idols that need to look gold for completely legitimate reasons that don\'t require further explanation...' WHERE `item`.`id` = 94; 
        EOSQL);

        // Champignon description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Ever notice how mushrooms pop up in circles sometimes? Like they\'re trying to tell us something about the spaces between spaces. This staff seems to whisper similar secrets - though mostly about where to find more sparkly magic stuff.' WHERE `item`.`id` = 118; 
        EOSQL);

        // Decorated Spear description shortening
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Nothing says \"I\'m serious about survival\" like a splash of colorful plumage.' WHERE `item`.`id` = 153; 
        EOSQL);

        // Dragonstick description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'The dragon part helps you beat up monsters. The stick part helps you point at things. Both parts help you look like you know what you\'re doing.' WHERE `item`.`id` = 1298; 
        EOSQL);

        // Moon Pearl description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Caught between what it was and what it will become - to either shape greater magics, or be smashed into flavorful dust.\n\nThere\'s gotta\' be a metaphor in there, somewhere, but I\'m too distracted by how sparkly it is to think of one.' WHERE `item`.`id` = 137; 
        EOSQL);

        // Blueberry Yogurt description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Remember that story about the god of love wearing a crown of blueberries? Well, they\'re also credited with inventing this yogurt. The god of love, I mean. Not the blueberries. Blueberries haven\'t invented diddly-squat.' WHERE `item`.`id` = 150; 
        EOSQL);

        // Blackberry Yogurt description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'ALLERGY WARNING: Produced in a facility that processes memories of climbing through thorny bushes, scraped knees, and stained fingers.' WHERE `item`.`id` = 151; 
        EOSQL);

        // Apricot Preserves description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Sugar and fruit join in a complex dance that somehow convinces time to look the other way.' WHERE `item`.`id` = 179; 
        EOSQL);

        // Bean Milk description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Someone asked \"what if we could milk a bean?\" Then they actually figured out how to do that. Science can be beautiful sometimes.' WHERE `item`.`id` = 215; 
        EOSQL);

        // Iron Tongs description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'These help you grab hot things without grabbing hot things. If that doesn\'t make sense, you probably need these more than you realize.' WHERE `item`.`id` = 229; 
        EOSQL);

        // The Umbra description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'The pages seem to shift when you\'re not looking directly at them, and like most beginner\'s guides, it\'s remarkably unclear about several important things. Your pets seem to understand it just fine, though.' WHERE `item`.`id` = 261; 
        EOSQL);

        // Tentacle description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'It\'s a perfectly normal tentacle that occasionally waves hello. Your Lolligovore pets insist this is the best part.' WHERE `item`.`id` = 253; 
        EOSQL);

        // Cobbler Recipe description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'The dessert kind, not the shoe kind. (It\'d be weird to call the shoe kind a \"recipe.\")' WHERE `item`.`id` = 298; 
        EOSQL);

        // Fish Stroganoff description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Necessity is the mother of invention; the distinct lack of beef on the island is the mother of Fish Stroganoff.' WHERE `item`.`id` = 305; 
        EOSQL);

        // "Chicken" Noodle Soup description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Every attempt to summon chickens on the island has just resulted in more fish. Summoning turkey works fine though, oddly enough.' WHERE `item`.`id` = 323;
        EOSQL);

        // Fermented Fish Onigiri description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Sometimes love takes time, you know?' WHERE `item`.`id` = 309; 
        EOSQL);

        // Witch's Hat description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Does a witch\'s hat make a witch, or does a witch make a witch\'s hat? Or maybe a pointy hat is just a pointy hat.' WHERE `item`.`id` = 332; 
        EOSQL);

        // Evil Feather Duster description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'What makes a feather duster evil, anyway? Is it the black feathers? The slightly menacing bristle arrangement? The way it whispers with the broom at midnight?' WHERE `item`.`id` = 344; 
        EOSQL);

        // new item! Wicked Broom
        $this->addSql(<<<EOSQL
        -- enchantment effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (495,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,NULL,0,0,0,0,34,36,1,0,0,0,0,0,0,0,0,0,1,NULL,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- enchantment
        INSERT INTO enchantment (`id`, `effects_id`, `name`, `is_suffix`, `aura_id`) VALUES (147,495,"Wicked",0,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- tool effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (494,0,0,2,4,0,0.705,0.355,12,0.79,0,0,2,0,0,0,"arcana",0,0,0,0,34,36,3,0,0,0,0,0,0,0,0,0,1,NULL,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1438,"Wicked Broom",NULL,"tool/broom/wicked",NULL,494,NULL,0,NULL,NULL,690,15,147,NULL,NULL,0,NULL,0,30) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1523,1438,"a") ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // Suacepan description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Isn\'t all cooking just playing with food?' WHERE `item`.`id` = 771; 
        EOSQL);

        // Alice's Secret description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Apparently this bucket\'s color-mixing formula is \"totally secure\"? What does that even _mean?_' WHERE `item`.`id` = 776;
        EOSQL);

        // Bob's Secret description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'The paint looks like a normal shade of blue, but there\'s something oddly prime-number-ish about the way it shimmers...' WHERE `item`.`id` = 777; 
        EOSQL);

        // Rib description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'This rib once kept a creature\'s insides where they belonged. Now your pets use it for... well, the exact opposite of that.' WHERE `item`.`id` = 802;
        EOSQL);

        // Chocolate Sword description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'They say the best blades are forged in fire and taste of victory. This one was made in a kitchen and tastes like chocolate. Hard to say which is better.' WHERE `item`.`id` = 787; 
        EOSQL);

        // Canned Food description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Each can exists in a superposition of all possible foods until opened. Some say this is proof that the whole universe is just a simulation...' WHERE `item`.`id` = 814; 
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
