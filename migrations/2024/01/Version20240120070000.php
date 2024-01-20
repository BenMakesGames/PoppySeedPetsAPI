<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240120070000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'naner ketchup';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
        INSERT INTO `item` (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (NULL, 'Naner Ketchup', 'I know some of you may complain \"ketchup can\'t be made of naners,\" but consider this: there are others who say \"ketchup is basically naner puddin\',\" and those people are really speakin\' my language.', 'fruit/naner-ketchup', NULL, NULL, NULL, '4', NULL, NULL, '0', '0', NULL, '2', NULL, '0', NULL, '0', '1')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
