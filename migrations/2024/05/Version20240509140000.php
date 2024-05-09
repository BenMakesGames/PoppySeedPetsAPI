<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240509140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'more new location tags';
    }

    public const New_Tags = [
        [ 'id' => 90, 'title' => 'Location: Abandoned Quarry', 'color' => '917876', 'emoji' => '🧱' ],
    ];

    public function up(Schema $schema): void
    {
        foreach(self::New_Tags as $tag)
        {
            $this->addSql(<<<EOSQL
            INSERT INTO pet_activity_log_tag
            (id, title, color, emoji)
            VALUES
            ({$tag['id']}, "{$tag['title']}", "{$tag['color']}", "{$tag['emoji']}")
            ON DUPLICATE KEY UPDATE `id` = `id`;
            EOSQL);

            $this->addSql('UPDATE pet_activity_log_tag SET title="Location: Icy Moon" WHERE title="Icy Moon"');
        }
    }

    public function down(Schema $schema): void
    {
    }
}
