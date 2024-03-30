<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240330120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Majestosa';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
            INSERT INTO `pet_species` (`id`, `name`, `image`, `description`, `hand_x`, `hand_y`, `hand_angle`, `flip_x`, `hand_behind`, `available_from_pet_shelter`, `pregnancy_style`, `egg_image`, `hat_x`, `hat_y`, `hat_angle`, `available_from_breeding`, `sheds_id`, `family`, `name_sort`, `physical_description`) VALUES (108, 'Majestosa', 'lizard/majestosa', 'When first cataloged by a team of HERG scientists, the creature was to be named \"Winged Paradise\", however one member of the team was convinced the animal looked familiar, and after a couple days of searching realized why: a sketch of it appears in the notes of Portuguese explorer Estêvão de Noronha Almeida during one of his expeditions in 1528. He described the creature as being a majestic one - \"uma ciatura majestosa\" - which ultimately lead the team to settle on the name \"Majestosa\" for this species.', '0.535', '0.74', '-124', '1', '1', '1', '0', 'speckled-small', '0.095', '0.61', '-38', '1', '35', 'lizard', 'Majestosa', 'A long, thin, flying snake. It has two white wings that are flecked with color.')
            ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
