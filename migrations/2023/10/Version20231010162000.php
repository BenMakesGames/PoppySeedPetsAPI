<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231010162000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
INSERT INTO user_unlocked_aura (user_id, aura_id, unlocked_on, comment)
SELECT pet.owner_id,136,NOW(),'You got yourself a Hedgehog at one point or another.'
FROM pet WHERE pet.species_id=41
GROUP BY pet.owner_id
EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
