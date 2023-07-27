<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230727210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
INSERT INTO `pet_species` (`id`, `name`, `image`, `description`, `hand_x`, `hand_y`, `hand_angle`, `flip_x`, `hand_behind`, `available_from_pet_shelter`, `pregnancy_style`, `egg_image`, `hat_x`, `hat_y`, `hat_angle`, `available_from_breeding`, `sheds_id`, `family`, `name_sort`, `physical_description`) VALUES (91, 'Kejoro', 'spirit/totally-hair', 'This creature is named after the Japanese Kejōrō, a yokai that appears as a young woman whose face and body is completely obscured by long hair.\r\n\r\nFortunately, that\'s where the similarities end: _this_ spirit will _not_ slice you to pieces with its hair should you approach it! Quite the contrary: it loves to be pet!', '0.315', '0.48', '116', '0', '0', '1', '1', NULL, '0.495', '0.08', '-10', '0', '34', 'spirit', 'Kejoro', 'An elongated mass of unkempt hair... which it can apparently use to hold things, so that\'s neat.');
EOSQL
);
    }

    public function down(Schema $schema): void
    {
    }
}
