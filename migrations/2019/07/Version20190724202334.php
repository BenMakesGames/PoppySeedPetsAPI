<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Enum\SpiritCompanionStarEnum;
use App\Service\Squirrel3;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190724202334 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE spirit_companion CHANGE skill star VARCHAR(40) NOT NULL');
    }

    public function postUp(Schema $schema): void
    {
        parent::postUp($schema);

        $squirrel3 = new Squirrel3();
        $companions = $this->connection->fetchAllAssociative('SELECT id FROM spirit_companion');

        foreach($companions as $companion)
        {
            $this->connection->executeQuery(
                'UPDATE spirit_companion SET star=:star WHERE id=:id LIMIT 1',
                [
                    'id' => $companion['id'],
                    'star' => SpiritCompanionStarEnum::getRandomValue($squirrel3)
                ]
            );
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE spirit_companion CHANGE star skill VARCHAR(40) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('UPDATE spirit_companion SET skill=\'\'');
    }
}
