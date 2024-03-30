<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240330132000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Majestosa';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
            INSERT INTO `pet_species` (`id`, `name`, `image`, `description`, `hand_x`, `hand_y`, `hand_angle`, `flip_x`, `hand_behind`, `available_from_pet_shelter`, `pregnancy_style`, `egg_image`, `hat_x`, `hat_y`, `hat_angle`, `available_from_breeding`, `sheds_id`, `family`, `name_sort`, `physical_description`) VALUES (109, 'Arnoldson\'s Chlamydosaurus', 'lizard/arnoldsons-chlamydosaurus', 'Named after its discoverer, field researcher Fisk Arnoldson. It is the second known species of the genus Chlamydosaurus.', '0.645', '0.825', '45', '0', '0', '0', '0', 'striped-small', '0.54', '0.435', '-4', '1', '35', 'lizard', 'Arnoldson\'s Chlamydosaurus', 'A frill-necked lizard with thick stripes on its back. It walks upright.')
            ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
