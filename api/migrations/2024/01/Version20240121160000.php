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
final class Version20240121160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'tiles!';
    }

    public function up(Schema $schema): void
    {
        // Flying Keys, Only
        $this->addSql(<<<EOSQL
        -- hollow earth tile card
        INSERT INTO hollow_earth_tile_card (`id`, `type_id`, `name`, `event`, `required_action`, `image`, `author`) VALUES (77,3,"Flying Keys, Only","{\"item\":\"Winged Key\",\"type\":\"payItem?\",\"ifPaid\":{\"type\":\"petChallenge\",\"stats\":[\"dexterity\",\"stamina\",\"brawl\"],\"ifFail\":{\"exp\":{\"stats\":[\"brawl\"],\"amount\":2},\"type\":\"onward\",\"esteem\":-2,\"safety\":-2,\"buttonText\":\"Onward...\",\"description\":\"After many jumps and tumbles, the wings fly off into the sunset, never to be seen again... (Dang!)\"},\"baseRoll\":20,\"ifSucceed\":{\"exp\":{\"stats\":[\"brawl\"],\"amount\":1},\"type\":\"onward\",\"esteem\":4,\"buttonText\":\"Onward!\",\"description\":\"The whelp and your pet catch the wings, which it let\'s you keep!\",\"receiveItems\":[\"Wings\"]},\"buttonText\":\"Catch the wings!\",\"description\":\"The whelp takes the key and chews the wings off, causing them to fly around!\",\"requiredRoll\":\"10\"},\"description\":\"A dragon whelp stands in your path, laying on a pile of gold keys. It looks expectantly towards a small note in the pile that reads \\\\\"Flying Keys Only\\\\\".\"}",1,"community/flying-keys-only","🐍") ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1360,"Tile: Flying Keys, Only",NULL,"tile/community/flying-keys-only",NULL,NULL,NULL,0,NULL,NULL,30,1,NULL,NULL,NULL,0,77,0,15) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (39, 1360);
        EOSQL);

        // Fae Court Feast
        $this->addSql(<<<EOSQL
        -- hollow earth tile card
        INSERT INTO hollow_earth_tile_card (`id`, `type_id`, `name`, `event`, `required_action`, `image`, `author`) VALUES (78,6,"A Fae Court Feast","{\"type\":\"chooseOne\",\"buttons\":[\"Spy Matters\",\"A Feast For All\"],\"outcomes\":[{\"exp\":{\"stats\":[\"stealth\"],\"amount\":2},\"type\":\"onward\",\"buttonText\":\"Fae Court Spy\",\"description\":\"%pet.name% slinks through the crowd, gathering intel.\"},{\"food\":12,\"type\":\"onward\",\"buttonText\":\"Fae Court Feast\",\"description\":\"%pet.name% checks out the banquet table, picking over the various foods, and finds a few delicacies.\"}],\"description\":\"A room in the Manor opens on a Fae Court feast - will %pet.name% be a spy, or a gourmand?\"}",0,"community/fae-court-feast","%user:2452.name%") ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1361,"Tile: Fae Court Feast",NULL,"tile/community/fae-court-feast",NULL,NULL,NULL,0,NULL,NULL,30,1,NULL,NULL,NULL,0,78,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (37, 1361);
        EOSQL);

        // Phishing Lessons
        $this->addSql(<<<EOSQL
        -- hollow earth tile card
        INSERT INTO hollow_earth_tile_card (`id`, `type_id`, `name`, `event`, `required_action`, `image`, `author`) VALUES (79,5,"Phishing Lessons","{\"item\":\"Password\",\"type\":\"payItem?\",\"ifPaid\":{\"type\":\"petChallenge\",\"stats\":[\"perception\",\"intelligence\",\"science\"],\"ifFail\":{\"type\":\"onward\",\"esteem\":-10,\"safety\":-5,\"buttonText\":\"<_<\",\"description\":\"Not knowing that much about Internet safety, %pet.name% manages to download even more malware to the laptop. One of them was a ransomware program: it swiftly locked the files behind a paywall, and insisted on needing 50 Cryptocurrency Wallets.\\\\n\\\\nThe Painter Gecko sighs in disbelief: \\\\\"This is probably the tenth laptop I\'ve had ruined...\\\\\"\"},\"baseRoll\":20,\"ifSuccess\":{\"exp\":{\"stats\":[\"science\"],\"amount\":4},\"love\":10,\"type\":\"onward\",\"esteem\":15,\"buttonText\":\"Better do that\",\"description\":\"%pet.name% is wise enough to search for the initial file the `.exe` file planted itself into. Upon removing it, the rampant advertisements disappear in a snap. The Painter Gecko seems relieved, finally getting their thanks to you and %pet.name%. They insist that they\'ll run their laptop with an antivirus or sort afterwards.\"},\"buttonText\":\"*fingers crossed*\",\"description\":\"The laptop\'s screen is met with a pop-up that states: \\\\\"Congratulations, you won! Click here to redeem your free Digital Camera!\\\\\" And then another, and a third, and a fourth. The Painter Gecko gets nervous: \\\\\"No... No!!\\\\\"\\\\n\\\\nIn major panic, they ask for the help of %pet.name%.\",\"requiredRoll\":\"20\"},\"ifNotPaid\":{\"type\":\"onward\",\"buttonText\":\"Onward!\",\"description\":\"You give the gecko a stern look and move on.\"},\"description\":\"You see a lone Painter Gecko sitting on a rock, tapping away on their laptop. While they seem shady, %pet.name% approaches them in interest. Upon having a chat with each other, the Painter Gecko reveals that they\'re going to pirate a new, sought-out video game. They had found a `.zip` file containing the assets, or so they say.\\\\n\\\\nTo open the suspicious `.zip` file, they need a Password.\"}",0,"community/phishing-lessons","%user:2660.name%") ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1362,"Tile: Phishing Lessons",NULL,"tile/community/phishing-lessons",NULL,NULL,NULL,0,NULL,NULL,30,1,NULL,NULL,NULL,0,79,0,5) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (38, 1362);
        EOSQL);

        // Heavy Stuff
        $this->addSql(<<<EOSQL
        -- hollow earth tile card
        INSERT INTO hollow_earth_tile_card (`id`, `type_id`, `name`, `event`, `required_action`, `image`, `author`) VALUES (80,2,"Heavy Stuff","{\"type\":\"petChallenge\",\"stats\":[\"strength\"],\"ifFail\":{\"type\":\"onward\",\"buttonText\":\"I didn\'t need flour anyways.\",\"description\":\"%pet.name% failed to pick the flour up. They dropped it and it went everywhere. %pet.name% picked up some Silica Grounds and pretended that they had always wanted to pick that up.\",\"receiveItems\":[\"Silica Grounds\"]},\"baseRoll\":20,\"ifSuccess\":{\"type\":\"onward\",\"buttonText\":\"Move on.\",\"description\":\"%pet.name% picked the bag of flour up. It\'s some Wheat Flour.\",\"receiveItems\":[\"Wheat Flour\"]},\"buttonText\":\"Pick it up!\",\"description\":\"A small bag of flour was found on the floor.\",\"requiredRoll\":\"10\"}",0,"community/heavy-stuff","an anonymous player") ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1363,"Tile: Heavy Stuff",NULL,"tile/community/heavy-stuff",NULL,NULL,NULL,0,NULL,NULL,30,1,NULL,NULL,NULL,0,80,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (37, 1363);
        EOSQL);

        // Yayyy! Bread!
        $this->addSql(<<<EOSQL
        -- hollow earth tile card
        INSERT INTO hollow_earth_tile_card (`id`, `type_id`, `name`, `event`, `required_action`, `image`, `author`) VALUES (81,4,"Yayyy! Bread!","{\"type\":\"payMoneys?\",\"amount\":5,\"ifPaid\":{\"type\":\"onward\",\"buttonText\":\"Yayy bread for me!\",\"description\":\"\\\\\"YAYYY! BREAD FOR YOU!!\\\\\" The magpie said while handing you the bread, \\\\\"Come again soon! More bread for you!\\\\\"\",\"receiveItems\":[\"Slice of Bread\"]},\"description\":\"A small magpie was standing on a basket. \\\\\"Bread! Bread for 5 moneys!\\\\\" It squawked, flapping its wings excitedly, \\\\\"You there! Do you want bread?!\\\\\"\"}",0,"community/yayyy-bread","an anonymous player") ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1364,"Tile: Yayyy! Bread!",NULL,"tile/community/yayyy-bread",NULL,NULL,NULL,0,NULL,NULL,30,1,NULL,NULL,NULL,0,81,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (37, 1364);
        EOSQL);

        // Salad of Wisdom
        $this->addSql(<<<EOSQL
        -- hollow earth tile card
        INSERT INTO hollow_earth_tile_card (`id`, `type_id`, `name`, `event`, `required_action`, `image`, `author`) VALUES (82,3,"Salad of Wisdom","{\"type\":\"chooseOne\",\"buttons\":[\"Approach!\",\"Leave\"],\"outcomes\":[{\"type\":\"onward\",\"buttonText\":\"Onward!\",\"description\":\"The Salad opens his eyes, his tongue lolling out in a smile. He begins to speak.\\\\n\\\\n\\\\\"Ha wa wa, haaa. Ha wa wa wa. Ha ba ba ba, ha wa wa wa, haaa.\\\\\"\\\\n\\\\n%pet.name% doesn\'t really understand what they said, but they feel inspired.\\\\n\\\\nThe Salad gives %pet.name% a knowing nod, rolls them a tenni-- er, Green Sportsball Ball, and sends them on their way.\",\"receiveItems\":[\"Green Sportsball Ball\"],\"statusEffect\":{\"status\":\"Inspired\",\"duration\":480,\"maxDuration\":480}},{\"type\":\"onward\",\"buttonText\":\"Leave\",\"description\":\"Best not to disturb the Salad\'s meditation. You continue onward, but %pet.name% can\'t help but feel inspired by the Salad\'s aura of wisdom.\",\"statusEffect\":{\"status\":\"Inspired\",\"duration\":480,\"maxDuration\":480}}],\"description\":\"You come across a yellow Salad meditating on a fluffy white blanket. Their aura emanates great wisdom but they have not noticed you yet. Next to them, is a sign beckoning you to approach for a free wisdom.\"}",0,"community/salad-of-wisdom","%user:2653.name% (who gets way too much dopamine from the \"dog of wisdom\" meme)") ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1365,"Tile: Salad of Wisdom",NULL,"tile/community/salad-of-wisdom",NULL,NULL,NULL,0,NULL,NULL,30,1,NULL,NULL,NULL,0,82,0,15) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (39, 1365);
        EOSQL);

        // Like Moths...
        $this->addSql(<<<EOSQL
        -- hollow earth tile card
        INSERT INTO hollow_earth_tile_card (`id`, `type_id`, `name`, `event`, `required_action`, `image`, `author`) VALUES (83,8,"Like Moths...","{\"type\":\"petChallenge\",\"stats\":[\"brawl\",\"dexterity\"],\"ifFail\":{\"type\":\"onward\",\"buttonText\":\"You\'ll get them next time!\",\"description\":\"%pet.name% swipes to grab the moths, and misses. They flutter away.\"},\"baseRoll\":20,\"ifSuccess\":{\"type\":\"onward\",\"buttonText\":\"Onward!\",\"description\":\"%pet.name% successfully captures a few moths.\",\"receiveItems\":[\"Moth\",\"Moth \",\"Moth\"]},\"buttonText\":\"That\'s no moon!\",\"description\":\"A flurry of moths swarm around an old mining lamp, mistaking it\'s warm glow for the moon. You might be able to catch some.\",\"requiredRoll\":\"15\"}",0,"community/like-moths","%user:2653.name%") ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1366,"Tile: Like Moths...",NULL,"tile/community/like-moths",NULL,NULL,NULL,0,NULL,NULL,30,1,NULL,NULL,NULL,0,83,0,5) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (38, 1366);
        EOSQL);

        // Worm "Merchant"
        $this->addSql(<<<EOSQL
        -- hollow earth tile card
        INSERT INTO hollow_earth_tile_card (`id`, `type_id`, `name`, `event`, `required_action`, `image`, `author`) VALUES (84,4,"Worm \"Merchant\"","{\"type\":\"chooseOne\",\"buttons\":[\"Fine, I\'ll take some worms.\",\"Nah. No way I\'m buying.\"],\"outcomes\":[{\"type\":\"payMoneys?\",\"amount\":2,\"ifPaid\":{\"type\":\"onward\",\"buttonText\":\"Yay, worms for me...?\",\"description\":\"\\\\\"Thank you for purchasing my worms! I\'ll unblock the path now,\\\\\" it squawked, then flew away.\",\"receiveItems\":[\"Worms\"]},\"description\":\"\\\\\"2 moneys for the worms,\\\\\" the magpie squawked happily. \\\\\"Just 2 moneys!\\\\\"\"},{\"type\":\"petChallenge\",\"stats\":[\"brawl\",\"dexterity\"],\"ifFail\":{\"type\":\"onward\",\"buttonText\":\"That was embarrassing.\",\"description\":\"The magpie beat you up. It then flew away, leaving nothing behind. You heard it grumbling as it flew past you, nearly smacking you on the face.\\\\n\\\\nOh well.\"},\"baseRoll\":20,\"ifSuccess\":{\"exp\":{\"stats\":[\"brawl\"],\"amount\":1},\"type\":\"onward\",\"buttonText\":\"Oh well, I\'ll take it.\",\"description\":\"Turns out magpies are incredibly easy to beat up. Or maybe this one is just weak. It flew away with it\'s basket, leaving behind two feathers as a \\\\\"prize for winning.\\\\\" Huh. You also gain some exp, because that\'s how battling works, apparently.\",\"receiveItems\":[\"Feathers\",\"Feathers\"]},\"buttonText\":\"Oh yeah! Let\'s beat that bird up!!\",\"description\":\"\\\\\"Then I\'m not moving!\\\\\" It squawks.\\\\n\\\\nWait: can\'t you just beat it up to make it go away?\",\"requiredRoll\":\"10\"}],\"description\":\"A magpie holding a basket filled with wriggly worms is standing on the path, blocking the way. \\\\\"Worms for moneys,\\\\\" it squawks. \\\\\"Buy my worms or I don\'t unblock the path!\\\\\"\"}",1,"community/worm-merchant","%user:1943.name%") ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1367,"Tile: Worm \"Merchant\"",NULL,"tile/community/worm-merchant",NULL,NULL,NULL,0,NULL,NULL,30,1,NULL,NULL,NULL,0,84,0,5) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (38, 1367);
        EOSQL);

        // A Fair Game
        $this->addSql(<<<EOSQL
        -- hollow earth tile card
        INSERT INTO hollow_earth_tile_card (`id`, `type_id`, `name`, `event`, `required_action`, `image`, `author`) VALUES (85,5,"A Fair Game","{\"type\":\"payMoneys?\",\"amount\":20,\"ifPaid\":{\"type\":\"petChallenge\",\"stats\":[\"perception\",\"dexterity\",\"strength\"],\"ifFail\":{\"love\":4,\"type\":\"onward\",\"buttonText\":\"Onward!\",\"description\":\"\\\\\"Ah. No luck. Maybe next time?\\\\\" %pet.name% declines another go. These things are rigged anyway. Still, %pet.name% had a lot of fun today. \",\"receiveItems\":[\"Popcorn\",\"Blue Fairy Floss\",\"Red Juice\"]},\"baseRoll\":20,\"ifSuccess\":{\"love\":4,\"type\":\"onward\",\"esteem\":4,\"buttonText\":\"That was fun!\",\"description\":\"%pet.name% can\'t believe their luck. They did it! %pet.name% gleefully receives their prize from the bemused operator: a Fluff Heart, which they can redeem for a plushy at the trader! Aww!!\",\"receiveItems\":[\"Fluff Heart\",\"Pink Fairy Floss\",\"Buttered Popcorn\",\"Tall Glass of Yellownade\"]},\"buttonText\":\"Sweet! Hold my drink!\",\"description\":\"%pet.name% steps up to the challenge...\",\"requiredRoll\":\"20\"},\"description\":\"\\\\\"What\'s this\\u2014a fun fair?!\\\\\" As %pet.name% makes their way through the fairground, a game of coconut shy catches their eye. The operator smiles, \\\\\"20~~m~~ to play a round!\\\\\"\"}",0,"community/a-fair-game","%user:3148.name%") ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1368,"Tile: A Fair Game",NULL,"tile/community/a-fair-game",NULL,NULL,NULL,0,NULL,NULL,30,1,NULL,NULL,NULL,0,85,0,15) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (39, 1368);
        EOSQL);

        // Naner-eater
        $this->addSql(<<<EOSQL
        -- hollow earth tile card
        INSERT INTO hollow_earth_tile_card (`id`, `type_id`, `name`, `event`, `required_action`, `image`, `author`) VALUES (86,2,"Naner-eater","{\"item\":\"Naner\",\"type\":\"payItem?\",\"ifPaid\":{\"type\":\"onward\",\"buttonText\":\"*nod*\",\"description\":\"You give the friendly snail a Naner, and it thanks you for your generosity by gifting you some Quintessence!\",\"receiveItems\":[\"Quintessence\"]},\"ifNotPaid\":{\"type\":\"onward\",\"buttonText\":\"*nod*\",\"description\":\"The friendly snail understands, and thanks you anyway.\"},\"description\":\"You meet a friendly snail who offers you some Quintessence in return for a tasty Naner.\"}",0,"community/naner-eater","%user:2908.name%") ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1371,"Tile: Naner-eater",NULL,"tile/community/naner-eater",NULL,NULL,NULL,0,NULL,NULL,30,1,NULL,NULL,NULL,0,86,0,5) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (38, 1371);
        EOSQL);

        // Lumpy & Green
        $this->addSql(<<<EOSQL
        -- hollow earth tile card
        INSERT INTO hollow_earth_tile_card (`id`, `type_id`, `name`, `event`, `required_action`, `image`, `author`) VALUES (87,14,"Green & Lumpy","{\"type\":\"chooseOne\",\"buttons\":[\"Uhhh yeah, avocado!\",\"Suuuuure, I could use an avocado....\",\"There\'s something not quite right about this character here\"],\"outcomes\":[{\"item\":\"Avocado\",\"type\":\"payItem?\",\"ifPaid\":{\"type\":\"onward\",\"buttonText\":\"Alright then...\",\"description\":\"\\\\\"Avocado!\\\\\"\\\\nThe person(?) gives you a different, lint-covered avocado from their bag.\",\"receiveItems\":[\"Fluff\",\"Avocado\"]},\"description\":\"Give the person(?) an avocado?\"},{\"type\":\"payMoneys?\",\"amount\":3,\"ifPaid\":{\"type\":\"onward\",\"buttonText\":\"\\\\\"Avocado!\\\\\"\",\"description\":\"The person(?) hands you a lint-covered avocado from their bag\\\\n\\\\\"Avocado!\\\\\"\",\"receiveItems\":[\"Fluff\",\"Avocado\"]},\"description\":\"Give the person(?) 3 moneys, hoping to get an avocado in return?\"},{\"type\":\"petChallenge\",\"stats\":[\"stealth\"],\"ifFail\":{\"exp\":{\"stats\":[\"stealth\"],\"amount\":5},\"type\":\"onward\",\"esteem\":-2,\"buttonText\":\"AHHH BROWN AVOCADO\",\"description\":\"The avocado-person notices your pet\'s lame attempt to be sneaky.\\\\n\\\\\"AVOCADO! AVOCADO! AVOCADO!\\\\\" it cries in a harsh, annoying tone. \\\\nAs you start backing away, the avocado-person throws avocados at you. Unfortunately, they\'re all very over-ripe and unusable... :(\"},\"baseRoll\":20,\"ifSucceed\":{\"type\":\"onward\",\"esteem\":2,\"buttonText\":\"Avocados!\",\"description\":\"Your pet very carefully notices a tear in the avocado-person\'s jumpsuit and deftly removes five perfectly-ripe avocados\",\"receiveItems\":[\"Avocado\",\"Avocado\",\"Avocado\",\"Avocado\",\"Avocado\"]},\"buttonText\":\"I bet there\'s so many avocados inside it!\",\"description\":\"You notice that the person(?) seems to be made of a bunch of avocados dumped into a light green jumpsuit...\",\"requiredRoll\":\"20\"}],\"description\":\"A curiously lumpy person(?) in a light green jumpsuit carrying a lumpy sack appears in front of you. With a grating voice he says,\\\\n\\\\\"Avocado?\\\\\"\"}",0,"community/avocado","%user:2942.name%") ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1372,"Tile: Lumpy & Green",NULL,"tile/community/avocado",NULL,NULL,NULL,0,NULL,NULL,30,1,NULL,NULL,NULL,0,87,0,15) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (39, 1372);
        EOSQL);

        // A Captivating Arena
        $this->addSql(<<<EOSQL
        -- hollow earth tile card
        INSERT INTO hollow_earth_tile_card (`id`, `type_id`, `name`, `event`, `required_action`, `image`, `author`) VALUES (88,5,"A Captivating Arena","{\"type\":\"chooseOne\",\"buttons\":[\"Watch a game.\",\"Try fighting in the arena.\"],\"outcomes\":[{\"food\":2,\"type\":\"onward\",\"safety\":2,\"buttonText\":\"Onward!\",\"description\":\"The fighting was entertaining. %pet.name% felt inspired as you left the arena.\",\"statusEffect\":{\"status\":\"Inspired\",\"duration\":480,\"maxDuration\":480}},{\"type\":\"petChallenge\",\"stats\":[\"strength\",\"brawl\",\"stamina\",\"dexterity\",\"stealth\"],\"ifFail\":{\"exp\":{\"stats\":[\"brawl\"],\"amount\":1},\"food\":4,\"love\":2,\"type\":\"onward\",\"esteem\":-4,\"buttonText\":\"Onward!\",\"description\":\"%pet.name% lost the round, and left the arena defeated. The audience cheered regardless, thanking you for entertaining them.\"},\"baseRoll\":20,\"ifSuccess\":{\"exp\":{\"stats\":[\"brawl\"],\"amount\":4},\"food\":4,\"love\":4,\"type\":\"onward\",\"esteem\":4,\"buttonText\":\"Onward!\",\"description\":\"%pet.name% won the round, and the audience cheered!\"},\"buttonText\":\"Let\'s fight!\",\"description\":\"%pet.name% was welcomed into the arena and matched with an opponent of similar size. They looked to be an experienced fighter. They stared at %pet.name% and growled.\",\"requiredRoll\":\"20\"}],\"description\":\"You saw an arena in the middle of the town. Two fighters are fighting in the arena, and the crowd around is cheering loudly.\"}",1,"community/captivating-arena","%user:2029.name%") ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1373,"Tile: A Captivating Arena",NULL,"tile/community/captivating-arena",NULL,NULL,NULL,0,NULL,NULL,30,1,NULL,NULL,NULL,0,88,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (37, 1373);
        EOSQL);

        // Dealings in the Dark
        $this->addSql(<<<EOSQL
        -- hollow earth tile card
        INSERT INTO hollow_earth_tile_card (`id`, `type_id`, `name`, `event`, `required_action`, `image`, `author`) VALUES (89,8,"Dealings in the Dark","{\"type\":\"chooseOne\",\"buttons\":[\"Jump out and challenge!\",\"Sneak in and steal!\"],\"outcomes\":[{\"type\":\"petChallenge\",\"stats\":[\"brawl\",\"stamina\",\"strength\"],\"ifFail\":{\"exp\":{\"stats\":[\"brawl\"],\"amount\":1},\"food\":4,\"type\":\"onward\",\"esteem\":-2,\"safety\":-2,\"buttonText\":\"Onward!\",\"description\":\"The vampires fought back viciously! You were forced to flee! Fortunately, they did not chase you for far, and you were able to go back to your original path.\"},\"baseRoll\":20,\"ifSuccess\":{\"exp\":{\"stats\":[\"brawl\"],\"amount\":4},\"food\":4,\"type\":\"onward\",\"esteem\":4,\"buttonText\":\"Onward!\",\"description\":\"%pet.name% defeated the vampires, and as they were fleeing the scene, %pet.name% snatched a bunch of things from them!\",\"receiveItems\":[\"Talon\",\"Linens and Things\",\"Blood Wine\",\"Gold Bar\"]},\"buttonText\":\"Try it!\",\"description\":\"%pet.name% jumped out and took on the vampires!!\",\"requiredRoll\":\"20\"},{\"type\":\"petChallenge\",\"stats\":[\"perception\"],\"ifFail\":{\"exp\":{\"stats\":[\"stealth\"],\"amount\":1},\"type\":\"onward\",\"esteem\":-2,\"safety\":-2,\"buttonText\":\"Onward!\",\"description\":\"%pet.name% was caught red-handed! The vampires chased you out, but before going, you both managed to grab something from them.\",\"receiveItems\":[\"Linens and Things\"]},\"baseRoll\":20,\"ifSuccess\":{\"exp\":{\"stats\":[\"stealth\"],\"amount\":3},\"type\":\"onward\",\"buttonText\":\"Onward!\",\"description\":\"The vampire did not notice %pet.name% at all! Turns out they were trading beddings for their coffins for gold; %pet.name% was able to take a couple of things, before retreating with their prizes!\",\"receiveItems\":[\"Linens and Things\",\"Linens and Things\",\"Gold Bar\",\"Gold Bar\"]},\"buttonText\":\"Sneak in! (And steal!)\",\"description\":\"The vampires were all distracted and immersed in eager discussions of prices...\",\"requiredRoll\":\"15\"}],\"description\":\"You heard mumbling voices in the caves. You carefully approached and saw a vampire gathering! It seems that the vampires are trading with each other. What should %pet.name% do?\"}",0,"community/dealings-in-the-dark","%user:2029.name%") ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1374,"Tile: Dealings in the Dark",NULL,"tile/community/dealings-in-the-dark",NULL,NULL,NULL,0,NULL,NULL,30,1,NULL,NULL,NULL,0,89,0,5) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (38, 1374);
        EOSQL);

        $this->addSql("UPDATE `item` SET `name` = 'Hollow Earth Booster Pack: Beginnings' WHERE `item`.`id` = 1050;");

        // Hollow Earth Booster Pack: Community Pack
        $this->addSql(<<<EOSQL
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1376,"Hollow Earth Booster Pack: Community Pack",NULL,"booster-pack/2","[[\"Open!\",\"boosterPack\\/two\\/#\\/open\"]]",NULL,NULL,0,NULL,NULL,0,0,NULL,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
