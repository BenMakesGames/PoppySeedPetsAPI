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

final class Version20251001001936 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'mooooooooooooooooooooore item descriptions';
    }

    public function up(Schema $schema): void
    {

        $this->addSql(<<<'EOSQL'
        -- Hyperchromatic Prism
        UPDATE `item` SET `description` = 'Not all triangles are edible. _Many_ are... but not all.\n\nNever try to eat a Hyperchromatic Prism.' WHERE `item`.`id` = 652;

        -- Antlers
        UPDATE `item` SET `description` = '_I_ know a cool fact about antlers. Do _you_ know a cool fact about antlers? I\'ll tell you my cool fact about antlers if you\'ll tell me yours.' WHERE `item`.`id` = 1116;
        
        -- Blackberry PB&J
        UPDATE `item` SET `description` = 'Triangle cut is the best cut for a sandwich. If you didn\'t know this, your parents didn\'t raise you right.' WHERE `item`.`id` = 527;
        
        -- Blueberry PB&J
        UPDATE `item` SET `description` = 'Triangle cut is the best cut. For a sandwich! Not for... whatever _you_ were thinking!\n\nWeirdo!' WHERE `item`.`id` = 526;
        
        -- Apricot PB&J
        UPDATE `item` SET `description` = 'Apricot cut is... the triangle cut... ... Uh... sandwiches are best...?\n\nOh, man - I messed up bad, y\'all.' WHERE `item`.`id` = 529;
        
        -- Carrot PB&J
        UPDATE `item` SET `description` = 'Triangle cut is the best cut for a sandwich. I mean, think about it: a carrot is basically a triangle. Or, well, a _cone_, I guess. But cones are basically triangles anyway, right???\n\nSee! It all makes perfect sense!' WHERE `item`.`id` = 1073;
        
        -- Empty Crate
        UPDATE `item` SET `description` = 'There\'s not much you can do with an Empty Crate. Some have suggested using them as makeshift TV stands, but c\'mon: have you even _tried_ carrying a TV? Those things are heavy! Even a _Sturdy_ Empty Crate couldn\'t hold one up for more than the runtime of a single episode of Star Trek: The Next Generation, and this is just a _regular-type_ Empty Crate! We\'re talking half a Ren and Stimpy _AT BEST._' WHERE `item`.`id` = 1470;
        
        -- Fish Bones
        UPDATE `item` SET `description` = 'I knew a goblin named \"Fish Bones.\" Cool guy. He never did get along with Gravel Neck, though. I never did find out why.' WHERE `item`.`id` = 970;
        
        -- Fez
        UPDATE `item` SET `description` = 'trapped in a fez factory<br>please send help' WHERE `item`.`id` = 866;

        -- Gold Devil
        UPDATE `item` SET `description` = 'Oh! Aren\'t you, um... _handsome_... \\*takes a few steps back\\*' WHERE `item`.`id` = 1401;
        
        -- Grilled Cheese
        UPDATE `item` SET `description` = 'Triangle cut is the best cut for a sandwich. Especially one made of cheese, which, _as everyone knows_, the triangle cut is also bes-- oh, wait. Wait, but string cheese! String cheese is one of the best cheeses, and it\'s not triangle at all!\n\nNooo! My logic! It should have been perfect, but it was shattered! In an instant! That\'s the most devastating amount of time it could have taken! Why couldn\'t it at least have taken longer to shatterrrrrrr!!1!' WHERE `item`.`id` = 314;
        
        -- Invisibility Juice
        UPDATE `item` SET `description` = 'With just a few drops you can turn your tongue invisible!\n\nI dunno what you\'d do with an invisible tongue, but I\'m sure you and your ridiculous hormones will come up with _something!_' WHERE `item`.`id` = 914;
        
        -- Lightning Sword
        UPDATE `item` SET `description` = 'Equipped with this sword, you can cast Thundaga!\n\nWait, hold on... sorry: that\'s the rules for a different game entirely.\n\nIn Poppy Seed Pets, the Lightning Sword... turns sand into Glass?\n\nI guess that\'s _almost_ as cool??' WHERE `item`.`id` = 480;
        
        -- Mango
        UPDATE `item` SET `description` = 'Best fruit. Hands down. Fight me. You know I\'m right. (Don\'t fight me, though, actually. My arms are like sticks.)' WHERE `item`.`id` = 815;
        
        -- Moondial Blueprint
        UPDATE `item` SET `description` = 'If you cut just the sides of the, like, pointy bit that\'s drawn on the paper, and bend it up, then, um, the _blueprint itself_ works as a fully-functioning moondial. So, like, why even build the real thing, you know?' WHERE `item`.`id` = 1443;
        
        -- Nail File
        UPDATE `item` SET `description` = 'I seriously once bought a pair of nail clippers, only to discover it didn\'t have the nail file in it! What the heck is even the point?! YOU CAN\'T HAVE NAIL CLIPPERS WITHOUT A NAIL FILE IN IT - **_THAT\'S MADNESS!_**' WHERE `item`.`id` = 1397;
        
        -- Slice of Naner Bread
        UPDATE `item` SET `description` = 'They call it \"bread\", but it seems more like a cake to me. Why aren\'t they calling it \"Slice of Naner Cake\"?\n\nCowards!' WHERE `item`.`id` = 355;
        
        -- Rainbow Wings
        UPDATE `item` SET `description` = 'Ohh, semi-translucent! I see someone was getting fancy with the graphics editor today!' WHERE `item`.`id` = 1351;        
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
