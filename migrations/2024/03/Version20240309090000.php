<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240309090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'cat-- er... pillar?';
    }

    public function up(Schema $schema): void
    {
        // wearable mermaid eggs
        $this->addSql(<<<EOSQL
            INSERT INTO `pet_species` (`id`, `name`, `image`, `description`, `hand_x`, `hand_y`, `hand_angle`, `flip_x`, `hand_behind`, `available_from_pet_shelter`, `pregnancy_style`, `egg_image`, `hat_x`, `hat_y`, `hat_angle`, `available_from_breeding`, `sheds_id`, `family`, `name_sort`, `physical_description`) VALUES (107, 'Cat-- er... pillar?', 'bug/notcat', 'This species of tiny cat has-- er, wait a minute: that\'s not a tiny cat! It\'s some kind of cute & fuzzy cater<em>pillar</em>?!', '0.915', '0.58', '-20', '1', '1', '1', '0', 'striped-small', '0.445', '0.35', '0', '1', '34', 'bug', 'Cat-- er... pillar?', 'Its body is made of five, fuzzy segments of alternating colors. One of those segments bears its face, with two, large, deep-black eyes, and tufts that look like puffy, fuzzy cheeks.')
            ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
