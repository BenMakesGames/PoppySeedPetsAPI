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

final class Version20250126162500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Orange Gummies description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'When people ask what my favorite color is, I say \"transparent orange\". Not a color I often wear, though...' WHERE `item`.`id` = 26;
        EOSQL);

        // Scales description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'The fish kind. Not the musical kind. Or the weight kind. Don\'t feel too bad: there\'s a lot of different kinds of scales - it\'s easy to get mixed up.' WHERE `item`.`id` = 35; 
        EOSQL);

        // Rice description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'I was told that wheat contains about 65% water, while rice consists of 68% water. That\'s why, when I\'m boiling wheat, I always put in just a liiiiittle less water than when I\'m boiling rice. I asked an AI chat bot for help figuring out the exact numbers, but it seemed _really_ confused by what I was talking about, so, I dunno, I\'m thinking this whole AI thing might just be a fad.' WHERE `item`.`id` = 17; 
        EOSQL);

        // Welcome Note description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = '👋' WHERE `item`.`id` = 85; 
        EOSQL);

        // Tomato description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'The otamot got its name because its discoverer thought it tasted \"totally opposite of a tomato\", as she described it, so she just reversed the letters of \"tomato\", and there you have it: \"otamot\"!\n\nThe more you know!' WHERE `item`.`id` = 133;  
        EOSQL);

        // Cheese description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = '\"No matter how hard you work, you\'ll never be as famous as cheese.\" ~Dan Avidan' WHERE `item`.`id` = 255; 
        EOSQL);

        // Magpie's Deal description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Ha! Revenge!' WHERE `item`.`id` = 268; 
        EOSQL);

        // Large Radish is now a tool... and now has a description:
        $this->addSql(<<<EOSQL
        INSERT INTO `item_tool` (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (493, '0', '0', '2', '0', '0', '0.495', '0.385', '0', '0.7', '0', '0', '0', '0', '0', '0', NULL, '0', '0', '0', '1', NULL, NULL, '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', NULL, NULL, NULL)
        ON DUPLICATE KEY UPDATE `id` = `id`;

        UPDATE `item` SET `tool_id` = '493', `description` = 'Just _thinking_ about that game, the music gets instantly stuck in my head, you know?\n\n🎵 Do d-do dooo, d-do d-do d-do dooooooo...! 🎵' WHERE `item`.`id` = 368; 
        EOSQL);

        // Candied Ginger description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Ginger is incredible in so many of its forms. This is one of them.' WHERE `item`.`id` = 373; 
        EOSQL);

        // Phishing Rod description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Good news: you can still make 100,000~~m~~ this year!' WHERE `item`.`id` = 445; 
        EOSQL);

        // Dancing Sword description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'One of the first enchantments learned by many new mages is one that animates simple objects.\n\nOne of the last enchantments cast by many new mages is one that animates a sharp object.' WHERE `item`.`id` = 465; 
        EOSQL);

        // Antipode description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'How can the raw, elemental forces of fire and ice exist together in one blade without annihilating one another?!?\n\nMagic.\n\nLots and lots of magic.' WHERE `item`.`id` = 484;  
        EOSQL);

        // Strange Attractor description
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Oh, mathematicians! Always coming up with the silliest names for things! What\'s next - _sexy primes?_' WHERE `item`.`id` = 718; 
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
