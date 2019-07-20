<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Enum\FlavorEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190719203607 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet ADD favorite_flavor VARCHAR(20) NOT NULL');
    }

    public function postUp(Schema $schema): void
    {
        parent::postUp($schema);

        $pets = $this->connection->fetchAll('SELECT id FROM pet');

        foreach($pets as $pet)
        {
            $this->connection->executeQuery(
                'UPDATE pet SET favorite_flavor=:flavor WHERE id=:id LIMIT 1',
                [
                    'id' => $pet['id'],
                    'flavor' => FlavorEnum::getRandomValue()
                ]
            );
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet DROP favorite_flavor');
    }
}
