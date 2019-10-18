<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Merit;
use App\Entity\Pet;
use App\Functions\ArrayFunctions;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191018171826 extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    public function getDescription() : string
    {
        return '';
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pet_merit (pet_id INT NOT NULL, merit_id INT NOT NULL, INDEX IDX_B2265A86966F7FB6 (pet_id), INDEX IDX_B2265A8658D79B5E (merit_id), PRIMARY KEY(pet_id, merit_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pet_merit ADD CONSTRAINT FK_B2265A86966F7FB6 FOREIGN KEY (pet_id) REFERENCES pet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pet_merit ADD CONSTRAINT FK_B2265A8658D79B5E FOREIGN KEY (merit_id) REFERENCES merit (id) ON DELETE CASCADE');
    }

    public function postUp(Schema $schema): void
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        /** @var Pet[] $pets */
        $pets = $em->getRepository(Pet::class)->findAll();

        foreach($pets as $pet)
        {
            if(count($pet->getOldMerits()) === 0)
                continue;

            echo $pet->getName() . ' has: ' . ArrayFunctions::list_nice($pet->getOldMerits()) . "\n";

            $merits = $em->getRepository(Merit::class)->findBy([ 'name' => $pet->getOldMerits() ]);

            foreach($merits as $merit)
                $pet->addMerit($merit);
        }

        $em->flush();
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE pet_merit');
    }
}
