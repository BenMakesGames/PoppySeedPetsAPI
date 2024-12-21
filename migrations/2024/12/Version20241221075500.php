<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241221075500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Ichthyastra
        $this->addSql(<<<EOSQL
        INSERT INTO `merit` (`id`, `name`, `description`) VALUES (60, 'Ichthyastra', 'A third of the fish Fish that %pet.name% acquires will have a random spice applied, however %pet.name% can never again seek the Vesica Hydrargyrum.')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // Metatron's Touch
        $this->addSql(<<<EOSQL
        INSERT INTO `merit` (`id`, `name`, `description`) VALUES (61, 'Metatron\'s Touch', 'When %pet.name% makes something with Firestone, a Rock is leftover, however %pet.name% can never again seek Metatron\'s Fire.')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // Manxome
        $this->addSql(<<<EOSQL
        INSERT INTO `merit` (`id`, `name`, `description`) VALUES (62, 'Manxome', 'If %pet.name% has less Dexterity than Stamina, it gets +1 Dexterity, otherwise it gets +1 Stamina. Regardless, %pet.name% can never again seek Earth\'s Egg.')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // Lightning Reins
        $this->addSql(<<<EOSQL
        INSERT INTO `merit` (`id`, `name`, `description`) VALUES (63, 'Lightning Reins', 'Whenever %pet.name% collects Lightning in a Bottle, it also gets Quintessence, however %pet.name% can never again seek the Merkaba of Air.')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
