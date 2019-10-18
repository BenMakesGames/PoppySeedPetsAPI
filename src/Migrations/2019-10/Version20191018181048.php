<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Merit;
use App\Entity\Pet;
use App\Functions\ArrayFunctions;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191018181048 extends AbstractMigration implements ContainerAwareInterface
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

        $this->addSql('ALTER TABLE pet ADD is_fertile TINYINT(1) NOT NULL');
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

        $this->addSql('ALTER TABLE pet DROP is_fertile');
    }
}
